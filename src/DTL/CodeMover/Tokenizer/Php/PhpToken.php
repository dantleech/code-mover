<?php

namespace DTL\CodeMover\Tokenizer\Php;

use DTL\CodeMover\Line;

class PhpToken
{
    protected $line;
    protected $tokenType;
    protected $tokenValue;

    public function __construct(Line $line = null, $tokenType, $tokenValue)
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
