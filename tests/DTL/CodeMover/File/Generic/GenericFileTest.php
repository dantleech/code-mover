<?php

namespace DTL\CodeMover\File\Generic;

use DTL\CodeMover\File\Generic\GenericFile;
use DTL\CodeMover\File\Php\PhpFile;

class GenericFileTest extends \PHPUnit_Framework_TestCase
{
    protected $file;

    public function setUp()
    {
        $testFile = realpath(__DIR__.'/../../../..').'/stubb/testfile.txt';
        $testPhpClassFile = realpath(__DIR__.'/../../../..').'/stubb/testphpclass.php';
        $this->file = new GenericFile(new \SplFileInfo($testFile));
        $this->phpFile = new PhpFile(new \SplFileInfo($testPhpClassFile));
        $this->newFile = realpath(__DIR__.'/../../../..').'/stubb/tmp/newfile.txt';
        $this->removeNewFile();
    }

    public function tearDown()
    {
        $this->removeNewFile();
    }

    protected function removeNewFile()
    {
        if (file_exists($this->newFile)) {
            unlink($this->newFile);
            rmdir(dirname($this->newFile));
        }
    }

    public function testNameMatches()
    {
        $this->assertTrue($this->file->nameMatches('/testfile/'));
        $this->assertTrue($this->file->nameMatches('testfile'));
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
        $firstLine = $this->file->findLine($pattern)->getSingle();

        $coll = $this->file->findLines($pattern);
        $this->assertEquals($nbLines, $coll->count());
        $this->assertSame($firstLine, $coll[0]);
    }

    /**
     * @depends testFindLine
     * @todo Move to MoverLineTest
     */
    public function testNextLine()
    {
        $next = $this->file->findLine('Thing')->nextLine();
        $this->assertNotNull($next);
        $this->assertContains('setOption', $next->getLine());
    }

    /**
     * @depends testFindLine
     * @todo Move to MoverLineTest
     */
    public function testPrevLine()
    {
        $prev = $this->file->findLine('setOption')->prevLine();
        $this->assertNotNull($prev);
        $this->assertContains('Thing', $prev->getLine());
    }

    public function testPhpMethod()
    {
        $method = $this->phpFile->createMethod('public', 'setDefaultOptions', 'OptionsResolverInterface $resolver');
        $this->phpFile->saveMethod($method);
        $method = $this->phpFile->findLine('public function setDefaultOptions');
        $this->assertNotNull($method);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage already exists
     */
    public function testMethodAlreadyExists()
    {
        $method = $this->phpFile->createMethod('public', 'configure', null);
        $this->phpFile->saveMethod($method);
    }

    public function testPath()
    {
        $path = $this->file->getPath();
        $this->assertEquals(realpath(__DIR__.'/../../../../stubb').'/testfile.txt', $path);

        $this->file->setPath('foobar');
        $this->assertEquals('foobar', $this->file->getPath());
    }

    public function testWrite()
    {
        $this->assertFalse(file_exists($this->newFile));
        $this->file->setPath($this->newFile);
        $this->file->write();
        $this->assertTrue(file_exists($this->newFile));
    }

    public function testSetContent()
    {
        $this->assertFalse($this->file->isModified());

        $content = <<<HERE
This
is
some
content
HERE;
        $this->file->setContent($content);
        $this->assertEquals(array(
            'This',
            'is',
            'some',
            'content',
        ), $this->file->toArray());

        $this->assertTrue($this->file->isModified());
    }
}
