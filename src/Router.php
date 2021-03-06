<?php


namespace Fastero\Router;


use Fastero\Router\Exception\GeneratorException;
use Fastero\Router\Exception\ProcessRouterException;
use Fastero\Router\Exception\RouterException;
use Fastero\Router\Exception\RouterNotFoundException;
use Fastero\Router\PathHandler\GeneratorInterface;
use Fastero\Router\PathHandler\MatcherInterface;

class Router
{

    /**
     * @var array
     * [
     * type: RouteProcessorClass::class,
     * path: 'news/all' - path pattern - regex or something in format RouteProcessorClass accept
     * default: [
     *    name: value
     *  ] - default values or something you wish to pass along with request params
     * validate: [
     *    name: '\d+' - regexp to validate value against. simple validations preferred
     *  ] - if some value doesn't match validation regex route will be skipped to the next one
     * reverse: "news/all" - pattern to generate params back to path if  RouteProcessorClass needs one
     *
     * call:[
     *    class: ClassName::class,
     *    method: 'run'
     *  ]
     * validate - array where keys are parameter names and values are regex to validate against or array containing 'callback' key
     * e.g. ["name" => "[a-zA-Z]+", "id" => "[0-9]+"] or ["id" => ['callback' => 'NumberValidator::isInt' ]]
     * - defaults - default values for optional elements. key - name of parameter, value is value. It can be
     * also used to pass some additional parameters;
     * - query - settings for query parameters
     * => [
     * parameters = [
     *  'param1_name' => ['required' => true, 'validate' =>'[0-9]+', default => '1'],
     *  'param2_name' => ['required' => true], //no validation
     *  'param3_name' => [], //required=false by default, no validation
     *  ],
     *  'strict_match' => true, //order of params will be checked and no other parameters accepted. FALSE by default
     *  'strict_generate' => true, //order of params will preserved and params will be limited by those specified in the parameters section
     *
     * ]
     */
    protected $routes;

    protected $routeProcessors;
    protected $routeGenerators;

    /**
     * @param array $routes
     */
    public function __construct(array $routes = []) {
        $this->routes = $routes;
    }

    /**
     * @param $method - HTTP method (GET|HEAD|POST etc.)
     * @param $uriPath - URL excluding domain name and query string
     * @param array $queryParams all the params of query string
     * @return array - params of route that match or null
     *
     * return array structure:
     * [
     * 'name' => 'nameOfTheMatchedRoute',
     * 'parameters' => [], - parsed parameters merged with defaults
     * 'query' => [], - query parameters merged with defaults
     * 'options' = [] - options that were set during route configuration
     *
     * ]
     */
    public function match($method, $uriPath, $queryParams = []) {

        try {
            $matchRouteData = [];
            foreach ($this->routes as $routeName => $routeOptions) {
                $routeParams = $this->processRoute($routeOptions, $method, $uriPath, $queryParams);
                if (!is_null($routeParams)) {
                    $matchRouteData['name'] = $routeName;
                    $matchRouteData['parameters'] = $routeParams['parameters'];
                    $matchRouteData['query'] = $routeParams['query'];
                    $matchRouteData['options'] = $routeOptions;
                    return $matchRouteData;
                }
            }
        } catch (\Exception $exception) {
            throw new ProcessRouterException(sprintf('Error occurred during processing "%s" route: "%s"', $routeName, $exception->getMessage()), 0, $exception);
        }

        if (empty($this->routes)) {
            throw new RouterException(sprintf('No routes found'));
        } else {
            throw new RouterNotFoundException(sprintf('No routes found for path "%s", method "%s".', $uriPath, $method));
        }

    }

    protected function processRoute($routeOptions, $method, $uriPath, $queryParams = []) {

        if (!$this->methodMatch($method, $routeOptions))
            return null;

        $processorClass = $routeOptions['type'];

        /** @var MatcherInterface $routeProcessor */
        $routeProcessor = $this->routeProcessors[$processorClass] ??
            $this->routeProcessors[$processorClass] = new $processorClass;

        $routeProcessor->setOptions($routeOptions);


        $pathParameters = $routeProcessor->match($uriPath);
        if (is_null($pathParameters)) {
            return null;
        }

        $routeParams = $this->processParams($pathParameters, $routeOptions);
        if (is_null($routeParams)) {
            return null;
        }

        $queryParams = $this->processQuery($queryParams, $routeOptions);
        if (is_null($queryParams)) {
            return null;
        }

        return ['parameters' => $routeParams, 'query' => $queryParams];
    }

