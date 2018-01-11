<?php


namespace Fastero\Router\Configuration;


class Regex extends AbstractRouterGenerator
{
    protected static $instance = null;

    protected $matcherClass = \Fastero\Router\PathHandler\Regex::class;


    /**
     * start creating route configuration. Must be finished with ->get()
     * @param $prefix - static path prefix, immutable part of the url or
     * if there is no static part first paramteter can be a pattern
     * please note: if there is only static part use Literal matcher instead
     * @param null $pattern - properly formatted url pattern
     * @return static
     */
    public static function config($prefix, $regex = null) {
        $me = static::getInstance();
        $me->setPathRule($prefix, $regex);
        return $me;
    }


}