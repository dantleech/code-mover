<?php

namespace DTL\CodeMover;

class MoverContext
{
    protected $todos = array();

    public function addTodo($message)
    {
        $this->todos[] = $message;
    }

    public function getTodos()
    {
        return $this->todos;
    }
}
