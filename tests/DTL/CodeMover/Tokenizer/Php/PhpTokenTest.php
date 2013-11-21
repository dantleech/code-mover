<?php

namespace DTL\CodeMover;

use DTL\CodeMover\Tokenizer\Php\PhpToken;

class PhpTokenTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();
    }

    public function testIsEndOfStatement()
    {
        $token = new PhpToken('SINGLE_CHAR', ';');
        $this->assertTrue($token->isEndOfStatement());
    }

    public function testGet()
    {
        $token = new PhpToken('SINGLE_CHAR', ';');
        $this->assertEquals('SINGLE_CHAR', $token->getType());
        $this->assertEquals(';', $token->getValue());
    }
}
