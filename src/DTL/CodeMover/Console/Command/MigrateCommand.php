<?php

namespace DTL\CodeMover\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;
use DTL\CodeMover\MigrationRunner;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\CS\Fixer;
use DTL\CodeMover\Migrator\PhpCsFixerMigrator;
use DTL\CodeMover\Console\ConsoleLogger;

class MigrateCommand extends Command
{
    protected $output;
    protected $showDiff = false;

    public function configure()
    {
        $this->setName('migrate');
        $this->setDescription('Execute code migrations');

        $this->addArgument('migrations_path', InputArgument::REQUIRED, 'Path containing MigrationUnit classes');

        $this->addOption('path', null, 
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 
            'Path to migrate (can specify many', array('.')
        );
        $this->addOption('name', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'File basename to match', array('*'));
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump each file (debug)');
        $this->addOption('fix-cs', null, InputOption::VALUE_NONE, 'Applies the fabpot CSS fixer');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run');
        $this->addOption('migrator', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Migrator', array());
        $this->addOption('ignore-missing-deps', null, InputOption::VALUE_NONE, 'Ignore missing dependencies');
        $this->addOption('todos', null, InputOption::VALUE_NONE, 'List all todos');
        $this->addOption('diff', null, InputOption::VALUE_NONE, 'Show diff');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('green');
        $output->getFormatter()->setStyle('diffplus', $style);
        $style = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('diffminus', $style);

        $this->output = $output;
        $migrationsPath = $input->getArgument('migrations_path');
        $paths = $input->getOption('path');
        $names = $input->getOption('name');
        $dump = $input->getOption('dump');
        $fixCs = $input->getOption('fix-cs');
        $dryRun = $input->getOption('dry-run');
        $migratorNames = $input->getOption('migrator');
        $ignoreMissingDeps = $input->getOption('ignore-missing-deps');
        $showTodos = $input->getOption('todos');
        $this->showDiff = $input->getOption('diff');

        $mRunnerOptions = array(
            'ignore_missing_dependencies' => $input->getOption('ignore-missing-deps'),
            'show_diff' => $input->getOption('diff'),
            'dry_run' => $input->getOption('dry-run'),
        );

        $mRunner = $this->initMigrationRunner($migrationsPath, $migratorNames, $mRunnerOptions);

        if ($fixCs) {
            $csFixerMigrator = new PhpCsFixerMigrator;
            $mRunner->addMigrator($csFixerMigrator);
        }

        $finder = new Finder;
        $finder->files();

        if (!$paths) {
            $paths = array(__DIR__);
        }

        foreach ($names as $name) {
            $finder->name($name);
        }

        foreach ($paths as $path) {
            $finder->in($path);
        }

        $mRunner->migrate($finder);

        // validate PHP files
        $failedValidations = array();
        foreach ($finder as $file) {
            $validatePhp = true;
            if ($file->getExtension() == 'php' && $validatePhp) {
                $phpValidText = array();
                $out = exec('php -l '.$file->getRealPath(), $phpValidText, $exitCode);

                if ($exitCode == 255) {
                    $failedValidations[] = '<error>PHPLint returned ('.$exitCode.'): '.implode("\n", $phpValidText).'</error>';
                }
            }
        }

        // Show ToDOs
        $mContexts = $mRunner->getMigratorContexts();
        if (true === $showTodos) {
            foreach ($mContexts as $mContext) {
                if ($todos = $mContext->getTodos()) {
                    $output->writeln('');
                    $output->writeln('todos: '.$mContext->getFile()->getPath());
                    foreach ($todos as $todo) {
                        $output->writeln('  - '.$todo);
                    }
                }
            }
        } else {
            $todoCount = 0;
            foreach ($mContexts as $mContext) {
                $todoCount += count($mContext->getTodos());
            }

            $output->writeln('<info>There are </info>'.$todoCount.' todos, use --todos to list them');
        }

        foreach ($failedValidations as $failedValidation) {
            $output->writeln($failedValidations);
        }
    }

    protected function initMigrationRunner($migrationsPath, $migratorNames, $options = array())
    {
        $finder = new Finder;
        $finder->name('*Migrator.php');
        $finder->in($migrationsPath);

        $output = $this->output;
        $start = microtime(true);


        $logger = new ConsoleLogger($output);
        $mRunner = new MigrationRunner($logger, $options);

        $migratorFiles = array();
        foreach ($finder as $file) {
            $this->output->writeln('<comment>Found migrator: </comment>'.$file);
            $migratorFiles[] = $file;
            include($file);
        }

        foreach ($migratorFiles as $migratorFile) {
            $migratorClass = $this->getFqnForFile($migratorFile);
            $refl = new \ReflectionClass($migratorClass);
            if ($refl->isInstantiable()) {
                $migrator = new $migratorClass;

                if (!$migratorNames || $migratorNames && in_array($migrator->getName(), $migratorNames)) {
                    $mRunner->addMigrator($migrator);
                    $this->output->writeln('<info>Adding migrator: </info>'.$migratorClass);
                }
            }
        }

        return $mRunner;
    }

    public function getFqnForFile(\SplFileInfo $file)
    {
        $handle = fopen($file->getRealPath(), 'r');

        $namespace = null;
        $className = null;

        while ($line = fgets($handle)) {
            if (!$namespace) {
                preg_match('&namespace  (.*);&', $line, $matches);

                if (isset($matches[1])) {
                    $namespace = $matches[1];
                }
            }

            if (!$className) {
                preg_match('&class ([A-Za-z0-9]+)&', $line, $matches);

                if (isset($matches[1])) {
                    $className = $matches[1];
                    break;
                }
            }
        }

        if (null === $className) {
            throw new \InvalidArgumentException(sprintf('No class found in file %s', $file->getRealPath()));
        }

        if ($namespace) {
            $fqn = sprintf('%s\\%s', $namespace, $className);
        } else {
            $fqn = $className;
        }

        return $fqn;
    }
}
