<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MigratorInterface;

class RunnerContext
{
    protected $migrators = array();
    protected $todos = array();
    protected $parameters = array();

    public function addMigrator(MigratorInterface $migrator)
    {
        $name = $migrator->getName();

        if (isset($this->migrators[$name])) {
            throw new \RuntimeException(sprintf('Migrator with name "%s" already exists.', $name));
        }

        $this->migrators[$name] = $migrator;
    }

    public function getParameter($name) 
    {
        if (!isset($this->parameters[$name])) {
            throw new \Exception(sprintf(
                'Could not find parameters with name "%s". I have: "%s"', 
                $name, implode(', ', array_keys($this->parameters))
            ));
        }

        return $this->parameters[$name];
    }
    
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function getParameters()
    {
        return $this->parameters;
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
