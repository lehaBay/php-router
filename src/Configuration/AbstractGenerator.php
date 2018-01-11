<?php

namespace Fastero\Router\Configuration;


use Fastero\Router\Exception\ConfigurationException;

abstract class AbstractGenerator
{
    protected static $instance = null;
    protected $resultData = [];
    protected $reset = true;

    /**
     * @return static
     */
    protected static function getInstance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        if (!static::$instance->reset) {
            throw  new ConfigurationException("Finish previous configuration before starting new one");
        }
        static::$instance->reset = false;
        return static::$instance;
    }

    protected function reset(){
        $this->reset = true;
        $this->resultData = [];
    }

    public function get(){
        $data = $this->resultData;
        $this->reset();
        return $data;
    }
}