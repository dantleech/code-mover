<?php

namespace DTL\CodeMover;

use DTL\CodeMover\PhpTokenList;
use DTL\CodeMover\PhpToken;

interface LineInterface
{
    public function match($patterns);

    public function getLineNo();

    public function replace($patterns, $replacements);

    public function delete();

    public function tokenize();

    public function tokenizeStatement();

    public function tokenizeBetween($leftString, $rightString);
}

