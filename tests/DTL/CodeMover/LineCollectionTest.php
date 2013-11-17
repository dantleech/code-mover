<?php

namespace DTL\CodeMover;

class LineCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->line1 = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();
        $this->line2 = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();
        $this->line3 = $this->getMockBuilder('DTL\CodeMover\Line')
            ->disableOriginalConstructor()->getMock();

        $this->lineCollection = new LineCollection(array(
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
        $lc = new LineCollection(array(
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
        $lc = new LineCollection;
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
        $lc = new LineCollection(array(
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
        $lc = new LineCollection(array(
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
            ),
            array(
                array(
                    'function () {',
                    '// ..',
                ), '{', '}', 'exception',
            ),
            array(
                array(
                    'function () {',
                    '// ..',
                    '}',
                ), '%', '%', 0,
            ),
            array(
                array(
                    '% asd asd %',
                    '// ..',
                    '}',
                ), '%', '%', 1,
            ),
            array(
                array(), 'a', 'b', 0
            ),
        );

    }

    /**
     * @dataProvider provideTokenizeBetween
     */
    public function testTokenizeBetween($lines, $left, $right, $expectedNb)
    {
        $lc = new LineCollection();
        $lc->addLines($lines);

        if ($expectedNb === 'exception') {
            $this->setExpectedException('RuntimeException', 'Could not find end string');
        }

        $res = $lc->tokenizeBetween($left, $right);

        $this->assertNotNull($res);
        $this->assertCount($expectedNb, $res);

        if ($expectedNb > 0) {
            $this->assertEquals($right, $res->last()->getValue());
        }
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
        $lc = new LineCollection();
        $res = $lc->nextLine();
        $this->assertNull($res);

        $res = $lc->prevLine();
        $this->assertNull($res);
    }

    public function testAddLineAfter()
    {
        $this->lineCollection->addLineAfter($this->line1, $this->line3);
        $res = $this->lineCollection->toArray();
        $this->assertSame(array(
            $this->line1, $this->line3, $this->line2
        ), $res);
    }

    public function testAddLinesAfter()
    {
        $this->lineCollection->addLinesAfter($this->line1, array(
            'this is line 1',
            'this is line 2',
        ));
        $res = $this->lineCollection->toArray();

        $this->assertCount(4, $this->lineCollection);
        $this->assertEquals('this is line 1', $this->lineCollection->offsetGet(1));
        $this->assertEquals('this is line 2', $this->lineCollection->offsetGet(2));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetSingleMoreThanOne()
    {
        $this->lineCollection->getSingle();
    }

    public function testGetSingle()
    {
        $lc = new LineCollection();
        $lc->add($this->line1);
        $res = $lc->getSingle();
        $this->assertSame($this->line1, $res);
    }
}











