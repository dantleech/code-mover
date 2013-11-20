<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MigratorContext;
use DTL\CodeMover\AbstractFile;

interface MigratorInterface
{
    public function getName();

    public function getDependencies();

    public function accepts(AbstractFile $file);

    public function migrate(MigratorContext $context);
}
