<?php

namespace DTL\CodeMover;

class MoverFileTest extends \PHPUnit_Framework_TestCase
{
    protected $file;

    public function setUp()
    {
        $testFile = realpath(__DIR__.'/../../stubb/testfile.txt');
        $testPhpClassFile = realpath(__DIR__.'/../../stubb/testphpclass.php');
        $this->file = new MoverFile($testFile);
        $this->phpFile = new MoverFile($testPhpClassFile);
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
            array('setOption', true, 8),
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

        $this->assertCount(1, $line);
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
        $firstLine = $this->file->findLine($pattern)->unwrap();

        $coll = $this->file->findLines($pattern);
        $this->assertEquals($nbLines, $coll->count());
        $this->assertSame($firstLine, $coll[0]);
    }

    /**
     * @depends testFindLine
     */
    public function testNextLine()
    {
        $next = $this->file->findLine('Thing')->nextLine();
        $this->assertNotNull($next);
        $this->assertContains('setOption', $next->getLine());
    }

    /**
     * @depends testFindLine
     */
    public function testPrevLine()
    {
        $prev = $this->file->findLine('setOption')->prevLine();
        $this->assertNotNull($prev);
        $this->assertContains('Thing', $prev->getLine());
    }

    public function testMethod()
    {
        $method = $this->phpFile->createMethod('public', 'setDefaultOptions', 'OptionsResolverInterface $resolver');
        $this->phpFile->saveMethod($method);
        $method = $this->phpFile->findLine('public function setDefaultOptions');
        $this->assertNotNull($method);
    }
}
