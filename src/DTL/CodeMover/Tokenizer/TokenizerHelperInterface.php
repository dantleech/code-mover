<?php

namespace DTL\CodeMover\Tokenizer;

use DTL\CodeMover\Tokenizer\Php\PhpTokenList;

interface TokenizerHelperInterface
{
    public function __construct(PhpTokenList $tokenList);
}
