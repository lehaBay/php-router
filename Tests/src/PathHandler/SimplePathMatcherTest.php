<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 25.12.17 8:48
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\MatcherException;
use Fastero\Router\PathHandler\SectionPathMatcher;
use PHPUnit\Framework\TestCase;

class SimplePathMatcherTest extends TestCase
{


    public function testMatchNoOptional() {
        $routeOptions = [

            "rule" => ['news/', ':_id52_'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/');
        $this->assertNull($result);

        $result = $matcher->match('news/5der');
        $this->assertEquals(['_id52_' => '5der'], $result);

    }

    public function testMatchWithOptionalAndRequiredNoneGiven() {
        $routeOptions = [

            "rule" => ['news/', ':_id52_[/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/');
        $this->assertNull($result);

    }

    public function testMatchWithOptionalAndRequiredOptionalGiven() {
        $routeOptions = [

            "rule" => ['news/', ':_id52_[/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/author/alexey');
        $this->assertNull($result);

    }

    public function testMatchWithOptionalAndRequiredRequiredGiven() {
        $routeOptions = [

            "rule" => ['news/', ':_id52_[/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der');
        $this->assertEquals(['_id52_' => '5der'], $result);
    }

    public function testMatchWithOptionalAndRequiredBothGivenIncomplete() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/author');
        $this->assertNull($result);
    }

    public function testMatchWithOptionalAndRequiredBothGiven() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/author/carambola');
        $this->assertEquals(['_id52_' => '5der', 'author_name' => 'carambola'], $result);
    }

    public function testMatchEmptyGroup() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[]/author/:author_name'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/author/carambola');
        $this->assertEquals(['_id52_' => '5der', 'author_name' => 'carambola'], $result);
    }

    public function testMatchGroupWithoutParametersPresentInUrl() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[/lola]/author/:author_name'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/lola/author/carambola');
        $this->assertEquals(['_id52_' => '5der', 'author_name' => 'carambola'], $result);
    }

    public function testMatchGroupWithoutParametersNotPresentInUrl() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[/lola]/author/:author_name'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/author/carambola');
        $this->assertEquals(['_id52_' => '5der', 'author_name' => 'carambola'], $result);
    }

    public function testMatchGroupWithoutParametersInsightAnotherGroupPresentInUrlParametersGiven() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[[/lola]/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/lola/author/carambola');
        $this->assertEquals(['_id52_' => '5der', 'author_name' => 'carambola'], $result);
    }

    public function testMatchGroupWithoutParametersInsightAnotherGroupPresentInUrlNoParametersGiven() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[[/lola]/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der/lola');
        $this->assertNull($result);
    }

    public function testMatchGroupWithoutParametersInsightAnotherGroupNotPresentInUrlNoParametersGiven() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[[/lola]/author/:author_name]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der');
        $this->assertEquals(['_id52_' => '5der'], $result);
    }

    public function testMatchRegexPathContainsSpecialChars() {
        $routeOptions = [
            "rule" => ['ne&ws/', ':_id52_[/(author=:author_name)]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('ne%26ws/5der/(author=ale%29%26%60~_-%2Fxey%5B%29)');
        $this->assertEquals(['_id52_' => '5der', 'author_name' => 'ale)&`~_-/xey[)'], $result);
    }
    public function testMatchRegexPathContainsEscapedChars() {
        $routeOptions = [
            "rule" => ['news/', '\[:_id52\]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/[5der]');
        $this->assertEquals(['_id52' => '5der'], $result);
    }
    public function testMatchTwoParametersInOptionalBothGiven() {
        $routeOptions = [
            "rule" => ['news', '[/optional/:some/:another]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/optional/firstParam/secondParam');
        $this->assertSame(['some' => 'firstParam', 'another' => 'secondParam'], $result);

    }

    public function testMatchTwoParametersInOptionalGroupOneGiven() {
        $routeOptions = [
            "rule" => ['news', '[/optional/:some/:another]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/optional/firstParam');
        $this->assertNull($result);

    }
    public function testMatchTwoParametersInOptionalGroupNoneGiven() {
        $routeOptions = [
            "rule" => ['news', '[/optional/:some/:another]'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news');
        $this->assertSame([],$result);

    }

    public function testMatchMissingCloseBracket() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_[/optional/:some'],
        ];
        $this->expectException(MatcherException::class);
        $this->expectExceptionMessage("Number of open and close brackets doesn't match");
        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $result = $matcher->match('news/5der');

    }

    public function testMatchMissingOpenBracket() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_/optional/:some]'],
        ];
        $this->expectException(MatcherException::class);
        $this->expectExceptionMessage("Number of open and close brackets doesn't match");
        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $matcher->match('news/5der');

    }

    public function testMatchWrongParameterFirstLetter() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_/optional/:1some'],
        ];
        $this->expectException(MatcherException::class);
        $this->expectExceptionMessage('Illegal character "1" in the parameter name, position "18"');
        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $matcher->match('news/5der');

    }
    public function testMatchParameterStartedAtTheVeryEndOfRegex() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_/optional/:'],
        ];
        $this->expectException(MatcherException::class);
        $this->expectExceptionMessage('Illegal character "" in the parameter name, position "18"');
        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $matcher->match('news/5der');

    }
    public function testMatchEscapeCharacterAtTheVeryEndOfPath() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_/optional/\\'],
        ];
        $this->expectException(MatcherException::class);
        $this->expectExceptionMessage('Escaping character "\" at the end of the string"');
        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);

        $matcher->match('news/5der');

    }
    public function testMakePath() {
        $routeOptions = [
            "rule" => ['news/', ':_id52_/optional/:param'],
        ];

        $matcher = new SectionPathMatcher();
        $matcher->setOptions($routeOptions);
        $path = $matcher->makePath(['_id52_'=> '52', 'param'=> 'some']);
        $this->assertSame('news/52/optional/some', $path);

    }

}