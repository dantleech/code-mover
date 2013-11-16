<?php

namespace DTL\CodeMover\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class MigrateCommandTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->testTargetDir = __DIR__.'/TestMigrationTarget';
        $this->testSkeletonDir = __DIR__.'/../../../../stubb/TestCode';
        $this->command = new MigrateCommand();
        $this->fs = new Filesystem;
        $this->deleteTestFiles();
        $this->initTestFiles();
        $this->ct = new CommandTester($this->command);
    }

    public function deleteTestFiles()
    {
        $this->fs->remove($this->testTargetDir);
    }

    public function initTestFiles()
    {
        $this->fs->mirror($this->testSkeletonDir, $this->testTargetDir);
    }

    public function provideMigrate()
    {
        return array(
            array(array(), array(
                'exception' => array('RuntimeException', 'Not enough arguments')
            )),
            array(
                array(
                    'migrations_path' => __DIR__.'/TestMigration',
                    '--path' => array(__DIR__.'/TestMigrationTarget'),
                ), array(), array(
                    'Migrator "test" accepts',
                )
            ),
        );

    }

    /**
     * @dataProvider provideMigrate
     */
    public function testMigrate($input, $options, $expectedOutputs = null)
    {
        $options = array_merge(array(
            'exception' => null,
        ), $options);

        if ($options['exception']) {
            list($type, $message) = $options['exception'];
            $this->setExpectedException($type, $message);
        }

        $this->ct->execute($input, array());

        if ($expectedOutputs) {
            $res = $this->ct->getDisplay();
            foreach ($expectedOutputs as $expectedOutput) {
                $this->assertContains($expectedOutput, $res);
            }
        }
    }
}
