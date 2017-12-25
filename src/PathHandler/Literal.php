<?php


namespace Fastero\Router\PathHandler;


class Literal extends AbstractMatcher implements GeneratorInterface
{
    /**
     * @param $path
     * @param array $query
     * @return array|null
     */
    public function match($path, array $query = [])
    {
        if ($this->ruleData['full'] == $path) {
            return [];
        } else {
            return null;
        }
    }

    /**
     * Just return literal path, all the parameters will(and should) be ignored
     * @param array $urlParameters
     * @return string
     */
    public function makePath(array $urlParameters): string
    {

        return $this->ruleData['full'];
    }



}

