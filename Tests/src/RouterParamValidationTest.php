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
use PHPUnit\Framework\TestCase;

class RouterParamValidationTest extends TestCase
{

    public static function setUpBeforeClass() {
        require_once ($_ENV["TESTS_BASE_DIR"] . "/Fixtures/StubClasses.php");
    }

    public function setUp() {
        MatcherMatch::$returnParams = [];
    }

    public function testMatchValidationNoRules(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);


        $match = $router->match("GET","",[]);
        $this->assertEquals(MatcherMatch::$returnParams, $match['parameters']);
    }

    public function testMatchValidationParamsValid(){
        $config = [
            'route1' => [
               "type" => MatcherMatch::class,
               "rule" => [],
               'validate' => [
                   'name' => "[a-z]+",
                   'age' => "[0-9]+"
               ]
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);


        $match = $router->match("GET","",[]);
        $this->assertEquals(MatcherMatch::$returnParams, $match['parameters']);
    }


    public function testMatchValidationParamsBothValidFirstMatch(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => "[a-z]+",
                    'age' => "[0-9]+"
                ]
            ],
            'route2' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => "[a-z]+",
                    'age' => "[0-9]+"
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);


        $match = $router->match("GET","",[]);
        $this->assertEquals('route1', $match['name']);
    }

    public function testMatchValidationParamsSecondValidSecondMatch(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => "[a-z]+",
                    'age' => "[a-z]+"
                ]
            ],
            'route2' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => "[a-z]+",
                    'age' => "[0-9]+"
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);


        $match = $router->match("GET","",[]);
        $this->assertEquals('route2', $match['name']);
    }

    public function testMatchValidationParamsNotValid(){
        $this->expectException(RouterNotFoundException::class);
        $this->expectExceptionMessage("No routes found for path \"\", method \"GET\".");
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => "\d+",
                    'age' => "\d+"
                ]
            ],
            'route2' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => "\d+",
                    'age' => "\d+"
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);
        $router->match("GET","",[]);
    }

    public function testMatchValidationParamsInvalidRegex(){
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Error occurred during processing "route1" route: "Error parsing validation regex "(^[a-z$)", message: "preg_match(): Compilation failed: missing terminating ] for character class at offset 6"');
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'someparam' => "[a-z",
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['someparam' => 'somevalue'];
        $router = new Router($config);
        $router->match("GET","",[]);

    }


    public function testMatchValidationParamsCallableNotValid(){
        $this->expectException(RouterNotFoundException::class);
        $this->expectExceptionMessage("No routes found for path \"\", method \"GET\".");
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => ['callback' => 'Fastero\Router\Tests\Fixtures\someValidationReturnFalse'],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);
        $router->match("GET","",[]);
    }
    public function testMatchValidationParamsCallableValid(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'name' => ['callback' => 'Fastero\Router\Tests\Fixtures\someValidationReturnTrue'],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['name' => 'alexey', 'age' => '30'];
        $router = new Router($config);
        $result = $router->match("GET","",[]);
        $this->assertSame('route1',$result['name']);
    }

    public function testMatchValidationParamsCallableCheckIfTrueValid(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'isTrue' => ['callback' => 'Fastero\Router\Tests\Fixtures\someValidationReturnParamEqualValue', 'parameters' => [true]],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['isTrue' => true];
        $router = new Router($config);
        $result = $router->match("GET","",[]);
        $this->assertSame('route1',$result['name']);
    }
    public function testMatchValidationParamsCallableCheckIfFalseValid(){
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'isFalse' => ['callback' => 'Fastero\Router\Tests\Fixtures\someValidationReturnParamEqualValue', 'parameters' => [false]],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['isFalse' => false];
        $router = new Router($config);
        $result = $router->match("GET","",[]);
        $this->assertSame('route1',$result['name']);
    }


    public function testMatchValidationParamsCallableCheckIfFalseNotValid(){
        $this->expectException(RouterNotFoundException::class);
        $this->expectExceptionMessage("No routes found for path \"\", method \"GET\".");
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'isFalse' => ['callback' => 'Fastero\Router\Tests\Fixtures\someValidationReturnParamEqualValue', 'parameters' => [false]],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['isFalse' => true];
        $router = new Router($config);
        $router->match("GET","",[]);

    }


    public function testMatchValidationParamsCallableInvalidCallback(){
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Error occurred during processing "route1" route: "Validation rule contains callback but it isn\'t callable"');
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'someparam' => ['callback' => 'FunctionDoesNotExist'],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['someparam' => 'somevalue'];
        $router = new Router($config);
        $router->match("GET","",[]);

    }

    public function testMatchValidationParamsInvalidValidationRule(){
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Error occurred during processing "route1" route: "Validation rule is incorrect"');
        $config = [
            'route1' => [
                "type" => MatcherMatch::class,
                "rule" => [],
                'validate' => [
                    'someparam' => ['thereisnocallbackgiven'],
                ]
            ],
        ];
        MatcherMatch::$returnParams = ['someparam' => 'somevalue'];
        $router = new Router($config);
        $router->match("GET","",[]);

    }
}