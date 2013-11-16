<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MigrationRunner;
use DTL\CodeMover\MigratorContext;

class MigrationRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->log = new \ArrayObject();
        $log = $this->log;

        $logger = function ($message, $type) use ($log) {
            $log[] = $message;
        };

        $this->runner = new MigrationRunner($logger, array('show_diff' => true));
        $this->testFile = realpath(__DIR__.'/../..').'/stubb/testfile.tmp.txt';
        $this->originalTestFile = realpath(__DIR__.'/../..').'/stubb/testfile.txt';
        $this->removeTestFile();
        copy($this->originalTestFile, $this->testFile);
    }

    public function tearDown()
    {
        $this->removeTestFile();
    }

    public function removeTestFile()
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function provideDependencyResolution()
    {
        return array(
            array(array(
                'mig1' => array('mig2'),
                'mig2' => array(),
                'mig3' => array('mig2'),
            ), array(
                'expectedOrder' => array('mig2', 'mig1', 'mig3')
            )),
            array(array(
                'mig1' => array('mig2', 'mig3'),
                'mig2' => array(),
                'mig3' => array('mig2'),
            ), array(
                'expectedOrder' => array('mig2', 'mig3', 'mig1')
            )),
            array(array(
                'mig1' => array('mig2', 'mig3', 'mig4'),
                'mig2' => array(),
                'mig3' => array('mig2'),
                'mig4' => array('mig1'),
            ), array(
                'expectedException' => array('\RuntimeException', 'Circular'),
            )),
            array(array(
                'mig1' => array('mig1')
            ), array(
                'expectedException' => array('\RuntimeException', 'Migrator cannot have itself'),
            )),
        );
    }

    /**
     * @dataProvider provideDependencyResolution
     */
    public function testDependencyResolution($migrators, $options)
    {
        $options = array_merge(array(
            'expectedOrder' => array(),
            'expectedException' => null,
        ), $options);

        foreach ($migrators as $name => $deps) {
            $m = $this->getMock('DTL\CodeMover\MigratorInterface');
            $m->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($name));
            $m->expects($this->any())
                ->method('getDependencies')
                ->will($this->returnValue($deps));
            $this->runner->addMigrator($m);
        }

        if ($options['expectedException']) {
            list($eType, $eMessage) = $options['expectedException'];
            $this->setExpectedException($eType, $eMessage);
        }

        $orderedMigrators = $this->runner->getOrderedMigrators();
        $expectedNames = array();

        foreach ($orderedMigrators as $orderedMigrator) {
            $expectedNames[] = $orderedMigrator->getName();
        }

        $this->assertEquals($options['expectedOrder'], $expectedNames);
    }

    public function testCommit()
    {
        $migratorData = array(
            array('test_1', 'Thing', 'Bar'),
            array('test_2', 'Option', 'Foo'),
            array('test_3', 'Foo', 'Bar'),
        );

        foreach ($migratorData as $data) {
            list($mName, $mPattern, $mReplace) = $data;

            $migrator = $this->getMock('DTL\CodeMover\MigratorInterface');
            $migrator->expects($this->once())
                ->method('migrate')
                ->will($this->returnCallback(function (MigratorContext $context) use ($mPattern, $mReplace) {
                    $file = $context->getFile();
                    $lines = $file->findLines($mPattern);
                    foreach ($lines as $line) {
                        $line->replace('/'.$mPattern.'/', $mReplace);
                    }
                }));
            $migrator->expects($this->once())
                ->method('getDependencies')
                ->will($this->returnValue(array()));
            $migrator->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($mName));
            $migrator->expects($this->any())
                ->method('accepts')
                ->will($this->returnValue(true));

            $this->runner->addMigrator($migrator);
        }

        $this->runner->migrate(array(
            new \SplFileInfo($this->testFile)
        ));
        $this->assertContains('-$foo = new Thing;', (array) $this->log);
        $this->assertContains('+$foo = new Bar;', $this->log);
        $this->assertContains('-$foo->setOption(\'asd\', \'dsa\');', $this->log);
        $this->assertContains('+$foo->setFoo(\'asd\', \'dsa\');', $this->log);
        $this->assertContains('-$foo->setFoo(\'asd\', \'dsa\');', $this->log);
        $this->assertContains('+$foo->setBar(\'asd\', \'dsa\');', $this->log);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage already exists
     */
    public function testAddDuplicateMigrator()
    {
        $migratorNames = array('test_1', 'test_2', 'test_1');

        foreach ($migratorNames as $migratorName) {
            $migrator = $this->getMock('DTL\CodeMover\MigratorInterface');
            $migrator->expects($this->once())
                ->method('getName')
                ->will($this->returnValue($migratorName));
            $this->runner->addMigrator($migrator);
        }
    }

    public function testNullMigration()
    {
        $this->runner->migrate(new \SplFileInfo($this->testFile));
    }
}
