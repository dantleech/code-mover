<?php

namespace DTL\CodeMover\Tokenizer\Php;

use DTL\CodeMover\Line;
use DTL\CodeMover\LineInterface;

class PhpToken
{
    protected $line;
    protected $tokenType;
    protected $tokenValue;

    public function __construct($tokenType, $tokenValue)
    {
        $this->tokenType = $tokenType;
        $this->tokenValue = $tokenValue;
    }

    public function setLine(LineInterface $line)
    {
        $this->line = $line;
    }

    public function getType()
    {
        return $this->tokenType;
    }

    public function getValue()
    {
        return $this->tokenValue;
    }

    public function getDecapsedValue()
    {
        if ($this->tokenType == T_ENCAPSED_STRING) {
            return substr($this->tokenValue, 1, -1);
        }

        return $this->tokenValue;
    }

    public function setValue($value)
    {
        $this->tokenValue = $value;
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
