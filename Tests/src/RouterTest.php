<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\PathHandler\Regex;
use Fastero\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{

 public function testAll(){
     $config = [
       'route1' => [
           "type" => Regex::class,
           "rule" => ['about/', "(?<name>\w+)"]
       ]
     ];
     $router = new Router($config);


     $match = $router->findMatch("GET","about/alexey",[]);
     //var_dump($match);
     $this->assertTrue(true);
 }



}