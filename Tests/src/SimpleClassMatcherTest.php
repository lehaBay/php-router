<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 25.12.17 8:48
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\PathHandler\SectionPathMatcher;
use PHPUnit\Framework\TestCase;

class SimpleClassMatcherTest extends TestCase
{


    public function testMakePathNoOptional() {
        $routeOptions = [

            "rule" => ['news/', ':_id52_']
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/');
        $this->assertNull($result);

        $result = $matcher->match('news/5der');
        $this->assertEquals(['_id52_'=>'5der'],$result);

    }
    public function testMakePathWithOptional() {
        $routeOptions = [

            "rule" => ['news/', ':_id52_[/author/:author_name]']
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/');
        $this->assertNull($result);

        $result = $matcher->match('news/5der');
        $this->assertEquals(['_id52_'=>'5der'],$result);

        $result = $matcher->match('news/5der/author');
        $this->assertNull($result);

        $result = $matcher->match('news/5der/author/carambola');
        $this->assertEquals(['_id52_'=>'5der', 'author_name'=>'carambola'],$result);

        $result = $matcher->match('news/author/carambola');
        $this->assertNull($result);


    }
}