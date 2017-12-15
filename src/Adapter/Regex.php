<?php


namespace Fastero\Router\Adapter;


class Regex extends AdapterAbstract
{

    public function match($method, $path, array $query = [])
    {
        $match = $this->prefixMatch($path);
        $match = $match && !is_null($pathParams = $this->processRegex($path));
        /** @noinspection PhpUndefinedVariableInspection */
        $match = $match && !is_null($routeParams = $this->processParams($pathParams));
        $match = $match && !is_null($routeQuery = $this->processQuery($query));

        if($match){
            /** @noinsection PhpUndefinedVariableInspection */
            $this->result['parameters'] = $routeParams;
            /** @noinspection PhpUndefinedVariableInspection */
            $this->result['query'] = $routeQuery;

            return $this->result;
        }else{
            return null;

        }
    }

    protected function processRegex($path)
    {
        $regex = "~^" . str_replace("~", '$\~', $this->options['path']);
        if (!empty($this->options['regexModifiers'])) $regex = $regex . $this->options['regexModifiers'];
        if(preg_match($regex,$path, $matches)){
            $resultParams = [];
            foreach ($matches as $paramName => $paramValue) {
                if(!is_int($paramName) && $paramValue !== ''){
                    $resultParams[$paramName] = rawurldecode($paramValue);
                }
            }
            return $resultParams;
        }else{
            return null;
        }
    }

}

