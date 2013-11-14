<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverFile;

class MigratorContext
{
    protected $parameters = array();
    protected $todos = array();

    public function __construct(RunnerContext $runnerContext, MoverFile $file)
    {
        $this->runnerContext = $runnerContext;
        $this->file = $file;
    }

    public function getFile() 
    {
        return $this->file;
    }

    public function getRunnerContext() 
    {
        return $this->runnerContext;
    }
    

    public function getParameter($name) 
    {
        if (!isset($this->parameters[$name])) {
            throw new \Exception(sprintf(
                'Could not find parameters with name "%s"', $name
            ));
        }

        return $this->parameters[$name];
    }
    
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function addTodo($message)
    {
        $this->todos[] = $message;
    }

    public function getTodos()
    {
        return $this->todos;
    }
}
