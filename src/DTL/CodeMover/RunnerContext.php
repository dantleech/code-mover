<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MigratorInterface;

class RunnerContext
{
    protected $migrators = array();
    protected $todos = array();

    public function addMigrator(MigratorInterface $migrator)
    {
        $name = $migrator->getName();

        if (isset($this->migrators[$name])) {
            throw new \RuntimeException(sprintf('Migrator with name "%s" already exists.', $name));
        }

        $this->migrators[$name] = $migrator;
    }

    public function getMigrators()
    {
        return $this->migrators;
    }

    public function getMigrator($name)
    {
        if (!isset($this->migrators[$name])) {
            throw new \RuntimeException(sprintf('Migrator with name "%s" does NOT exist.', $name));
        }

        return $this->migrators[$name];
    }

    public function hasMigrator($name)
    {
        return isset($this->migrators[$name]);
    }

    public function addTodo($message)
    {
        $this->todos[] = $message;
    }
}
