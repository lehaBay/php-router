<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\ProcessRouterException;
use Fastero\Router\Exception\RouterException;
use Fastero\Router\Exception\RouterNotFoundException;
use Fastero\Router\Router;
use Fastero\Router\Tests\Fixtures\AbstractMatcherImplementation;
use Fastero\Router\Tests\Fixtures\MatcherMatch;
use Fastero\Router\Tests\Fixtures\MatcherNotMatch;
use PHPUnit\Framework\TestCase;

class AbstractMatcherTest extends TestCase
{
    public static function setUpBeforeClass() {
        require_once($_ENV["TESTS_BASE_DIR"] . "/Fixtures/StubClasses.php");
    }

    public function setUp() {
        MatcherMatch::$returnParams = [];
    }


    public function testSetOptionsEmptyRule() {
        $this->expectException(ProcessRouterException::class);
        $this->expectExceptionMessage('Parameter "rule" is required');
        $options = [
            'type'=> AbstractMatcherImplementation::class,
        ];
        $matcher = new AbstractMatcherImplementation();
        $matcher->setOptions($options);
    }
    public function testSetOptionsEmptyArrayRule() {
        $this->expectException(ProcessRouterException::class);
        $this->expectExceptionMessage('Parameter "rule" is required');
        $options = [
            'type' => AbstractMatcherImplementation::class,
            'rule' => []
        ];
        $matcher = new AbstractMatcherImplementation();
        $matcher->setOptions($options);
    }

    public function testGetOptions() {

        $options = [
            'type'=> AbstractMatcherImplementation::class,
            'rule' => ['some/path']
        ];
        $matcher = new AbstractMatcherImplementation();
        $matcher->setOptions($options);
        $getOptions = $matcher->getOptions();

        $this->assertSame($options,$getOptions);
    }
}