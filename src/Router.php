<?php


namespace Fastero\Router;



use Fastero\Router\Adapter\AdapterInterface;
use Fastero\Router\Exception\ProcessRouteException;
use Fastero\Router\Exception\RouteException;
use Fastero\Router\Exception\RouteNotFoundException;

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

    /**
     * Router constructorm.
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
            $routeProcessors = [];
            $match = null;
            $matchRouteData = [];
            foreach ($this->routes as $routeName => $routeData){

                /**
                 * @var $routeProcessor AdapterInterface
                 */
                $processorClass = $routeData['type'];

                $routeProcessor = $routeProcessors[$processorClass] ??
                    $routeProcessors[$processorClass] = new $processorClass;

                $routeProcessor->setOptions($routeData);
                $match = $routeProcessor->match($method, $uriPath, $queryParams);
                if(!is_null($match)){
                    $matchRouteData = $routeData;
                    $matchRouteData['routeName'] = $routeName;
                    $matchRouteData['routeParameters'] = $match;
                }

            }
        } catch (\Exception $exception){
            throw new ProcessRouteException(sprintf('Error occurred during processing "%s" route', $routeName), 0, $exception);
        }

        if(is_null($match)){
            if(empty($this->routes)){
                throw new RouteException(sprintf('No routes found'));
            }else{
                throw new RouteNotFoundException(sprintf('No routes found for path "%s", method "%s".', $uriPath, $method));
            }
        }

        return $matchRouteData;
    }



}