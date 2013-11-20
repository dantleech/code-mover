<?php

namespace DTL\CodeMover;

use SebastianBergmann\Diff\Differ;
use DTL\CodeMover\RunnerContext;
use Psr\Log\LoggerInterface;
use DTL\CodeMover\File\Factory as FileFactory;

class MigrationRunner
{
    protected $migrators = array();
    protected $order = array();
    protected $orderedMigrators = array();
    protected $ignoreMissingDependencies = false;
    protected $context;
    protected $migratorContexts = array();
    protected $options = array();

    public function __construct(LoggerInterface $logger = null, $options = array())
    {
        $options = array_merge(array(
            'ignore_missing_dependencies' => false,
            'show_diff' => false,
            'dry_run' => false,
        ), $options);

        $this->logger = $logger;
        $this->options = $options;
        $this->context = new RunnerContext;
        $this->fileFactory = new FileFactory;
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
        $this->logger->info('<info>Resolved migrator order</info>: '.implode(', ', $keys));

        return $this->orderedMigrators;
    }

    public function migrate($files)
    {
        $modified = false;
        $fileRepository = array();

        foreach ($this->getOrderedMigrators() as $migrator) {
            $this->logger->info(sprintf('<info>Running %s files thorugh migrator</info>: %s', count($files), $migrator->getName()));

            foreach ($files as $file) {
                $this->logger->debug(sprintf('<comment>Processing file</comment>: %s', $file->getRealPath()));

                if (isset($fileRepository[$file->getRealPath()])) {
                    $moverFile = $fileRepository[$file->getRealPath()];
                } else {
                    $moverFile = $this->fileFactory->getFile($file);
                }

                $this->logger->debug('Created mover file');
                $migratorContext = new MigratorContext($this->context, $moverFile);
                $this->migratorContexts[] = $migratorContext;

                if (false === $migrator->accepts($moverFile)) {
                    $this->logger->debug(sprintf('<comment>Migrator "%s" rejects</comment>', $migrator->getName()));
                } else {
                    $this->logger->debug(sprintf('<info>Migrator "%s" accepts, processing</info>', $migrator->getName()));

                    $migrator->migrate($migratorContext);

                    $this->logger->debug('  Done processing');

                    if ($this->options['show_diff']) {
                        $this->logger->debug('  Starting diff');
                        $diff = new Differ;
                        $originalString = $moverFile->getOriginalFile()->getRaw();
                        $newString = $moverFile->getRaw();
                        $diff = $diff->diffToArray($originalString, $newString);

                        foreach ($diff as $el) {
                            list($line, $stat)  = $el;
                            if ($stat != 0) {
                                $this->logger->info(sprintf('%s%s',
                                    $stat == 1 ? '+' : '-',
                                    $line
                                ), array('style' => $stat == 1 ? 'diffplus' : 'diffminus'));
                            }
                        }
                        $this->logger->debug(' -- Done');
                    }

                    if ($moverFile->isModified()) {
                        $this->logger->debug('  File modified, committing');
                        $modified = true;
                        $moverFile->commit();
                        $fileRepository[$moverFile->getSplFileInfo()->getRealPath()] = $moverFile;
                        if (false === $this->options['dry_run']) {
                            $this->logger->debug('  Writing file');
                            $moverFile->write();
                            $this->logger->debug('  File written');
                        }
                    }

                    $this->logger->debug('Finished processing file');
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
