<?php

namespace DTL\CodeMover;

use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Diff\Differ;

class MigrationRunner
{
    use LoggableTrait;

    protected $migrators = array();
    protected $order = array();
    protected $orderedMigrators = array();

    public function __construct(\Closure $logger = null)
    {
        $this->logger = $logger;
    }

    public function addMigrator(MigratorInterface $migrator)
    {
        if (isset($this->migrators[$migrator->getName()])) {
            throw new \Exception(sprintf('Migrator with name "%s" already exists.'));
        }

        $this->migrators[$migrator->getName()] = $migrator;
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

            if (!in_array($this->migrators[$depName], $this->orderedMigrators, true)) {
                $seen[] = $migrator->getName();
                $this->resolveOrder($this->migrators[$depName], $seen);
            }
        }

        $this->orderedMigrators[] = $migrator;
    }

    public function getOrderedMigrators()
    {
        foreach ($this->migrators as $migrator) {
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
        foreach ($this->getOrderedMigrators() as $migrator) {
            if ($migrator->accepts($moverFile)) {
                $this->log(sprintf('Migrator "%s" accepts file "%s"', $migrator->getName(), $file), 'debug');
                $migrator->migrate($moverFile);

                $diff = new Differ;
                $originalString = implode("", $moverFile->getOriginalFile());
                $newString = implode("", $moverFile->getLines()->toArray());
                $diff = $diff->diffToArray($originalString, $newString);

                foreach ($diff as $el) {
                    list($line, $stat)  = $el;
                    if ($stat != 0) {
                        $this->log(sprintf('%s%s',
                            $stat == 1 ? '+' : '-',
                            $line
                        ));
                    }
                }

                if ($moverFile->isModified()) {
                    $modified = true;
                    $moverFile->commit();
                }
            }
        }

        if ($modified) {
            return $moverFile;
        }

        return null;
    }
}
