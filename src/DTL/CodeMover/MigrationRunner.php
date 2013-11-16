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
    protected $options = array();

    public function __construct(\Closure $logger = null, $options = array())
    {
        $options = array_merge(array(
            'ignore_missing_dependencies' => false,
            'show_diff' => false,
            'dry_run' => false,
        ), $options);

        $this->logger = $logger;
        $this->options = $options;
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

    public function migrate($files)
    {
        $modified = false;

        foreach ($this->getOrderedMigrators() as $migrator) {
            $this->log(sprintf('Running %s files thorugh migrator "%s"', count($files), $migrator->getName()), 'info');

            foreach ($files as $file) {
                $this->log(sprintf('Processing file %s', $file->getRealPath()), 'info');
                $moverFile = new MoverFile($file);
                $this->log(sprintf('Created mover file'));
                $migratorContext = new MigratorContext($this->context, $moverFile);
                $this->migratorContexts[] = $migratorContext;

                if (false === $migrator->accepts($moverFile)) {
                    $this->log(sprintf('Migrator "%s" rejects', $migrator->getName()), 'debug');
                } else {
                    $this->log(sprintf('Migrator "%s" accepts', $migrator->getName()), 'debug');
                    $migrator->migrate($migratorContext);
                    $this->log(' -- Done', 'debug');

                    if ($this->options['show_diff']) {
                        $this->log('Starting diff');
                        $diff = new Differ;
                        $originalString = $moverFile->getOriginalFile()->getRaw();
                        $newString = $moverFile->getRaw();
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
                        $this->log(' -- Done');
                    }

                    if ($moverFile->isModified()) {
                        $this->log('File modified, committing');
                        $modified = true;
                        $moverFile->commit();
                        if (false === $this->options['dry_run']) {
                            $this->log('Writing file');
                            $moverFile->write();
                            $this->log('File written');
                        }
                    }

                    $this->log('Finished processing file');
                }
            }
        }

        return $this->context;
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
