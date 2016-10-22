<?php
/**
 * Definition of RestServiceFactory
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    jsutso\service
 */

namespace justso\justapi;

/**
 * Creates REST services
 *
 * @package    justso\service
 */
class RestServiceFactory
{
    /**
     * List of services in this project
     * @var string[]
     */
    private $services;

    /**
     * @var SystemEnvironmentInterface
     */
    private $environment;

    /**
     * Initializes the factory
     * @param SystemEnvironmentInterface $environment
     * @param null $services
     */
    public function __construct(SystemEnvironmentInterface $environment, $services = null)
    {
        if ($services === null) {
            $config = $environment->getBootstrap()->getConfiguration();
            $services = isset($config['services']) ? $config['services'] : array();
        }
        $this->services = $services;
        $this->environment = $environment;
        date_default_timezone_set('UTC');
    }

    /**
     * Handles a request by instantiating the matching REST service class in the current project.
     */
    public function handleRequest()
    {
        try {
            $server = $this->environment->getRequestHelper()->getServerParams();
            $serviceName = preg_replace('/^(.*?)(\?.*)?$/', '$1', $this->getURI($server));
            $className = $this->findServiceClassName($this->services, $serviceName);
            $method = $this->getMethod($server);
            $this->handleAllowedOrigins();

            if ($method != 'options') {
                $this->callService($className, $serviceName, $method);
            }
        } catch (InvalidParameterException $e) {
            $msg = $e->getMessage() ?: "Missing parameter";
            $this->environment->sendResult('400 Bad Request', 'text/plain; charset=utf-8', $msg);
        } catch (DenyException $e) {
            $this->environment->sendResult('403 Forbidden', "text/plain; charset=utf-8", $e->getMessage());
        } catch (\Exception $e) {
            $this->environment->sendResult('500 Server error', "text/plain; charset=utf-8", $e->getMessage());
        }
    }

    /**
     * Extracts request parameters to the given environment
     *
     * @param SystemEnvironmentInterface $environment
     */
    private function extractParameters(SystemEnvironmentInterface $environment)
    {
        $server = $this->environment->getRequestHelper()->getServerParams();
        $params = array();
        parse_str(isset($server['QUERY_STRING']) ? $server['QUERY_STRING'] : '', $params);
        $body = $this->environment->getStdInput();
        $content_type = isset($server['CONTENT_TYPE']) ? $server['CONTENT_TYPE'] : '';
        if (strchr($content_type, ';') !== false) {
            list($content_type) = preg_split('/;/', $content_type, 2);
        }
        switch ($content_type) {
            case "application/json":
                $params = array_merge($params, $this->parseApplicationJson($body));
                break;

            case "application/x-www-form-urlencoded":
                $params = array_merge($params, $this->parseApplicationFormUrlEncoded($body));
                break;

            case "multipart/form-data":
                $params = array_merge($params, $this->parseMultipartFormdata($body));
                break;
        }
        $environment->getRequestHelper()->fillWithData($params);
    }

    /**
     * Searches the services list for a matching service specification and returns the corresponding class name.
     *
     * @param  mixed[] $services    list of services
     * @param  string  $serviceName name of requested service
     *
     * @throws \Exception if no matching service was found
     * @return string     class name
     */
    private function findServiceClassName($services, $serviceName)
    {
        $candidates = array_filter(array_keys($services), function ($service) use ($serviceName) {
            $service = str_replace(array('/', '*', '-'), array('\\/', '.*', '\\-'), $service);
            return preg_match('/^' . $service . '$/', $serviceName);
        });
        if (count($candidates) > 0) {
            $prefix = preg_replace('/\*$/', '', current($candidates));
            $info = $services[current($candidates)];
            if (strpos($info, 'file:') === 0) {
                $appFile = $this->environment->getBootstrap()->getAppRoot() . '/' . str_replace('file:', '', $info);
                $config = json_decode(file_get_contents($appFile), true);
                return $this->findServiceClassName($config['services'], str_replace($prefix, '', $serviceName));
            } else {
                return $info;
            }
        }
        throw new \Exception("Unknown service: '{$serviceName}'");
    }

    private function handleAllowedOrigins()
    {
        $env = $this->environment;
        $allowedOrigins = $env->getBootstrap()->getAllowedOrigins();
        if ($allowedOrigins !== '') {
            $env->sendHeader('Access-Control-Allow-Origin: ' . $allowedOrigins);
            $env->sendHeader('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
            $env->sendHeader('Access-Control-Allow-Headers: Token, Content-Type, Origin, Accept, x-requested-with');
        }
    }

    /**
     * @param $className
     * @param $serviceName
     * @param $method
     * @throws InvalidParameterException
     */
    private function callService($className, $serviceName, $method)
    {
        /** @var $service RestService */
        $service = $this->environment->getDIC()->get($className, [$this->environment, $serviceName]);
        $service->setName($serviceName);
        $this->extractParameters($this->environment);
        $verb = $method . 'Action';
        if (!method_exists($service, $verb)) {
            throw new InvalidParameterException("The request method is not defined in this service");
        }
        $service->$verb();
    }

    /**
     * @param string[] $server
     * @return string
     * @throws InvalidParameterException
     */
    private function getMethod($server)
    {
        if (empty($server['REQUEST_METHOD'])) {
            throw new InvalidParameterException("Missing request method");
        }
        return strtolower($server['REQUEST_METHOD']);
    }

    /**
     * @param string[] $server
     * @return string
     * @throws InvalidParameterException
     */
    private function getURI($server)
    {
        if (empty($server['REQUEST_URI'])) {
            throw new InvalidParameterException("Missing information about service URI");
        }
        return $server['REQUEST_URI'];
    }

    private function parseApplicationFormUrlEncoded($body)
    {
        $postvars = [];
        parse_str($body, $postvars);
        return $postvars;
    }

    private function parseApplicationJson($body)
    {
        return json_decode($body, true);
    }

    private function parseMultipartFormdata($body)
    {
        $params = [];
        $boundary = substr($body, 0, strpos($body, "\r\n"));
        foreach (array_slice(explode($boundary, $body), 1) as $part) {
            if ($part === "--\r\n") {
                break;
            }
            $part = ltrim($part, "\r\n");
            list($rawHeaders, $content) = explode("\r\n\r\n", $part, 2);
            $content = substr($content, 0, strlen($content) - 2);
            $headers = [];
            foreach (explode("\r\n", $rawHeaders) as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }
            if (isset($headers['content-disposition'])) {
                preg_match(
                    '/^(.+); *name="(([\w_]+)(\[([\w_]*)\])?)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );

                $param = $matches[3];
                if (isset($matches[7])) {
                    $params[$param] = [
                        'name' => $matches[7],
                        'content' => $content,
                        'type' => $headers['content-type']
                    ];
                } elseif (isset($matches[4])) {
                    if ($matches[4] === '[]') {
                        if (!isset($params[$param])) {
                            $params[$param] = [];
                        }
                        $params[$param][] = $content;
                    } else {
                        $params[$param][$matches[5]] = $content;
                    }
                } else {
                    $params[$param] = $content;
                }
            }
        }
        return $params;
    }
}
