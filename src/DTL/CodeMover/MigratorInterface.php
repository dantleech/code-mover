<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverFile;
use DTL\CodeMover\MigratorContext;

interface MigratorInterface
{
    public function getName();

    public function getDependencies();

    public function accepts(MoverFile $file);

    public function migrate(MigratorContext $context);
}
