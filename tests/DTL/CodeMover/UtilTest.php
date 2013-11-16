<?php

namespace DTL\CodeMover;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function provideTokenIntToString()
    {
        return array(
            array(T_WHITESPACE, 'WHITESPACE'),
            array(123872349234, null, true),
        );
    }

    /**
     * @dataProvider provideTokenIntToString
     */
    public function testTokenIntToString($tokenInt, $expected, $outOfBounds = false)
    {
        if ($outOfBounds) {
            $this->setExpectedException('InvalidArgumentException',
                'Unknown token type'
            );
        }
        $res = Util::tokenTypeIntToString($tokenInt);
        $this->assertEquals($expected, $res);
    }

    public function provideTokenStringToInt()
    {
        return array(
            array('WHITESPACE', 377),
            array('T_WHITESPACE', 377),
            array('WXAXXSAHITESPACE', null, true),
        );
    }

    /**
     * @dataProvider provideTokenStringToInt
     */
    public function testTokenStringToInt($tokenString, $expected, $outOfBounds = false)
    {
        if ($outOfBounds) {
            $this->setExpectedException('InvalidArgumentException',
                'Unknown token type'
            );
        }
        $res = Util::tokenTypeStringToInt($tokenString);
        $this->assertEquals($expected, $res);
    }
}

