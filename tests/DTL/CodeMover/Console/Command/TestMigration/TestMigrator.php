<?php

namespace DTL\CodeMover\Console\Command\TestMigration;

use DTL\CodeMover\AbstractMigrator;
use DTL\CodeMover\MigratorContext;
use DTL\CodeMover\AbstractFile;

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

    public function accepts(AbstractFile $file)
    {
        return true;
    }

    public function migrate(MigratorContext $context)
    {
    }
}
