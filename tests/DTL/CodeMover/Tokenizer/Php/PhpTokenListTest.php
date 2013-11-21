<?php

namespace DTL\CodeMover;

use DTL\CodeMover\Tokenizer\Php\PhpToken;
use DTL\CodeMover\Tokenizer\Php\PhpTokenList;
use DTL\CodeMover\LineCollection;

class PhpTokenListTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();
        $this->t1 = new PhpToken('FOOBAR', 'arf');
        $this->t1->setLine($this->line);
        $this->t2 = new PhpToken('WHITESPACE', 'barf');
        $this->t2->setLine($this->line);
        $this->t3 = new PhpToken('FOOBAR', 'garf');
        $this->t3->setLine($this->line);
    }

    public function testFilterByType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $tokenList = $tokenList->filterByType('FOOBAR');

        $this->assertEquals(array($this->t1, $this->t3), array_values($tokenList->toArray()));
    }

    public function testValuesByType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $values = $tokenList->getValuesByType('FOOBAR');

        $this->assertEquals(array(
            'arf', 'garf'
        ), $values);
    }

    public function testSeekType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $token = $tokenList->seekType(T_WHITESPACE)->getToken();
        $this->assertEquals('barf', $token->getValue());
    }

    public function testSeekValue()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $token = $tokenList->seekValue('barf')->getToken();
        $this->assertEquals('WHITESPACE', $token->getType());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find token
     */
    public function testSeekValueNotFound()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $tokenList->seekValue('NOTKNOWN');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find token
     */
    public function testSeekTypeNotFound()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $tokenList->seekType('NOTKNOWN');
    }

    public function testLines()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $lines = $tokenList->getLines();
        $this->assertNotNull($lines);
        $this->assertCount(1, $lines);
        $this->assertSame($this->line, $lines->first());
    }

    public function testToken()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $token = $tokenList->getToken();
        $this->assertSame($token, $this->t1);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No token found at offset
     */
    public function testTokenNotFound()
    {
        $tokenList = new PhpTokenList();
        $token = $tokenList->getToken();
    }

    public function testGetValue()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $value = $tokenList->seekType(T_WHITESPACE)->getValue();
        $this->assertEquals('barf', $value);
    }

    public function testGetType()
    {
        $tokenList = new PhpTokenList(array($this->t1, $this->t2, $this->t3));
        $type = $tokenList->getType();
        $this->assertEquals('FOOBAR', $type);
    }

    public function provideBomb()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider provideBomb
     */
    public function testBomb($bomb)
    {
        $tokenList = new PhpTokenList();
        if ($bomb) {
            $this->setExpectedException('RuntimeException');
        }

        $tokenList->bomb($bomb)->seekValue('asdasdasd');
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
        $res = $tokens->castArray();

        $this->assertEquals($expected, $res);
    }
}







