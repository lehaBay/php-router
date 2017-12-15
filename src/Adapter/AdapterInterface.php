<?php


namespace Fastero\Router\Adapter;


interface AdapterInterface
{

    public function setOptions(array $options);
    public function getOptions();

    /**
     * @param $method - HTTP method (GET|HEAD|POST etc.)
     * @param $path - URL excluding domain name and query string
     * @param array $query all the params of query string
     * @return array|null - params of route that match or null
     */
    public function match($method, $path, array $query = []);
    public function reset();

}