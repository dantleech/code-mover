<?php

namespace DTL\CodeMover\Tokenizer\Php;

use DTL\CodeMover\LineCollection;
use DTL\CodeMover\Line;
use DTL\CodeMover\Tokenizer\Php\PhpArray;

class PhpArrayTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->phpArray = new PhpArray();
    }

    public function provideCastArray()
    {
        return array(
            array('array(\'foobar\' => "barfoo", $foobar => $poovar);', array(
                '\'foobar\'' => '"barfoo"',
                '$foobar' => '$poovar',
            )),
            array('array(\'foobar\', "barfoo", $foobar, $poovar);', array(
                '\'foobar\'', '"barfoo"', '$foobar', '$poovar',
            )),
        );
    }

    /**
     * @dataProvider provideCastArray
     */
    public function testCastArray($line, $expected)
    {
        $lineCollection = new LineCollection;
        $line = new Line($lineCollection, $line);
        $tokens = $line->tokenize();
        $this->phpArray->loadTokenList($tokens);

        $this->assertEquals($expected, $this->phpArray->toArray());
    }
}
