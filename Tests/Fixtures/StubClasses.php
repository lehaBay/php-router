<?php
namespace Fastero\Router\Tests\Fixtures;

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
        // TODO: Implement setOptions() method.
    }

    public function getOptions() {
        // TODO: Implement getOptions() method.
    }

    /**
     * check if $path match against the rule supported by the concrete matcher and return array of
     * parsed parameters if it matches or null otherwise
     * @param $path - raw URL excluding domain name and query string
     * @return array|null - params of route that match or null
     */
    public function match($path) {
        // TODO: Implement match() method.
    }

    /**
     * reset matcher so it could be used again with different options
     * @return null
     */
    public function reset() {
        // TODO: Implement reset() method.
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
public function reset() {}
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

function someValidationReturnParam($value, $returnMe){
    return $returnMe;
}