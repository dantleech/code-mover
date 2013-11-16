<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;

require __DIR__.'/../vendor/autoload.php';

$application = new Symfony\Component\Console\Application('Code Mover - Automated Code Migration Tool', 'v0.1.0');
$application->addCommands(array(
    new DTL\CodeMover\Console\Command\MigrateCommand()
));

ErrorHandler::register(E_WARNING, false);
Debug::enable();

$application->run();
