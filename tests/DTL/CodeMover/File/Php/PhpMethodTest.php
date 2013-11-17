<?php

namespace DTL\CodeMover\File\Php;

use DTL\CodeMover\File\Php\PhpMethod;

class PhpMethodTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->file = $this->getMockBuilder('DTL\CodeMover\AbstractFile')
            ->disableOriginalConstructor()->getMock();

        $this->phpMethod = new PhpMethod($this->file, 'public', 'foobar', 'Foobar $foobar');
        $this->phpMethod->addLine('$this->foobar = $foobar;');
    }

    public function testAddLines()
    {
        $lines = $this->phpMethod->getLines();

        $this->assertContains('public function foobar(Foobar $foobar)', $lines);
        $this->assertContains('$this->foobar = $foobar;', $lines);
    }

    public function testGetType()
    {
        $type = $this->phpMethod->getType();
        $this->assertEquals('public', $type);
    }

    public function testGetArgumentString()
    {
        $res = $this->phpMethod->getArgumentString();
        $this->assertEquals('Foobar $foobar', $res);
    }
}
