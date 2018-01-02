<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\GeneratorException;
use Fastero\Router\Router;
use Fastero\Router\Tests\Fixtures\MatcherGenerator;
use Fastero\Router\Tests\Fixtures\MatcherMatch;

use PHPUnit\Framework\TestCase;

class RouterGenerateTest extends TestCase
{
    public static function setUpBeforeClass() {
        require_once($_ENV["TESTS_BASE_DIR"] . "/Fixtures/StubClasses.php");
    }

    public function setUp() {
        MatcherMatch::$returnParams = [];
    }


    public function testMakePathNoQuery() {
       $routes = [
            'route1' => [
                'type' => MatcherGenerator::class,
            ]
        ];
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $path = $router->makePath('route1',$data);
        $this->assertSame('{"name":"alexey","age":30}', $path);
    }

    public function testMakePathWithQuery() {
        $routes = [
            'route1' => [
                'type' => MatcherGenerator::class,
            ]
        ];
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $path = $router->makePath('route1',$data,['gh' => 1, 'w' => 'looch']);
        $this->assertSame('{"name":"alexey","age":30}?gh=1&w=looch', $path);
    }

    public function testMakePathWithDefaults() {
        $routes = [
            'route1' => [
                'type' => MatcherGenerator::class,
                'default' => [
                    'loka' => 'doka'
                ]
            ]
        ];
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $path = $router->makePath('route1',$data);
        $this->assertSame('{"loka":"doka","name":"alexey","age":30}', $path);
    }

    public function testMakePathWithDefaultsMergeDefaultsFalse() {
        $routes = [
            'route1' => [
                'type' => MatcherGenerator::class,
                'default' => [
                    'loka' => 'doka'
                ]
            ]
        ];
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $path = $router->makePath('route1',$data,[], false);
        $this->assertSame('{"name":"alexey","age":30}', $path);
    }


    public function testMakePathWrongRoute() {
        $this->expectException(GeneratorException::class);
        $this->expectExceptionMessage('Route "route2" not found in the configuration or empty"');
        $routes = [
            'route1' => [
                'type' => MatcherGenerator::class,
            ]
        ];
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $router->makePath('route2',$data);
    }
    public function testMakePathMatcherDoesNotSupportGenerationNoGeneratorProvided() {
        $this->expectException(GeneratorException::class);
        $this->expectExceptionMessage('"Fastero\Router\Tests\Fixtures\MatcherMatch" must implement "Fastero\Router\PathHandler\GeneratorInterface" interface.');
        $routes = [
            'route1' => [
                'type' => MatcherMatch::class,
            ]
        ];
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $path = $router->makePath('route1',$data);
        $this->assertSame('{"name":"alexey","age":30}', $path);
    }

   /* public function testMakePathMatcherDoesNotSupportGenerationGeneratorProvided() {
        $routes = [
            'route1' => [
                'type' => MatcherMatch::class,
               'reverse' =>[
                   'generator' => ''
               ]
            ]
        ];
        //$mockedClass::expects($this->once());
        $data = ['name' => "alexey", 'age'=> 30];
        $router = new Router($routes);
        $path = $router->makePath('route1',$data,[], false);
        $this->assertSame('{"name":"alexey","age":30}', $path);
    }*/
}