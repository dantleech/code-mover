<?php

namespace DTL\CodeMover\Tokenizer;

use DTL\CodeMover\Line;
use DTL\CodeMover\LineInterface;

interface TokenInterface
{
    public function setLine(LineInterface $line);

    public function getType();

    public function setType($type);

    public function getValue();

    public function setValue($value);

    public function getLine();
}
