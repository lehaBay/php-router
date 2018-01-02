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
     * @param array $options - options can be different for different adapters but mostly they are the same:
     *
     * - rule - array containing static prefix and expression to compare against url, e.g. ["user/info"] or ["news/", ":id/author/:author_id"]

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