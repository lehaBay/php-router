<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\RouterException;
use Fastero\Router\Exception\RouterNotFoundException;
use Fastero\Router\Router;
use Fastero\Router\Tests\Fixtures\MatcherMatch;
use Fastero\Router\Tests\Fixtures\MatcherNotMatch;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public static function setUpBeforeClass() {
        require_once($_ENV["TESTS_BASE_DIR"] . "/Fixtures/StubClasses.php");
    }

    public function setUp() {
        MatcherMatch::$returnParams = [];
    }


    public function testMatchEmptyRoutes() {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No routes found');
        $router = new Router([]);
        $router->match("GET", "about/alexey", []);
    }

    public function testMatchMethodArrayMatch() {
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                "method" => ["GET"],
            ],
        ];

        $router = new Router($config);
        $match = $router->match("GET", "about/alexey", []);
        $this->assertEquals('route1',$match['name']);
    }

    public function testMatchMethodScalarMatch() {
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                "method" => "GET",
            ],
        ];

        $router = new Router($config);
        $match = $router->match("GET", "about/alexey", []);
        $this->assertEquals('route1',$match['name']);
    }

    public function testMatchMethodNotMatch() {
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                "method" => ["post"],
            ],
        ];
        $this->expectException(RouterNotFoundException::class);
        $this->expectExceptionMessage('No routes found for path "about/alexey", method "GET"');
        $router = new Router($config);
        $router->match("GET", "about/alexey", []);
    }

    public function testMatchFirstMatch() {
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],

            ],
            'route2' => [
                "type" => MatcherMatch::class,
                "rule" => [],
            ],
        ];

        $router = new Router($config);
        $match = $router->match("GET", "about/alexey", []);
        $this->assertEquals('route1',$match['name']);
    }


    public function testMatchNoMatch() {
        $config = [
            'route1' => [
                "type" => MatcherNotMatch::class,
                "rule" => [],
            ],
        ];
        $this->expectException(RouterNotFoundException::class);
        $this->expectExceptionMessage('No routes found for path "noop", method "GET"');
        $router = new Router($config);
        $router->match("GET", "noop", []);
    }


}