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

    public function testGetLineNoSingle()
    {
        $lc = new MoverLineCollection(array(
            $this->line1
        ));
        $this->line1->expects($this->once())
            ->method('getLineNo');
        $this->line1->getLineNo();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage requires a single element
     */
    public function testGetLineNoMultiple()
    {
        $this->lineCollection->getLineNo();
    }

    public function testGetLineNoEmpty()
    {
        $lc = new MoverLineCollection;
        $lc->getLineNo();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage requires a single element
     */
    public function testTokenizeStatementNoMultiple()
    {
        $this->lineCollection->tokenizeStatement();
    }

    public function testTokenizeStatement()
    {
        $lc = new MoverLineCollection(array(
            $this->line1
        ));
        $this->line1->expects($this->once())
            ->method('tokenizeStatement')
            ->will($this->returnValue('asd'));

        $res = $lc->tokenizeStatement();
        $this->assertEquals('asd', $res);
    }

    public function testTokenizeStatementEmpty()
    {
        $lc = new MoverLineCollection(array(
        ));
        $res = $lc->tokenizeStatement();
    }

    public function provideTokenizeBetween()
    {
        return array(
            array(
                array(
                    'public function foobar()',
                    '{',
                    '  echo "Foobar";',
                    '}',
                    'you cant see me'
                ),
                '{', '}', 7
            ),
            array(
                array(
                    '$dir = (realpath(__DIR__."/Foobar/barFoo").basename("somefile.php")); // rubbish',
                    'you cant see me'
                ),
                '(', ')', 13
            )
        );

    }

    /**
     * @dataProvider provideTokenizeBetween
     */
    public function testTokenizeBetween($lines, $left, $right, $expectedNb)
    {
        $lc = new MoverLineCollection();
        $lc->addLines($lines);

        $res = $lc->tokenizeBetween($left, $right);
        $this->assertCount($expectedNb, $res);
        $this->assertEquals($right, $res->last()->getValue());
    }

    public function testMatch()
    {
        $this->line1->expects($this->once())
            ->method('match')
            ->with('foobar')
            ->will($this->returnValue(false));
        $this->line2->expects($this->once())
            ->method('match')
            ->with('foobar')
            ->will($this->returnValue(true));

        $res = $this->lineCollection->match('foobar');

        $this->assertTrue($res);
    }

    public function testFalse()
    {
        $this->line1->expects($this->once())
            ->method('match')
            ->with('foobar')
            ->will($this->returnValue(false));
        $this->line2->expects($this->once())
            ->method('match')
            ->with('foobar')
            ->will($this->returnValue(false));

        $res = $this->lineCollection->match('foobar');

        $this->assertFalse($res);
    }

    public function testNeighborLineNull()
    {
        $lc = new MoverLineCollection();
        $res = $lc->nextLine();
        $this->assertNull($res);

        $res = $lc->prevLine();
        $this->assertNull($res);
    }
}