    protected function methodMatch($methodToCheck, $routeOptions) {


        if (empty($routeOptions['method'])) {
            return true;
        } else if (is_array($routeOptions['method'])) {
            $methods = $routeOptions['method'];
        } else {
            $methods = [$routeOptions['method']];
        }

        $methodToCheck = strtoupper($methodToCheck);

        foreach ($methods as $method) {
            $method = strtoupper($method);
            if ($method == $methodToCheck) return true;
            if ($method == "GET" and $methodToCheck = "HEAD") return true;
        }
        return false;
    }

    /**
     * add default values and validate params
     * return $pathParams + default route params if
     * validation is successful or null otherwise
     * @param array $pathParams
     * @param $routeOptions
     * @return array|null
     */
    protected function processParams($pathParams, $routeOptions) {
        $allParams = $this->mergeRouteDefaultParams($pathParams, $routeOptions);
        $validations = $routeOptions['validate'] ?? [];

        foreach ($validations as $paramName => $rule) {
            if (isset($allParams[$paramName])) {
                $value = $allParams[$paramName];
                if (empty($rule) || !$this->isValidParam($rule, $value)) {
                    return null;
                };
            }
        }
        return $allParams;
    }

    protected function mergeRouteDefaultParams($pathParams, $routeOptions) {
        $defaultParams = $routeOptions['default'] ?? [];
        return array_merge($defaultParams, $pathParams);
    }

    protected function isValidParam($rule, $value) {
        $valid = true;
        if (is_array($rule)) {
            $callable = $rule['callback'] ?? null;
            $params = $rule['parameters'] ?? [];
            if (!is_null($callable) && is_callable($callable)) {
                $valid = call_user_func_array($callable, array_merge([$value], $params));
            } else if (!is_null($callable)) {
                throw new RouterException('Validation rule contains callback but it isn\'t callable');
            } else {
                throw new RouterException('Validation rule is incorrect');
            }
        } else {
            try {
                $regex = "(^" . $rule . "$)";
                $valid = (1 === preg_match($regex, $value));
            } catch (\Exception $exception) {
                throw new RouterException(sprintf('Error parsing validation regex "%s", message: "%s', $regex, $exception->getMessage()), 0, $exception);
            }
        }

        return $valid;
    }

    /**
     * @param $query
     * @param $routeOptions
     * @return array|null - array of accepted query parameters or null if validation failed
     */
    protected function processQuery($query, $routeOptions) {
        $queryConfig = $routeOptions['query'] ?? [];

        if (empty($queryConfig['parameters'])) {
            return $query;
        }

        $strictMatch = $queryConfig['strict_match'] ?? false;
        foreach ($queryConfig['parameters'] as $parameterName => $validationData) {
            //check params order
            if ($strictMatch) {
                $queryParameterName = key($query);
                if (is_null($queryParameterName)) {
                    return null;
                }
                if ($parameterName != $queryParameterName) {
                    return null;
                }
                next($query);
            }
            if (empty($validationData)) {
                continue;
            }

            $required = $validationData['required'] ?? false;
            if ($required && !isset($query[$parameterName])) {
                return null;
            }
            if (isset($validationData['validate'])
                && isset($query[$parameterName])
                && !$this->isValidParam($validationData['validate'], $query[$parameterName])) {
                return null;
            }

        }

        if ($strictMatch and !is_null(key($query))) {
            return null; //if query has extra params and strict mode is enabled
        }


        return $query;

    }

    public function makePath($routeName, $parameters = [], $query = [], $mergeDefault = true) {
        if (empty($this->routes[$routeName])) {
            throw new GeneratorException(sprintf('Route "%s" not found in the configuration or empty".', $routeName));
        }
        $routeOptions = $this->routes[$routeName];

        if ($mergeDefault) {
            $routerParameters = $this->mergeRouteDefaultParams($parameters, $routeOptions);
        } else {
            $routerParameters = $parameters;
        }

        $generatorClass = $routeOptions['reverse']['generator'] ?? $routeOptions['type'];
        if (empty($this->routeGenerators[$routeName])) {

            if (!class_exists($generatorClass)) {
                throw new GeneratorException(sprintf('Unknown route type "%s" for route "%s".', $generatorClass, $routeName));
            }
            /** @var MatcherInterface $generator */
            $generator = new $generatorClass();

            if (!$generator instanceof GeneratorInterface) {
                throw new GeneratorException(sprintf('Class  "%s" must implement "%s" interface.', $generatorClass, GeneratorInterface::class));
            }
            $generator->setOptions($routeOptions);
            $this->routeGenerators[$routeName] = $generator;
        } else {
            $generator = $this->routeGenerators[$routeName];
        }

        $path = $generator->makePath($routerParameters);
        if (!empty($query)) {

            $path .= "?" . http_build_query($query);
        }

        return $path;

    }

}