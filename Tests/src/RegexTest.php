<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 25.12.17 8:48
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\MatcherException;
use Fastero\Router\PathHandler\Literal;
use Fastero\Router\PathHandler\Regex;
use Fastero\Router\PathHandler\SectionPathMatcher;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{


    public function testMatchPrefixOnlyPathMatch() {
        $routeOptions = [

            "rule" => ['news/589'],
        ];

        $matcher = new Regex();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589');
        $this->assertNotNull($result);

    }
    public function testMatchPrefixAndPathPathMatch() {
        $routeOptions = [

            "rule" => ['news/589/', 'somethingelse'],
        ];

        $matcher = new Regex();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589/somethingelse');
        $this->assertSame([],$result);

    }
    public function testMatchPrefixAndPathPathMatchActualRegexNoParameters() {
        $routeOptions = [

            "rule" => ['news/589/', 'somethingelse(.[0-9])+'],
        ];

        $matcher = new Regex();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589/somethingelse.8.7');
        $this->assertSame([],$result);

    }

    public function testMatchPrefixAndPathPathMatchActualRegexWithParameters() {
        $routeOptions = [

            "rule" => ['news/589/', 'somethingelse(?<number>(.[0-9])+)'],
        ];

        $matcher = new Regex();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589/somethingelse.8.7');
        $this->assertSame(['number' => '.8.7'],$result);
    }

    public function testMatchPrefixAndPathPathMatchActualRegexWithParametersDoesNotMatch() {
        $routeOptions = [

            "rule" => ['news/589/', 'somethingelse(?<number>(.[0-9])+)'],
        ];

        $matcher = new Regex();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589/ooops');
        $this->assertNull($result);
    }

    public function testMatchPrefixAndPathPathMatchBadRegex() {
        $this->expectException(MatcherException::class);
        $this->expectExceptionMessage('Error parsing matching regex "(^news/589/somethingelse[a$)", message: ');
        $routeOptions = [

            "rule" => ['news/589/', 'somethingelse[a'],
        ];

        $matcher = new Regex();
        $matcher->setOptions($routeOptions);

        $matcher->match('news/589/somethingelse.8.7');
    }
}