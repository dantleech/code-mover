<?php

namespace DTL\CodeMover;

class RegExResultTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line = $this->getMock(
            'DTL\CodeMover\LineInterface'
        );
    }

    public function testApply()
    {
        $result = new RegExResult(array('fullmatch', 'foo', 'bar'));
        $result->setLine($this->line);

        $me = $this;
        $expectedLine = $this->line;
        $result->apply(function ($line, $match1, $match2) use ($me, $expectedLine) {
            $this->assertSame($expectedLine, $line);
            $this->assertEquals('foo', $match1);
            $this->assertEquals('bar', $match2);
        });
    }
}
