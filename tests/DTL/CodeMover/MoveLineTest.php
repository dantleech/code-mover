<?php

namespace DTL\CodeMover;

class MoverLineTest extends \PHPUnit_Framework_TestCase
{
    protected $moverFile;

    public function setUp()
    {
        $this->moverFile = $this->getMockBuilder(
            'DTL\CodeMover\MoverFile'
        )->disableOriginalConstructor()->getMock();
    }

    public function provideMatch()
    {
        return array(
            array('namespace Foo', 'namespace', true),
            array('namespace Foo', '.*namespace.*Foo', true),
            array('foobar', '.*namespace', false),
        );
    }

    /**
     * @dataProvider provideMatch
     */
    public function testMatch($line, $pattern, $isMatch)
    {
        $line = new MoverLine($this->moverFile, $line);
        $res = $line->match($pattern);
        $this->assertEquals($isMatch, $res);
    }
}

