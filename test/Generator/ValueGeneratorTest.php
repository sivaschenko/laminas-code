<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator;

use Laminas\Code\Generator\ValueGenerator;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 *
 * @covers \Laminas\Code\Generator\ValueGenerator
 */
class ValueGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testPropertyDefaultValueConstructor()
    {
        $valueGenerator = new ValueGenerator();
        $this->isInstanceOf($valueGenerator, 'Laminas\Code\Generator\ValueGenerator');
    }

    public function testPropertyDefaultValueIsSettable()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('foo');
        $this->assertEquals('foo', $valueGenerator->getValue());
    }

    public function testPropertyDefaultValueCanHandleStrings()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('foo');
        $this->assertEquals('\'foo\'', $valueGenerator->generate());
    }

    public function testPropertyDefaultValueCanHandleArray()
    {
        $expectedSource = <<<EOS
array(
    'foo',
)
EOS;

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(array('foo'));
        $this->assertEquals($expectedSource, $valueGenerator->generate());
    }

    public function testPropertyDefaultValueCanHandleUnquotedString()
    {
        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue('PHP_EOL');
        $valueGenerator->setType('constant');
        $this->assertEquals('PHP_EOL', $valueGenerator->generate());

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(5);
        $this->assertEquals('5', $valueGenerator->generate());

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue(5.25);
        $this->assertEquals('5.25', $valueGenerator->generate());
    }

    public function testPropertyDefaultValueCanHandleComplexArrayOfTypes()
    {
        $targetValue = array(
            5,
            'one' => 1,
            'two' => '2',
            'constant1' => '__DIR__ . \'/anydir1/anydir2\'',
            array(
                'baz' => true,
                'foo',
                'bar',
                array(
                    'baz1',
                    'baz2',
                    'constant2' => 'ArrayObject::STD_PROP_LIST',
                )
            ),
            new ValueGenerator('PHP_EOL', 'constant')
        );

        $expectedSource = <<<EOS
array(
    5,
    'one' => 1,
    'two' => '2',
    'constant1' => __DIR__ . '/anydir1/anydir2',
    array(
        'baz' => true,
        'foo',
        'bar',
        array(
            'baz1',
            'baz2',
            'constant2' => ArrayObject::STD_PROP_LIST,
        ),
    ),
    PHP_EOL,
)
EOS;

        $valueGenerator = new ValueGenerator();
        $valueGenerator->initEnvironmentConstants();
        $valueGenerator->setValue($targetValue);
        $generatedTargetSource = $valueGenerator->generate();
        $this->assertEquals($expectedSource, $generatedTargetSource);
    }

    public function testPropertyDefaultValueCanHandleArrayWithUnsortedKeys()
    {
        $value = array(
            1 => 'a',
            0 => 'b',
            'c',
            7 => 'd',
            3 => 'e'
        );

        $valueGenerator = new ValueGenerator();
        $valueGenerator->setValue($value);
        $expectedSource = <<<EOS
array(
    1 => 'a',
    0 => 'b',
    'c',
    7 => 'd',
    3 => 'e',
)
EOS;

        $this->assertEquals($expectedSource, $valueGenerator->generate());
    }

    /**
     * @group 6023
     *
     * @dataProvider getEscapedParameters
     */
    public function testEscaping($input, $expectedEscapedValue)
    {
        $this->assertSame($expectedEscapedValue, ValueGenerator::escape($input, false));
    }

    /**
     * Data provider for escaping tests
     *
     * @return string[][]
     */
    public function getEscapedParameters()
    {
        return array(
            array('\\', '\\\\'),
            array("'", "\\'"),
            array("\\'", "\\\\\\'"),
        );
    }
}
