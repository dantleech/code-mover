<?php

namespace DTL\CodeMover;

class PhpTokenTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\MoverLine')
            ->disableOriginalConstructor()->getMock();
    }

    public function testIsEndOfStatement()
    {
        $token = new PhpToken($this->line, 'SINGLE_CHAR', ';');
        $this->assertTrue($token->isEndOfStatement());
    }

    public function testGet()
    {
        $token = new PhpToken($this->line, 'SINGLE_CHAR', ';');
        $this->assertEquals('SINGLE_CHAR', $token->getType());
        $this->assertEquals(';', $token->getValue());
    }
}
