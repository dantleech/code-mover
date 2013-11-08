<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverFile;

interface MigratorInterface
{
    public function getName();

    public function getDependencies();

    public function accepts(MoverFile $file);

    public function migrate(MoverFile $file);
}
