<?php
namespace Fastero\Router\Tests\Fixtures;

use Fastero\Router\PathHandler\AbstractMatcher;
use Fastero\Router\PathHandler\GeneratorInterface;
use Fastero\Router\PathHandler\MatcherInterface;
use function foo\func;

/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 31.12.17 9:02
 * Licensed under the MIT license
 */
class MatcherGenerator implements MatcherInterface, GeneratorInterface{

    public function makePath(array $urlParameters): string {
        return json_encode($urlParameters);
    }

    public function setOptions(array $options) {

    }

    public function getOptions() {

    }


    public function match($path) {

    }


}

class AbstractMatcherImplementation extends AbstractMatcher {

    public function getRule(){
        return $this->ruleData;
    }
    public function match($path) {
        //do nothing
    }
}
class MatcherMatch implements MatcherInterface
{
    static $returnParams = [];

    public function match($path){
        return static::$returnParams;
    }

public function setOptions(array $options) {}
public function getOptions() {}

}
class MatcherNotMatch implements MatcherInterface
{


    public function match($path){
        return null;
    }

    public function setOptions(array $options) {}
    public function getOptions() {}
    public function reset() {}
}
function someValidationReturnTrue($value){
    return true;
}

function someValidationReturnFalse($value){
    return false;
}

function someValidationReturnParamEqualValue($value, $returnMe){
    return $value == $returnMe;
}