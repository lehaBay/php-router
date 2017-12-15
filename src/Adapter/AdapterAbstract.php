<?php


namespace Fastero\Router\Adapter;


use Fastero\Router\Exception\ProcessRouteException;

abstract class AdapterAbstract implements AdapterInterface
{

    protected $options;
    protected $method;
    protected $ruleData;
    protected $result = [];

    /**
     * @param array $options - options can be different for different adapters but most of theme the same:
     *
     * - rule - array containing static prefix and expression to compare against url, e.g. user/info or ["news/", "{id}"]
     * - validators - array where keys are parameters names and values are regexs to validate against
     * e.g. ["name" => "[a-zA-Z]+", "id" => "[0-9]+"]
     * - defaults - default values for optional elements. key - name of parameter, value is value. It can be
     * also used to pass some additional parameters;
     * - query - parameters for query parameters
     *
     */
    public function setOptions(array $options){
        $this->options = $options;
        $this->setRuleData();
    }

    public function getOptions() {
        return $this->options;
    }

    protected function setRuleData(){
        if(empty($this->options['rule'])){
            throw new ProcessRouteException("Parameter \"rule\" is required");
        }
        if(count($this->options['rule']) > 1){
            $staticPrefix = $this->options['rule'][0];
        }
        $this->ruleData = [
            'prefix' => $staticPrefix ?? '',
            'full' => implode("", $this->options['rule'])
        ];
    }


    /**
     * @param $query
     * @return array|null - array of accepted query parameters or null if validation failed
     */
    protected function processQuery($query){
        $queryConfig = $this->options['query'] ?? [];

        if(empty($queryConfig)){
            return $query;
        }
        /*if(!empty($queryConfig['parameters'])){
            foreach ($queryConfig['parameters'] as $parameterName => $data){

            }
        }*/

        return null;

    }

    protected function prefixMatch($path){
        $length = strlen($this->ruleData['prefix']);

        if($length > 0){
            return $this->ruleData['prefix'] === substr($path,0,$length);
        }else{
            return true;
        }
    }

    protected function methodMatch($methodToCheck){
        $methods = $this->options['methods'] ?? [];

        if(empty($methods)){
            return true;
        }
        foreach ($methods as $method){
            if($method== $methodToCheck) return true;
            if($method == "GET" and $methodToCheck = "HEAD") return true;
        }
        return false;
    }

    protected function processParams($pathParams = []){
        $defaultParams = $this->options['default'] ?? [];
        $validations = $this->options['validate'] ?? [];
        $allParams = array_merge($defaultParams, $pathParams);

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

    public function reset()
    {
        $this->options = [];
        $this->result = [];
    }
}