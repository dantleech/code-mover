<?php

require __DIR__.'/../vendor/autoload.php';

$application = new Symfony\Component\Console\Application('Code Mover - Automated Code Migration Tool', 'v0.1.0');
$application->addCommands(array(
    new DTL\CodeMover\Console\Command\MigrateCommand()
));

$application->run();
