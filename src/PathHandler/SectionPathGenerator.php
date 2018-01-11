<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 23.12.17 16:20
 * Licensed under the MIT license
 */

namespace Fastero\Router\PathHandler;


use Fastero\Router\Exception\GeneratorException;
use Fastero\Router\Exception\MakePathException;
use Fastero\Router\Exception\ParseException;

/**
 * format:
 * section/:sectionName/[/filter[/id/:id][/name/:name]]
 *
 * [] - optional section, will be generated only if any parameter inside is set
 * :id - parameter name - will be replaced with actual parameter if given.
 * [a-zA-Z_0-9] characters are allowed, started with [a-zA-Z_]
 *
 * characters '[', ']', ':' - can be escaped with '\' if meant as literals
 *
 * examples:
 * news/:id  - "id" is required parameter
 *
 * news[/:author[/:year\::moth\::day]  - 'author' and 'year' are optional
 * results: news/alexey/1989:08:1987", "news/alexey", "news", but not "news/alexey/1989::"
 *
 */

class SectionPathGenerator implements GeneratorInterface
{


    protected const PART_TYPE_GROUP = 'group';
    protected const PART_TYPE_LITERAL = 'literal';
    protected const PART_TYPE_PARAMETER = 'parameter';


    protected $regPath;
    protected $compiled;
    protected $nameBegin;
    protected $nameAllowed;
    protected $parsed;

    public function setOptions(array $options) {
        if (empty($options['reverse']) or empty($options['reverse']['path'])) {
            throw new GeneratorException("Configuration must contain ['reverse']['path'] data");
        }
        $this->regPath = $options['reverse']['path'];
        $this->parsed = null;
    }

    public function makePath(array $urlParameters): string {
        if (is_null($this->parsed)) {
            $this->compile();
        }
        return $this->renderGroup($urlParameters, $this->parsed);
    }

    protected function compile() {
        $this->parse($this->regPath);
    }


    protected function parse($section) {
        $this->parsed = $this->parseGroup($section)[1];

    }

    protected function parseGroup($groupPath, $startPosition = 0, $root = true) {

        $parsingName = false;
        $parsingLiteral = false;

        $currentString = '';
        $currentGroup = [];
        $i = $startPosition - 1;
        $groupPathLength = strlen($groupPath);

        while ($i <= $groupPathLength) {
            $i++;
            $finishing = ($i == $groupPathLength);
            if(!$finishing){
                $char = $groupPath[$i];
            }else{
                $char = null;
            }

            if ($parsingName) {

                if (!$finishing && isset( SectionPathMatcher::PARAMETER_NAME_LETTERS[$char])
                    && ($currentString != '' || SectionPathMatcher::PARAMETER_NAME_LETTERS[$char])) {
                    $currentString .= $char;
                    continue;
                } else if ($currentString != '') {
                    $currentGroup[] = ['type' => static::PART_TYPE_PARAMETER, 'value' => $currentString];
                    $currentString = '';
                    $parsingName = false;
                } else {
                    throw new ParseException(sprintf('Illegal character "%s" in the parameter name, position "%d"', $char, $i));
                }
            }
            if ($parsingLiteral && (in_array($char, ['[', ']', ':']) || $finishing)) {

                $currentGroup[] = ['type' => static::PART_TYPE_LITERAL, 'value' => $currentString];
                $currentString = '';
                $parsingLiteral = false;

            }
            if($finishing) break;

            if ($char == '\\') {
                $i++;
                if ($i > $groupPathLength - 1) {
                    throw new ParseException(sprintf('Escaping character "\" at the end of the string"'));
                }
                $nextChar = $groupPath[$i];
                if ($nextChar != '/') {
                    $nextChar = rawurlencode($nextChar);
                }
                $currentString .= $nextChar;
                $parsingLiteral = true;
            } else if ($char == ':') {
                $parsingName = true;

            } else if ($char == '[') {

                [$i, $group] = $this->parseGroup($groupPath, $i + 1, false);
                $currentGroup[] = ['type' => static::PART_TYPE_GROUP, 'value' => $group];

            } else if ($char == ']') {

                if ($root) {
                    throw new ParseException(sprintf('Unexpected character "%s", position "%d"', $char, $i));
                }

                return [$i, $currentGroup];
            } else {
                if ($char != '/') {
                    $char = rawurlencode($char);
                }
                $currentString .= $char;
                $parsingLiteral = true;
            }
        }
        return [$i, $currentGroup];
    }

    protected function renderGroup($params, $group, $root = true) {
        $result = '';
        $hasAnyData = false;
        $paramNumber = 0;
        foreach ($group as $part) {
            if ($part['type'] == static::PART_TYPE_LITERAL) {
                $result .= $part['value'];
            } else if ($part['type'] == static::PART_TYPE_PARAMETER) {
                $paramNumber++;
                $name = $part['value'];
                if (isset($params[$name])) {
                    $result .= rawurlencode($params[$name]);
                    $hasAnyData = true;
                } else if ($root) {
                    throw new MakePathException(sprintf('Parameter "%s" is required', $name));
                }

            } else if ($part['type'] == static::PART_TYPE_GROUP) {
                $subgroup = $part['value'];
                $subgroupPath = $this->renderGroup($params, $subgroup, false);
                if ($subgroupPath != '') {
                    $hasAnyData = true;
                    $result .= $subgroupPath;
                }
            }
        }

        if ($hasAnyData || $root ) {
            return $result;
        } else{
            return '';
        }
    }


}