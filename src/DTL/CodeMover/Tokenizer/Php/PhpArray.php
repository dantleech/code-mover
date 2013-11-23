<?php

namespace DTL\CodeMover\Tokenizer\Php;

class PhpArray implements \ArrayAccess
{
    protected $array;

    public function loadTokenList(PhpTokenList $tokenList)
    {
        $array = array();
        $tokens = $tokenList->seekValue('array')->findBetween('(', ')')->filterByType(T_WHITESPACE, true)->trim(1, 1)->filterByValue(',', true);

        if (!$tokens->hasToken()) {
            $this->array = array();
            return $this;
        }

        do {
            $key = $tokens->getValue();
            $next = $tokens->checkAhead();
            if ($next && $next->getType() == 'DOUBLE_ARROW') {
                $value = $tokens->next()->next()->getValue();
                $array[$key] = $value;
            } else {
                $array[] = $key;
            }

            $tokens->next();
        } while ($tokens->hasToken());

        $this->array = $array;

        return $this;
    }

    public function __toString()
    {
        $els = array('array', '(');
        foreach ($this->array as $key => $value) {
            $els[] = sprintf('%s => %s,', $key, $value);
        }
        $els[] = ')';

        return implode(' ', $els);
    }

    public function offsetGet($offset) 
    {
        return $this->array[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('RegExResult is readonly.');
    }

    public function toArray()
    {
        return $this->array;
    }
}
