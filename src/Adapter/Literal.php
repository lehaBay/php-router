<?php


namespace Fastero\Router\Adapter;


class Literal extends AdapterAbstract
{

    public function match($method, $path, array $query = [])
    {

        $match = ($this->ruleData['full'] == $path);
        $match = $match && $this->methodMatch($method);

        /*even though this route can not parse any parameters there
        can be still some be still some default values and we still need
        to return theme and even validate*/
        $match = $match && !is_null($routeParams = $this->processParams());
        if ($match) {
            $query = $this->processQuery($query);
            $this->result['query'] = $query ?? [];

            /** @noinspection PhpUndefinedVariableInspection */
            $this->result['parameters'] = $routeParams;
            return $this->result;
        } else {
            return null;
        }
    }



}

