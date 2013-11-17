<?php

namespace DTL\CodeMover;

use DTL\CodeMover\Tokenizer\Php\PhpToken;
use DTL\CodeMover\Tokenizer\Php\PhpTokenList;

class PhpTokenListTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();
        $this->t1 = new PhpToken($this->line, 'FOOBAR', 'arf');
        $this->t2 = new PhpToken($this->line, 'WHITESPACE', 'barf');
        $this->t3 = new PhpToken($this->line, 'FOOBAR', 'garf');
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
}
