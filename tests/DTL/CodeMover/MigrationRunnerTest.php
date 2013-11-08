<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MigrationRunner;

class MigrationRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->runner = new MigrationRunner;
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
}
