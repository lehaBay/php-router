<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 25.12.17 5:45
 * Licensed under the MIT license
 */

namespace Fastero\Router\PathHandler;


use Fastero\Router\Exception\MatcherException;

class SectionPathMatcher extends AbstractMatcher implements GeneratorInterface
{

    const PARAMETER_NAME_LETTERS = [
        'a' => true, 'b' => true, 'c' => true, 'd' => true, 'e' => true, 'f' => true, 'g' => true, 'h' => true, 'i' => true,
        'j' => true, 'k' => true, 'l' => true, 'm' => true, 'n' => true, 'o' => true, 'p' => true, 'q' => true, 'r' => true,
        's' => true, 't' => true, 'u' => true, 'v' => true, 'w' => true, 'x' => true, 'y' => true, 'z' => true, 'A' => true,
        'B' => true, 'C' => true, 'D' => true, 'E' => true, 'F' => true, 'G' => true, 'H' => true, 'I' => true, 'J' => true,
        'K' => true, 'L' => true, 'M' => true, 'N' => true, 'O' => true, 'P' => true, 'Q' => true, 'R' => true, 'S' => true,
        'T' => true, 'U' => true, 'V' => true, 'W' => true, 'X' => true, 'Y' => true, 'Z' => true, '_' => true,
        0 => false, 1 => false, 2 => false, 3 => false, 4 => false, 5 => false, 6 => false, 7 => false, 8 => false, 9 => false,
    ];

    /**
     * @var SectionPathGenerator
     */
    protected $pathGenerator;

    protected $compiledRegex;

    public function __construct($pathGenerator = null) {
        $this->pathGenerator = $pathGenerator ?? new SectionPathGenerator();
    }

    public function setOptions(array $options) {

        parent::setOptions($options);
        $this->pathGenerator->setOptions(['reverse' => ['path' => $this->ruleData['full']]]);
        $this->compiledRegex = null;
    }

    /**
     * check if $path match against the rule supported by the concrete matcher and return array of
     * parsed parameters if it matches or null otherwise
     * @param $path - URL excluding domain name and query string
     * @return array|null - params of route that match or null
     */
    public function match($path) {
        if (is_null($this->compiledRegex)) {
            $this->compile();
        }


        return $this->processRegex($path);
    }

    protected function compile() {
        $this->compiledRegex = $this->ruleData['prefix'] . $this->makeRegex($this->ruleData['rest']);
    }

    protected function makeRegex($path) {
        $parsingName = false;
        $firstLetter = false;
        $level = 0;
        $currentString = '';

        $i = -1;
        $groupPathLength = strlen($path);

        while ($i <= $groupPathLength) {
            $i++;
            $finishing = ($i == $groupPathLength);
            if (!$finishing) {
                $char = $path[$i];
            } else {
                $char = null;
            }

            if ($parsingName) {
                if ($firstLetter && !(self::PARAMETER_NAME_LETTERS[$char] ?? false)) {
                    throw new MatcherException(sprintf('Illegal character "%s" in the parameter name, position "%d"', $char, $i));
                }
                if (!$finishing && isset(self::PARAMETER_NAME_LETTERS[$char])) {
                    $currentString .= $char;
                    $firstLetter = false;
                    continue;
                } else {
                    $currentString .= ">[^/]+)";
                    $parsingName = false;
                }

            }

            if ($finishing) break;

            if ($char == '\\') {
                $i++;
                if ($i > $groupPathLength - 1) {
                    throw new MatcherException(sprintf('Escaping character "\" at the end of the string"'));
                }

                $nextChar = preg_quote($path[$i], '(');
                $currentString .= $nextChar;
            } else if ($char == ':') {
                $currentString .= "(?<";
                $parsingName = true;
                $firstLetter = true;

            } else if ($char == '[') {
                $level++;
                $currentString .= "(?:";

            } else if ($char == ']') {
                $level--;
                $currentString .= ")?";
            } else {
                $char = preg_quote($char, '(');
                $currentString .= $char;
            }
        }

        if ($level !== 0) {
            throw new MatcherException("Number of open and close brackets doesn't match");
        }
        return $currentString;
    }

    protected function processRegex($path) {
        /**
         * if there are urlencoded slashes in the path they could cause a problems.
         * So replace them with something unique so after rawurldecode we can distinguish
         * actual slashes from those being encoded.
         */

        $slashPlaceholder = null;
        if (strpos($path, '%2F')) {
            $i = 0;
            do {
                $slashPlaceholder = 's42' . $i;
                $i++;

                $isUnique = strpos($path, $slashPlaceholder) === false;
                $isUnique = $isUnique && strpos($this->compiledRegex, $slashPlaceholder) === false;

            } while (!$isUnique);
            $path = str_replace('%2F', $slashPlaceholder, $path);
        }

        $path = rawurldecode($path);

        $regex = "(^" . $this->compiledRegex . "$)";


        if (preg_match($regex, $path, $matches)) {
            $resultParams = [];
            foreach ($matches as $paramName => $paramValue) {
                if (!is_int($paramName) && $paramValue !== '') {
                    if (!is_null($slashPlaceholder)) {
                        $paramValue = str_replace($slashPlaceholder, '/', $paramValue);
                    }
                    $resultParams[$paramName] = $paramValue;
                }
            }
            return $resultParams;
        } else {
            return null;
        }
    }

    public function makePath(array $urlParameters): string {
        return $this->pathGenerator->makePath($urlParameters);
    }

}