<?php


namespace Fastero\Router\PathHandler;


interface MatcherInterface
{

    public function setOptions(array $options);

    public function getOptions();

    /**
     * check if $path match against the rule supported by the concrete matcher and return array of
     * parsed parameters if it matches or null otherwise
     * @param $path - raw URL excluding domain name and query string
     * @return array|null - params of route that match or null
     */
    public function match($path);

}