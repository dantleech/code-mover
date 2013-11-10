<?php

namespace DTL\CodeMover;

use DTL\CodeMover\PhpTokenList;

class PhpTokenListTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMockBuilder('DTL\CodeMover\MoverLine')
            ->disableOriginalConstructor()->getMock();
        $this->t1 = new PhpToken($this->line, 'FOOBAR', 'arf');
        $this->t2 = new PhpToken($this->line, 'BARFOO', 'barf');
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
        $values = $tokenList->valuesByType('FOOBAR');

        $this->assertEquals(array(
            'arf', 'garf'
        ), $values);
    }
}
