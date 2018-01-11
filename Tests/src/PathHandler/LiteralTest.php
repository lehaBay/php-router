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
use Fastero\Router\PathHandler\SectionPathMatcher;
use PHPUnit\Framework\TestCase;

class LiteralTest extends TestCase
{


    public function testMatchPrefixOnlyPathMatch() {
        $routeOptions = [

            "rule" => ['news/589'],
        ];

        $matcher = new Literal();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589');
        $this->assertNotNull($result);

    }
    public function testMatchPrefixAndPathPathMatch() {
        $routeOptions = [

            "rule" => ['news/589/', 'somethingelse'],
        ];

        $matcher = new Literal();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589/somethingelse');
        $this->assertNotNull($result);

    }

    public function testMatchPrefixOnlyPathNotMatch() {
        $routeOptions = [

            "rule" => ['news/589'],
        ];

        $matcher = new Literal();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('somepath');
        $this->assertNull($result);

    }

    public function testMatchPrefixAndPathPathNotMatch() {
        $routeOptions = [
            "rule" => ['news/589/', 'somethingelse'],
        ];

        $matcher = new Literal();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/589/');
        $this->assertNull($result);

    }

    public function testMakePathPrefixAndPath() {
        $routeOptions = [
            "rule" => ['news/589/', 'somethingelse'],
        ];

        $matcher = new Literal();
        $matcher->setOptions($routeOptions);
        $path = $matcher->makePath([]);

        $this->assertSame('news/589/somethingelse', $path);

    }

    public function testMakePathPrefix() {
        $routeOptions = [
            "rule" => ['news/589/'],
        ];

        $matcher = new Literal();
        $matcher->setOptions($routeOptions);
        $path = $matcher->makePath([]);

        $this->assertSame('news/589/', $path);

    }
}