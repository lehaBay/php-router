<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 25.12.17 8:48
 * Licensed under the MIT license
 */

namespace Fastero\Router\Tests;


use Fastero\Router\Configuration\AbstractGenerator;
use Fastero\Router\Configuration\AbstractRouterGenerator;
use Fastero\Router\Exception\ConfigurationException;
use Fastero\Router\Exception\MatcherException;
use Fastero\Router\PathHandler\Literal;
use Fastero\Router\PathHandler\Regex;
use Fastero\Router\PathHandler\SectionPathMatcher;
use PHPUnit\Framework\TestCase;

class GeneratorsTest extends TestCase
{

    public function setUp() {
        parent::setUp();
        foreach ($this->getSimilarGenerators() as [$generator]){
            $reflection = new \ReflectionClass($generator);
            $instanceProperty = $reflection->getProperty('instance');
            $instanceProperty->setAccessible(true);
            $instanceProperty->setValue(null, null);
        }
    }

    public function testConfigRightInstance(){
        foreach ($this->getSimilarGenerators() as [$generatorClass]){
            $generator = $generatorClass::config('pattern');
            $this->assertSame($generatorClass, get_class($generator));
        }
    }

    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     * @param $type
     */
    public function testConfigOneParameter($generator, $type) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');

        $config = $generator->get();
        $this->assertSame($type, $config['type'], 'wrong matcher type');
        $this->assertSame(['pattern'], $config['rule'], 'wrong rule format');

    }
    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testParameterNoDefaultNoValidation($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->parameter('age');
        $config = $generator->get();
        $this->assertArrayNotHasKey('default', $config);
        $this->assertArrayNotHasKey('validate',$config);


    }

    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testParameterWithDefaultNoValidation($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->parameter('age', '25');
        $config = $generator->get();
        $this->assertEquals('25', $config['default']['age']);
        $this->assertArrayNotHasKey('validate',$config);


    }
    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testParameterNoDefaultWithValidation($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->parameter('age', null,"vregex");
        $config = $generator->get();
        $this->assertEquals('vregex', $config['validate']['age']);

    }

    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testQueryParameterNoDefaultNoValidation($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->queryParameter('age', true);
        $config = $generator->get();
        $this->assertEquals(['required' => true], $config['query']['parameters']['age']);
    }

    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testQueryParameterWithDefaultAndValidation($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->queryParameter('age', true,'25','vrule');
        $config = $generator->get();
        $this->assertEquals(['required' => true, 'default' => '25', 'validate' => 'vrule'],
            $config['query']['parameters']['age']);
    }
    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testSetQueryMatchMode($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->setQueryMatchMode(true);
        $config = $generator->get();
        $this->assertEquals(['strict_match' => true],
            $config['query']);
    }

    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testSetReversePattern($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->setReversePattern('reversePattern');
        $config = $generator->get();
        $this->assertEquals('reversePattern',
            $config['reverse']['path']);
    }
    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testSetEverything($generator, $type) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->queryParameter('age', true,'25','vrule');
        $generator->parameter('age', null,"ageRegex");
        $generator->parameter('name', null,"nameRegex");
        $generator->parameter('sex', 'male',"sexRegex");
        $generator->setQueryMatchMode(true);
        $generator->setReversePattern('reversePattern');
        $generator->setReverseGenerator('generatorClass');
        $generator->setController('controllerClass', 'method');
        $generator->setCustomParameter('_lang','fr');
        //$this->assertArraySubset()
        $config = $generator->get();

        $expected = array (
            'type' => $type,
            'rule' =>
                array (
                    0 => 'pattern',
                ),
            'controller' =>
                array (
                    'class' => 'controllerClass',
                    'method' => 'method',
                ),

            'validate' =>
                array (
                    'age' => 'ageRegex',
                    'name' => 'nameRegex',
                    'sex' => 'sexRegex',
                ),
            'default' => [
                'sex' => 'male'
            ],
            'reverse' =>
                array (
                    'path' => 'reversePattern',
                    'generator' => 'generatorClass',
                ),
            'query' =>
                array (
                    'strict_match' => true,
                    'parameters' =>
                        array (
                            'age' =>
                                array (
                                    'required' => true,
                                    'default' => '25',
                                    'validate' => 'vrule',
                                ),
                        ),
                ),
            '_lang' => 'fr',
        );

        $this->assertArraySubset($expected,$config);
    }
    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testParameterWithDefaultAndValidation($generator) {

        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator->parameter('age', '25',"vregex");
        $config = $generator->get();
        $this->assertEquals('vregex', $config['validate']['age']);
        $this->assertEquals('25', $config['default']['age']);

    }



    public function testConfigLiteralTwoParameters() {

        $generator = \Fastero\Router\Configuration\Literal::config('pattern', 'someExtra');

        $config = $generator->get();
        $this->assertSame(['pattern'], $config['rule'], 'wrong rule format');

    }

    public function testConfigRegexTwoParameters() {

        $generator = \Fastero\Router\Configuration\Regex::config('pattern', 'someExtra');

        $config = $generator->get();
        $this->assertSame(['pattern', 'someExtra'], $config['rule'], 'wrong rule format');

    }
    public function testConfigSectionTwoParameters() {

        $generator = \Fastero\Router\Configuration\Section::config('pattern', 'someExtra');

        $config = $generator->get();
        $this->assertSame(['pattern', 'someExtra'], $config['rule'], 'wrong rule format');

    }


    /**
     * @dataProvider getSimilarGenerators
     * @param $generator
     */
    public function testNewConfigWithoutCallingGet($generator) {
        $this->expectException(ConfigurationException::class);
        /** @var AbstractRouterGenerator $generator */
        $generator = $generator::config('pattern');
        $generator = $generator::config('anotherPattern');

    }


    public function getSimilarGenerators(){
        return[
            [\Fastero\Router\Configuration\Literal::class, Literal::class],
            [\Fastero\Router\Configuration\Regex::class, Regex::class],
            [\Fastero\Router\Configuration\Section::class, SectionPathMatcher::class],
        ];
    }
}