<?php

namespace DTL\CodeMover;

class PhpToken
{
    protected $line;
    protected $tokenType;
    protected $tokenValue;

    public function __construct(MoverLine $line, $tokenType, $tokenValue)
    {
        $this->line = $line;
        $this->tokenType = $tokenType;
        $this->tokenValue = $tokenValue;
    }

    public function getType()
    {
        return $this->tokenType;
    }

    public function getValue()
    {
        return $this->tokenValue;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function isEndOfStatement()
    {
        return $this->tokenType == 'SINGLE_CHAR' && $this->tokenValue == ';';
    }
}
