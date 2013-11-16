<?php

namespace DTL\CodeMover\Console\Command\TestMigration;

use DTL\CodeMover\AbstractMigrator;
use DTL\CodeMover\MoverFile;
use DTL\CodeMover\MigratorContext;

class TestMigrator extends AbstractMigrator
{
    public function getName()
    {
        return 'test';
    }

    public function getDependencies()
    {
        return array();
    }

    public function accepts(MoverFile $file)
    {
        return true;
    }

    public function migrate(MigratorContext $context)
    {
    }
}
