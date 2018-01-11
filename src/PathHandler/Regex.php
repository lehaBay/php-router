<?php


namespace Fastero\Router\PathHandler;

use Fastero\Router\Exception\MatcherException;

/**
 * Class Regex
 * @package Fastero\Router
 */
class Regex extends AbstractMatcher
{

    public function match($path, array $query = []) {

        if ($this->prefixMatch($path)) {
            $pathParams = $this->processRegex($path);
            if (!is_null($pathParams)) {
                return $pathParams;
            }
        }

        return null;
    }

    protected function processRegex($path) {
        $path = rawurldecode($path);
        $regex = "(^" . preg_quote($this->ruleData['prefix']) . $this->ruleData['rest'] . "$)";
        if (!empty($this->options['regexModifiers'])) $regex = $regex . $this->options['regexModifiers'];
        try {

            $match = (1 === preg_match($regex, $path, $matches));
        } catch (\Exception $exception) {
            throw new MatcherException(sprintf('Error parsing matching regex "%s", message: "%s', $regex, $exception->getMessage()), 0, $exception);
        }

        if ($match) {
            $resultParams = [];
            foreach ($matches as $paramName => $paramValue) {
                if (!is_int($paramName) && $paramValue !== '') {
                    $resultParams[$paramName] = $paramValue;
                }
            }
            return $resultParams;
        } else {
            return null;
        }
    }


}

