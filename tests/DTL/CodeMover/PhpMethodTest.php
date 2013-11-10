<?php

namespace DTL\CodeMover;

use DTL\CodeMover\PhpMethod;

class PhpMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testAddLines()
    {
        $file = $this->getMockBuilder('DTL\CodeMover\MoverFile')
            ->disableOriginalConstructor()->getMock();
        $phpMethod = new PhpMethod($file, 'public', 'foobar', 'Foobar $foobar');
        $phpMethod->addLine('$this->foobar = $foobar');
        $lines = $phpMethod->getLines();
        $this->assertContains('public function foobar(Foobar $foobar)', $lines);
        $this->assertContains('$this->foobar = $foobar', $lines);
    }
}
