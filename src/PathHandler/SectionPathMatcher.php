<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 25.12.17 5:45
 * Licensed under the MIT license
 */

namespace Fastero\Router\PathHandler;


use Fastero\Router\Exception\MatcherException;
use Fastero\Router\Exception\ParseException;

class SectionPathMatcher extends AbstractMatcher implements GeneratorInterface
{
    /**
     * @var SimplePathGenerator
     */
    protected $pathGenerator;
    /**
     * @var Regex
     */
    protected $regexMatcher;
    protected $compiledRegex;

    public function __construct($pathGenerator = null, $regexMatcher = null) {
        $this->pathGenerator = $pathGenerator ?? new SimplePathGenerator();
        $this->regexMatcher = $regexMatcher ?? new Regex();
    }

    public function setOptions(array $options) {

        parent::setOptions($options);
        $this->pathGenerator->setOptions(['reverse'=>['path'=>$this->ruleData['full']]]);
        $this->compiledRegex = null;
    }

    /**
     * check if $path match against the rule supported by the concrete matcher and return array of
     * parsed parameters if it matches or null otherwise
     * @param $path - URL excluding domain name and query string
     * @return array|null - params of route that match or null
     */
    public function match($path) {
        if(is_null($this->compiledRegex)){
            $this->compile();
        }

        return $this->regexMatcher->match($path);
    }


    public function makePath(array $urlParameters): string {
        return $this->ruleData['prefix'] . $this->pathGenerator->makePath($this->ruleData['rest']);
    }
    protected function compile() {
        $this->compiledRegex = $this->makeRegex($this->ruleData['rest']);
        $this->regexMatcher->setOptions(['rule'=>[$this->ruleData['prefix'], $this->compiledRegex]]);
    }
    protected function makeRegex($path){
        $startParameter = "(?<";
        $endParameter = ">[^/]+)";
        $startGroup = "(?:";
        $endGroup = ")?";

        $parsingName = false;
        $level = 0;
        $currentString = '';

        $i = -1;
        $groupPathLength = strlen($path);

        while ($i <= $groupPathLength) {
            $i++;
            $finishing = ($i == $groupPathLength);
            if(!$finishing){
                $char = $path[$i];
            }else{
                $char = null;
            }

            if ($parsingName) {
                if (!$finishing && isset( SimplePathGenerator::PARAMETER_NAME_LETTERS[$char])
                    && ($currentString != '' || SimplePathGenerator::PARAMETER_NAME_LETTERS[$char])) {
                    $currentString .= $char;
                    continue;
                } else if ($currentString != '') {
                    $currentString .= $endParameter;
                    $parsingName = false;
                } else {
                    throw new ParseException(sprintf('Illegal character "%s" in the parameter name, position "%d"', $char, $i));
                }
            }

            if($finishing) break;

            if ($char == '\\') {
                $i++;
                if ($i > $groupPathLength - 1) {
                    throw new MatcherException(sprintf('Escaping character "\" at the end of the string"'));
                }

                $nextChar = preg_quote($path[$i], '(');
                $currentString .= $nextChar;
            } else if ($char == ':') {
                $currentString .= $startParameter;
                $parsingName = true;

            } else if ($char == '[') {
                $level++;
                $currentString .= $startGroup;

            } else if ($char == ']') {
                $level --;
                $currentString .= $endGroup;
            } else {
                $char = preg_quote($char, '(');
                $currentString .= $char;
            }
        }

        if($level !== 0){
            throw new MatcherException("number of open and close brackets doesn't match");
        }
        return $currentString;
    }

}