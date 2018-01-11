<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 03.01.18 18:48
 * Licensed under the MIT license
 */

namespace Fastero\Router\Configuration;


class AbstractRouterGenerator extends AbstractGenerator
{
    protected $matcherClass = null;
    protected $validations = [];
    protected $defaults = [];
    protected $query = [];
    protected $queryParameters = [];
    protected $reverse = [];
    public function setPathRule($prefixOrPattern, $pattern = null){
        $rule = [$prefixOrPattern];
        if(!is_null($pattern)){
            $rule[] = $pattern;
        }
        $this->resultData['rule'] = $rule;
        return $this;
    }
    public function parameter($name, $default = null, $validationRule = null){
        if(!is_null($default)){
            $this->defaults[$name] = $default;
        }
        if(!is_null($validationRule)){
            $this->validations[$name] = $validationRule;
        }
        return $this;
    }
    public function queryParameter($name, $required, $default = null, $validationRule = null){
        $this->queryParameters[$name]['required'] = $required;
        if(!is_null($default)){
            $this->queryParameters[$name]['default'] = $default;
        }
        if(!is_null($validationRule)){
            $this->queryParameters[$name]['validate'] = $validationRule;
        }
        return $this;
    }

    public function setQueryMatchMode($strict = true){
        $this->query['strict_match'] = $strict;
        return $this;
    }

    /**
     * set pattern to generate urls. Some generators can use 'path' for this purposes
     * but even they will use this patten instead if set
     * @param $pattern
     */
    public function setReversePattern($pattern){
        $this->reverse['path'] = $pattern;
    }

    public function setReverseGenerator($generatorClass){
        $this->reverse['generator'] = $generatorClass;
    }
    public function setController($class, $method){
        $this->resultData['controller'] = ['class' => $class, 'method' => $method];
        return $this;
    }

    public function setCustomParameter($name, $value){
        $this->resultData[$name] = $value;
    }

    protected function assemble(){
        $this->resultData['type'] = $this->matcherClass;
        if(!empty( $this->queryParameters)){
            $this->query['parameters'] = $this->queryParameters;
        }

        if(!empty($this->defaults)){
            $this->resultData['default'] = $this->defaults;
        }
        if(!empty($this->validations)){
            $this->resultData['validate'] = $this->validations;
        }
        if(!empty($this->reverse)){
            $this->resultData['reverse'] = $this->reverse;
        }
        if(!empty($this->query)){
            $this->resultData['query'] = $this->query;
        }

    }

    protected function reset()
    {
        parent::reset();
        $this->validations = [];
        $this->defaults = [];
        $this->query = [];
        $this->queryParameters = [];
    }

    public function get(){
        $this->assemble();
        return parent::get();
    }
}