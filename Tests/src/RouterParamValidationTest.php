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

class RouterParamValidationTest extends TestCase
{

    public static function setUpBeforeClass() {
        require_once ($_ENV["TESTS_BASE_DIR"] . "/Fixtures/StubClasses.php");
    }

    public function setUp() {
        MatcherMatch::$returnParams = []; //just make sure
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

}