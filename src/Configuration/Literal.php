<?php


namespace Fastero\Router\Configuration;


class Literal extends AbstractRouterGenerator
{
    protected static $instance = null;
    protected $matcherClass = \Fastero\Router\PathHandler\Literal::class;


    /**
     * start creating route configuration. Must be finished with ->get()
     * @param $path - static url path
     * @return static
     */
    public static function config($path) {
        $me = static::getInstance();
        $me->setPathRule($path);
        return $me;
    }


}