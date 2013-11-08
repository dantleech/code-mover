<?php

namespace DTL\CodeMover\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;
use DTL\CodeMover\MigrationRunner;

class MigrateCommand extends Command
{
    protected $output;

    public function configure()
    {
        $this->setName('migrate');
        $this->setDescription('Execute code migrations');

        $this->addArgument('migrations_path', InputArgument::REQUIRED, 'Path containing MigrationUnit classes');

        $this->addOption('path', null, 
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 
            'Path to migrate (can specify many', array('.')
        );
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'File basename to match', '*');
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump each file (debug)');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $migrationsPath = $input->getArgument('migrations_path');
        $paths = $input->getOption('path');
        $name = $input->getOption('name');
        $dump = $input->getOption('dump');

        $mRunner = $this->initMigrationRunner($migrationsPath);

        $finder = new Finder;
        $finder->name($name);

        foreach ($paths as $path) {
            $finder->in($path);
        }

        foreach ($finder as $file) {
            $mFile = $mRunner->migrate($file);
            if ($mFile && $dump) {
                $output->writeln($mFile->dump());
            }
        }
    }

    protected function initMigrationRunner($migrationsPath)
    {
        $finder = new Finder;
        $finder->name('*Migrator.php');
        $finder->in($migrationsPath);

        $output = $this->output;
        $logger = function ($message, $type) use ($output) {
            switch ($type) {
                case 'info':
                    $mesage = sprintf('<info>%s</info>', $message);
                    break;
                case 'debug':
                    $message = sprintf('<comment>%s</comment>', $message);
                    break;
            }

            $output->writeln($message);
        };
        $mRunner = new MigrationRunner($logger);

        $migratorFiles = array();
        foreach ($finder as $file) {
            $migratorFiles[] = $file;
        }

        $classes = get_declared_classes();
        foreach ($migratorFiles as $migratorFile) {
            include $migratorFile;
        }
        $migratorClasses = array_diff(get_declared_classes(), $classes);

        foreach ($migratorClasses as $migratorClass) {
            $refl = new \ReflectionClass($migratorClass);
            if ($refl->isInstantiable()) {
                $migrator = new $migratorClass;
                $mRunner->addMigrator($migrator);
                $this->output->writeln('<info>Adding migrator: </info>'.$migratorClass);
            }
        }

        return $mRunner;
    }
}
