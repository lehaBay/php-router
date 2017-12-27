<?php


namespace Fastero\Router;



use Fastero\Router\Exception\GeneratorException;
use Fastero\Router\PathHandler\GeneratorInterface;
use Fastero\Router\PathHandler\MatcherInterface;
use Fastero\Router\Exception\ProcessRouterException;
use Fastero\Router\Exception\RouterException;
use Fastero\Router\Exception\RouterNotFoundException;

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
     */
    protected $routes;

    protected $routeProcessors;
    protected $routeGenerators;

    /**
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }


    /**
     * @param $method - HTTP method (GET|HEAD|POST etc.)
     * @param $uriPath - URL excluding domain name and query string
     * @param array $queryParams all the params of query string
     * @return array - params of route that match or null
     */
    public function findMatch($method, $uriPath, $queryParams = []){

        try{
            $matchRouteData = [];
            foreach ($this->routes as $routeName => $routeOptions){
                $routeParams = $this->processRoute($routeOptions, $method, $uriPath, $queryParams);
                if(!is_null($routeParams)){
                    $matchRouteData['routeName'] = $routeName;
                    $matchRouteData['routeData'] = $routeParams;
                    $matchRouteData['routeOptions'] = $routeOptions;
                    return $matchRouteData;
                }
            }
        } catch (\Exception $exception){
            throw new ProcessRouterException(sprintf('Error occurred during processing "%s" route', $routeName), 0, $exception);
        }

        if(empty($this->routes)){
            throw new RouterException(sprintf('No routes found'));
        }else{
            throw new RouterNotFoundException(sprintf('No routes found for path "%s", method "%s".', $uriPath, $method));
        }

    }

    public function makePath($routeName, $parameters = [], $query = [], $mergeDefault = true){
        if(empty($this->routes[$routeName])){
            throw new GeneratorException(sprintf('Route "%s" not found in the configuration or empty".', $routeName));
        }
        $routeOptions = $this->routes[$routeName];

        if($mergeDefault){
            $routerParameters = $this->mergeRouteDefaultParams($parameters,$routeOptions);
        }else{
            $routerParameters = $parameters;
        }

        $processorClass = $routeOptions['type'];
        if(empty($this->routeGenerators[$routeName])){

            if(!class_exists($processorClass)){
                throw new GeneratorException(sprintf('Unknown route type "%s" for route "%s".', $processorClass,$routeName));
            }
            /** @var MatcherInterface $generator */
            $generator = new $processorClass();

            if (!$generator instanceof GeneratorInterface){
                throw new GeneratorException(sprintf('Class  "%s" must implement "%s" interface.', $processorClass,GeneratorInterface::class));
            }
            $generator->setOptions($routeOptions);
            $this->routeGenerators[$routeName] = $generator;
        }else{
            $generator = $this->routeGenerators[$routeName];
        }

        $path = $generator->makePath($routerParameters);
        if(!empty($query)){

            $path .= "?" . http_build_query($query);
        }

        return $path;

    }

    protected function processRoute($routeOptions, $method, $uriPath, $queryParams = [] ){

        if(!$this->methodMatch($method,$routeOptions))
            return null;

        $processorClass = $routeOptions['type'];

        /** @var MatcherInterface $routeProcessor */
        $routeProcessor = $this->routeProcessors[$processorClass] ??
            $this->routeProcessors[$processorClass] = new $processorClass;

        $routeProcessor->reset();
        $routeProcessor->setOptions($routeOptions);


        $pathParameters = $routeProcessor->match($uriPath);
        if(is_null($pathParameters)){
            return null;
        }

        $routeParams = $this->processParams($pathParameters,$routeOptions);
        if(is_null($routeParams)){
            return null;
        }

        $queryParams = $this->processQuery($queryParams, $routeOptions);
        if(is_null($queryParams)){
            return null;
        }

        return ['parameters'=> $routeParams, 'query' => $queryParams];
    }

    /**
     * @param $query
     * @return array|null - array of accepted query parameters or null if validation failed
     */
    protected function processQuery($query, $routeOptions){
        $queryConfig = $routeOptions['query'] ?? [];

        if(empty($queryConfig)){
            return $query;
        }
        /*if(!empty($queryConfig['parameters'])){
            foreach ($queryConfig['parameters'] as $parameterName => $data){

            }
        }*/
        //TODO: validate query and everything

        return null;

    }



    protected function methodMatch($methodToCheck, $routeOptions){
        $methods = $routeOptions['methods'] ?? [];

        if(empty($methods)){
            return true;
        }
        foreach ($methods as $method){
            if($method == $methodToCheck) return true;
            if($method == "GET" and $methodToCheck = "HEAD") return true;
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
    protected function processParams($pathParams, $routeOptions){
        $allParams = $this->mergeRouteDefaultParams($pathParams,$routeOptions);
        $validations = $routeOptions['validate'] ?? [];

        foreach ($validations as $paramName => $regex){
            if(isset($allParams[$paramName])){
                $value = $allParams[$paramName];
                if(!preg_match($regex, $value)){
                    return null;
                }
            }
        }
        return $allParams;
    }

    protected function mergeRouteDefaultParams($pathParams, $routeOptions){
        $defaultParams = $routeOptions['default'] ?? [];
        return array_merge($defaultParams, $pathParams);
    }

}