<?php


namespace Fastero\Router\PathHandler;


use Fastero\Router\Exception\ProcessRouterException;

abstract class AbstractMatcher implements MatcherInterface
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
        $this->ruleData = null;
        $this->processRule();
    }

    public function getOptions() {
        return $this->options;
    }

    protected function processRule(){
        if(empty($this->options['rule'])){
            throw new ProcessRouterException("Parameter \"rule\" is required");
        }
        $staticPrefix = '';
        if(count($this->options['rule']) > 1){
            $staticPrefix = $this->options['rule'][0];
            unset($this->options['rule'][0]);
        }
        $rest = implode("", $this->options['rule']);
        $this->ruleData = [
            'prefix' => $staticPrefix,
            'rest' => $rest,
            'full' => $staticPrefix . $rest
        ];
    }


    protected function prefixMatch($path){
        $length = strlen($this->ruleData['prefix']);

        if($length > 0){
            return $this->ruleData['prefix'] === substr($path,0,$length);
        }else{
            return true;
        }
    }

    public function reset()
    {
        $this->options = [];
        $this->result = [];
    }

}