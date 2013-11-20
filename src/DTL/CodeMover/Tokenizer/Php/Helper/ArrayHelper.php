<?php

namespace DTL\CodeMover\Tokenizer\Php\Helper;

use DTL\CodeMover\Tokenizer\TokenizerHelperInterface;
use DTL\CodeMover\Tokenizer\Php\PhpTokenList;

class ArrayHelper implements TokenizerHelperInterface
{
    protected $tokenList;

    public function __construct(PhpTokenList $tokenList)
    {
        $this->tokenList = $tokenList;
    }

    /**
     * Append the given array to the next array
     * in the token stream.
     */
    public function append($array)
    {
        $dataString = array();

        foreach ($array as $key => $value) {
            $dataString[] = $key . ' => ' . $value;
        }

        $dataString = implode(', ', $dataString);

        $list = $this->tokenList
            ->seekValue('array')
            ->seekValue('(')->next()
            ->findBetween('(', ')')
            ->trim(1, 1)
        ;

        if ($list->last()->getValue() != ',') {
            $dataString = ', '.$dataString;
        }

        $this->tokenList->addRawTokenAfter($res->last(), $dataString);

        return $this->tokenList;
    }

    /**
     * Remove the next array from the token stream
     */
    public function remove()
    {
    }

    /**
     * Remove the given key(s) from the next array in the token stream.
     */
    public function removeKey()
    {
    }
}
