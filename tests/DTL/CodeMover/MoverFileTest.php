<?php

namespace DTL\CodeMover;

class MoverFileTest extends \PHPUnit_Framework_TestCase
{
    protected $file;

    public function setUp()
    {
        $testFile = realpath(__DIR__.'/../../stubb/testfile.txt');
        $this->file = new MoverFile($testFile);
    }

    public function testNameMatches()
    {
        $this->assertTrue($this->file->nameMatches('/testfile/'));
        $this->assertFalse($this->file->nameMatches('/bahfile/'));
    }

    public function provideFindLine()
    {
        return array(
            array('/namespace/', true, 3),
            array('/echo \".*\"/', true, 5),
        );
    }

    /**
     * @dataProvider provideFindLine
     */
    public function testFindLine($pattern, $isMatch, $expectedLineNo)
    {
        $line = $this->file->findLine($pattern);

        if ($isMatch) {
            $this->assertNotNull($line);
        } else {
            $this->assertNull($line);
        }

        $this->assertEquals($expectedLineNo, $line->getLineNo());
    }

    public function provideFindLines()
    {
        return array(
            array('/setOption/', 2),
            array('/thislinedoesnotexist/', 0),
        );
    }

    /**
     * @dataProvider provideFindLines
     * @depends testFindLine
     */
    public function testFindLines($pattern, $nbLines)
    {
        // find first
        $firstLine = $this->file->findLine($pattern);

        $coll = $this->file->findLines($pattern);
        $this->assertEquals($nbLines, $coll->count());
        $this->assertSame($firstLine, $coll[0]);
    }
}
