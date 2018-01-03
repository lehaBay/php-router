<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 16.12.17 5:52
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Exception\GeneratorException;
use Fastero\Router\Exception\ParseException;
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


    public function testMakePathOptionalGroupNoParamsGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/:id]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath([]);
        $this->assertSame('news', $path);



    }

    public function testMakePathOptionalGroupParamGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/:id]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath(['id' => 'someid']);
        $this->assertSame('news/someid', $path);



    }

    public function testMakePathOnlyOptioanlGroupNoParamsGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"[news/:id]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath([]);
        $this->assertSame('', $path);
    }
    public function testMakePathOnlyOptioanlGroupOptionalParamGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"[news/:id]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);


        $path = $generator->makePath(['id' => 'someid']);
        $this->assertSame('news/someid', $path);
    }

    public function testMakePathNestedGroupsNoOptionalGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/author[/id=:id][/name=:name]]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath([]);
        $this->assertSame('news', $path);



    }
    public function testMakePathNestedGroupsOneOptionalGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/author[/id=:id][/name=:name]]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);

        $path = $generator->makePath(['id' => 'someid']);
        $this->assertSame('news/author/id%3Dsomeid', $path);

    }
    public function testMakePathNestedGroupsAllOptionalGiven(){
        $routeOptions = [

            "reverse" => ['path' =>"news[/author[/id=:id][/name=:name]]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);


        $path = $generator->makePath(['name' => 'somename', 'id' => 'someid']);
        $this->assertSame('news/author/id%3Dsomeid/name%3Dsomename', $path);


    }
    public function testMakePathSetOptionsNoReversePathGiven(){

        $this->expectException(GeneratorException::class);
        $this->expectExceptionMessage('Configuration must contain [\'reverse\'][\'path\'] data');
        $routeOptions = [

            "reverse" => []
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);





    }
    public function testMakePathWrongParameterName(){

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Illegal character "5" in the parameter name, position "6"');
        $routeOptions = [

            "reverse" => ['path' =>"news/:5d"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);
        $generator->makePath([]);

    }
    public function testMakePathEscapeCharacterAtTheEndOfTheString(){

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Escaping character "\" at the end of the string"');
        $routeOptions = [

            "reverse" => ['path' =>"news\\"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);
        $generator->makePath([]);

    }

    public function testMakePathUnexpectedCharacter(){

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unexpected character "]", position "4"');
        $routeOptions = [

            "reverse" => ['path' =>"news]"]
        ];
        $generator = new SimplePathGenerator();

        $generator->setOptions($routeOptions);
        $generator->makePath([]);

    }
}