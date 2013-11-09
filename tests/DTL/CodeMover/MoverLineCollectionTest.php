<?php

namespace DTL\CodeMover;

class MoverLineCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line1 = $this->getMockBuilder('DTL\CodeMover\MoverLine')
            ->disableOriginalConstructor()->getMock();
        $this->line2 = $this->getMockBuilder('DTL\CodeMover\MoverLine')
            ->disableOriginalConstructor()->getMock();

        $this->lineCollection = new MoverLineCollection(array(
            $this->line1,
            $this->line2,
        ));
    }

    public function testReplace()
    {
        $this->line1->expects($this->once())
            ->method('replace')
            ->with('pattern', 'replacement');
        $this->line2->expects($this->once())
            ->method('replace')
            ->with('pattern', 'replacement');

        $this->lineCollection->replace('pattern', 'replacement');
    }

    public function testDelete()
    {
        $this->line1->expects($this->once())
            ->method('delete');
        $this->line2->expects($this->once())
            ->method('delete');

        $this->lineCollection->delete();
    }
}
