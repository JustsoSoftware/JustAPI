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
            $config = Bootstrap::getInstance()->getConfiguration();
            $services = $config['services'];
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
            if (empty($server['PATH_INFO']) || empty($server['REQUEST_METHOD'])) {
                throw new InvalidParameterException("Missing service specification.");
            }
            $serviceName = preg_replace('/^\/(.*)(\?.*)?$/', '$1', $server['PATH_INFO']);
            $className = $this->findServiceClassName($this->services, $serviceName);

            $allowedOrigins = Bootstrap::getInstance()->getAllowedOrigins();
            if ($allowedOrigins !== '') {
                $this->environment->sendHeader('Access-Control-Allow-Origin: ' . $allowedOrigins);
                $this->environment->sendHeader('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
                $this->environment->sendHeader('Access-Control-Allow-Headers: Token, Content-Type, Origin, Accept');
            }

            $method = strtolower($server['REQUEST_METHOD']);
            if ($method != 'options') {
                /** @var $service RestService */
                $service = new $className($this->environment, $serviceName);
                $service->setName($serviceName);
                $this->extractParameters($this->environment);
                $verb = $method . 'Action';
                $service->$verb();
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
        $body = file_get_contents("php://input");
        $content_type = isset($server['CONTENT_TYPE']) ? $server['CONTENT_TYPE'] : '';
        if (strchr($content_type, ';') !== false) {
            list($content_type) = preg_split('/;/', $content_type);
        }
        switch ($content_type) {
            case "application/json":
                $body_params = json_decode($body, true);
                if ($body_params) {
                    $params = array_merge($params, $body_params);
                }
                break;

            case "application/x-www-form-urlencoded":
                $postvars = array();
                parse_str($body, $postvars);
                $params = array_merge($params, $postvars);
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
            $service = str_replace(array('/', '*'), array('\\/', '.*'), $service);
            return preg_match('/^' . $service . '$/', $serviceName);
        });
        if (count($candidates) > 0) {
            $prefix = preg_replace('/\*$/', '', current($candidates));
            $info = $services[current($candidates)];
            if (strpos($info, 'file:') === 0) {
                $appFile = Bootstrap::getInstance()->getAppRoot() . '/' . str_replace('file:', '', $info);
                $config = json_decode(file_get_contents($appFile), true);
                return $this->findServiceClassName($config['services'], str_replace($prefix, '', $serviceName));
            } else {
                return $info;
            }
        }
        throw new \Exception("Unknown service: '{$serviceName}'");
    }
}
