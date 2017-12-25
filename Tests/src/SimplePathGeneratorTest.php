<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\PathHandler\Regex;
use Fastero\Router\PathHandler\SimplePathGenerator;
use Fastero\Router\Router;
use PHPUnit\Framework\TestCase;

class SimplePathGeneratorTest extends TestCase
{

 public function testMakePathNoGroups(){
     $routeOptions = [

           "reverse" => ['path' =>"news/:id"]
     ];
     $generator = new SimplePathGenerator();

     $generator->setOptions($routeOptions);

     $path = $generator->makePath(['id' => 'someid']);
     $this->assertSame('news/someid', $path);
 }

    public function testMakePathEncoding(){
        $routeOptions = [

            "reverse" => ['path' =>"news(:id) _-~\[\]="]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath(['id' => 'someid8& _-#']);
        $this->assertSame('news%28someid8%26%20_-%23%29%20_-~%5B%5D%3D', $path);
    }


    public function testMakePathOptionalGroup(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/:id]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath([]);
        $this->assertSame('news', $path);

        $path = $generator->makePath(['id' => 'someid']);
        $this->assertSame('news/someid', $path);

    }

    public function testMakePathOnlyOptioanlGroup(){
        $routeOptions = [

            "reverse" => ['path' =>"[news/:id]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath([]);
        $this->assertSame('', $path);

        $path = $generator->makePath(['id' => 'someid']);
        $this->assertSame('news/someid', $path);

    }

    public function testMakePathNestedGroups(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/author[/id=:id][/name=:name]]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath([]);
        $this->assertSame('news', $path);

        $path = $generator->makePath(['id' => 'someid']);
        $this->assertSame('news/author/id%3Dsomeid', $path);


        $path = $generator->makePath(['name' => 'somename']);
        $this->assertSame('news/author/name%3Dsomename', $path);


        $path = $generator->makePath(['name' => 'somename', 'id' => 'someid']);
        $this->assertSame('news/author/id%3Dsomeid/name%3Dsomename', $path);

    }


}