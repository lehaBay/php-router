<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\RouterNotFoundException;
use Fastero\Router\Router;
use Fastero\Router\Tests\Fixtures\MatcherMatch;
use PHPUnit\Framework\TestCase;

class RouterQueryValidationTest extends TestCase
{

    public static function setUpBeforeClass() {
        require_once ($_ENV["TESTS_BASE_DIR"] . "/Fixtures/StubClasses.php");
    }

    public function setUp() {
        MatcherMatch::$returnParams = [];
    }

    public function testMatchValidationQueryValid(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"]
                    ],
                ]
            ],
        ];


        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $match = $router->match("GET","",['size' => '25.5', 'name' => '#xf']);

        $this->assertEquals(['size' => '25.5', 'name' => '#xf'], $match['query']);
    }

    public function testMatchValidationQueryInvalidStrict(){
        $this->expectException(RouterNotFoundException::class);
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"]
                    ],

                    'strict_match' => True,
                ]
            ],
        ];


        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $router->match("GET","",['size' => '25.5', 'name' => '#xf']);

    }

    public function testMatchValidationQuerySecondIsValidStrict(){

        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"]
                    ],

                    'strict_match' => true,
                ]
            ],
            'route2' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\,\d+)?"],
                        'name' => []
                    ],

                    'strict_match' => true,
                ]
            ],
        ];


        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $match = $router->match("GET","",['size' => '25,5', 'name' => '#xf']);
        $this->assertEquals('route2', $match['name']);
    }

    public function testMatchValidationQueryStrictWrongOrder(){
        $this->expectException(RouterNotFoundException::class);
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"],
                        'name' => ['required' => true, 'validate' => "\w+"]
                    ],

                    'strict_match' => true,
                ]
            ],

        ];


        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $router->match("GET","",['name' => 'lxf', 'size' => '25.5']);

    }

    public function testMatchValidationQueryNotStrictWrongOrder(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"],
                        'name' => ['required' => true, 'validate' => "\w+"]
                    ],

                ]
            ],

        ];

        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $match = $router->match("GET","",['name' => 'lxf', 'size' => '25.5']);
        $this->assertEquals(['name' => 'lxf', 'size' => '25.5'], $match['query']);
    }

    public function testMatchValidationQueryStrictWithOptional(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"],
                        'name' => []
                    ],

                ]
            ],

        ];

        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $match = $router->match("GET","",['size' => '25.5', 'name' => 'lxf']);
        $this->assertEquals(['size' => '25.5', 'name' => 'lxf'], $match['query']);
    }

    public function testMatchValidationQueryStrictNotEnoughParameters(){
        $this->expectException(RouterNotFoundException::class);
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"],
                        'name' => ['required' => true, 'validate' => "\w+"]
                    ],

                    'strict_match' => true,
                ]
            ],

        ];


        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $router->match("GET","",['size' => '25.5']);
    }

    public function testMatchValidationQueryRequiredIsMissing(){
        $this->expectException(RouterNotFoundException::class);
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'query' => [
                    'parameters' =>[
                        'size' => ['required' => true, 'validate' => "\d+(\.\d+)?"],
                        'name' => ['required' => true, 'validate' => "\w+"]
                    ],

                    'strict_match' => false,
                ]
            ],

        ];


        MatcherMatch::$returnParams = [];
        $router = new Router($config);
        $router->match("GET","",['size' => '25.5', 'go' => 'to']);
    }
}