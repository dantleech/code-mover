<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverFile;
use DTL\CodeMover\AbstractFile;

class MigratorContext
{
    protected $todos = array();
    protected $runnerContext;

    public function __construct(RunnerContext $runnerContext, AbstractFile $file)
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

    public function getParameters() 
    {
        return $this->runnerContext->getParameters();
    }

    public function getParameter($name) 
    {
        return $this->runnerContext->getParameter($name);
    }
    
    public function setParameter($name, $value)
    {
        return $this->runnerContext->setParameter($name, $value);
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
