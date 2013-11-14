<?php

namespace DTL\CodeMover;

use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Diff\Differ;
use DTL\CodeMover\RunnerContext;

class MigrationRunner
{
    use LoggableTrait;

    protected $migrators = array();
    protected $order = array();
    protected $orderedMigrators = array();
    protected $ignoreMissingDependencies = false;
    protected $context;
    protected $migratorContexts = array();

    public function __construct(\Closure $logger = null, $options = array())
    {
        $options = array_merge(array(
            'ignore_missing_dependencies' => false,
        ), $options);

        $this->logger = $logger;
        $this->ignoreMissingDependencies = $options['ignore_missing_dependencies'];
        $this->context = new RunnerContext;
    }

    public function addMigrator(MigratorInterface $migrator)
    {
        $this->context->addMigrator($migrator);
    }

    public function resolveOrder($migrator, $seen = array())
    {
        foreach ($migrator->getDependencies() as $depName) {
            if ($depName == $migrator->getName()) {
                throw new \RuntimeException(sprintf(
                    'Migrator cannot have itself as a dependency "%s"',
                    get_class($migrator)
                ));
            }

            if (in_array($depName, $seen)) {
                throw new \RuntimeException(sprintf(
                    'Circular reference detected "%s" -> "%s"',
                    get_class($migrator), $depName
                ));
            }

            if ($this->context->hasMigrator($depName)) {
                $depMigrator = $this->context->getMigrator($depName);
            } else {
                if (false == $this->ignoreMissingDependencies) {
                    throw new \Exception(sprintf(
                        'Missing dependency "%s" for migration class "%s"',
                        $depName, get_class($migrator)
                    ));
                }

                continue;
            }

            if (!in_array($depMigrator, $this->orderedMigrators, true)) {
                $seen[] = $migrator->getName();
                $this->resolveOrder($depMigrator, $seen);
            }
        }

        $this->orderedMigrators[] = $migrator;
    }

    public function getOrderedMigrators()
    {
        if (array() != $this->orderedMigrators) {
            return $this->orderedMigrators;
        }

        foreach ($this->context->getMigrators() as $migrator) {
            if (!in_array($migrator, $this->orderedMigrators, true)) {
                $this->resolveOrder($migrator);
            }
        }

        $keys = array();
        foreach ($this->orderedMigrators as $mig) {
            $keys[] = $mig->getName();
        }
        $this->log('Resolved migrator order: '.implode(', ', $keys));

        return $this->orderedMigrators;
    }

    public function migrate($file)
    {
        $modified = false;

        $moverFile = new MoverFile($file);
        $migratorContext = new MigratorContext($this->context, $moverFile);
        $this->migratorContexts[] = $migratorContext;

        foreach ($this->getOrderedMigrators() as $migrator) {
            $runnerContext = new RunnerContext;
            if ($migrator->accepts($moverFile)) {
                $this->log(sprintf('Migrator "%s" accepts file "%s"', $migrator->getName(), $file), 'debug');

                $migrator->migrate($migratorContext);

                $diff = new Differ;
                $originalString = $moverFile->getOriginalFile()->getRaw();
                $newString = implode("\n", $moverFile->toArray());
                $diff = $diff->diffToArray($originalString, $newString);

                foreach ($diff as $el) {
                    list($line, $stat)  = $el;
                    if ($stat != 0) {
                        $this->log(sprintf('%s%s',
                            $stat == 1 ? '+' : '-',
                            $line
                        ), $stat == 1 ? 'diffplus' : 'diffminus');
                    }
                }

                if ($moverFile->isModified()) {
                    $modified = true;
                    $moverFile->commit();
                }
            }
        }

        return $moverFile;
    }

    public function getMigratorContexts() 
    {
        return $this->migratorContexts;
    }
    

    public function getContext()
    {
        return $this->context;
    }
}
