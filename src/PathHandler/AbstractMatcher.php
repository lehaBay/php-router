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
     * - validators - array where keys are parameter names and values are regex to validate against
     * e.g. ["name" => "[a-zA-Z]+", "id" => "[0-9]+"]
     * - defaults - default values for optional elements. key - name of parameter, value is value. It can be
     * also used to pass some additional parameters;
     * - query - settings for query parameters
     * => [
     * parameters = [
     *  'param1_name' => ['required' => true, 'validate' =>'[0-9]+', default => '1'],
     *  'param2_name' => ['required' => true], //no validation
     *  'param3_name' => [], //required=false by default, no validation
     *  ],
     *  'strict_match' => true, //order of params will be checked and no other parameters accepted. FALSE by default
     *  'strict_generate' => true, //order of params will preserved and params will be limited by those specified in the parameters section
     *
     * ]
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