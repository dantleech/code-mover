<?php

namespace DTL\CodeMover;

class RegExResult implements \ArrayAccess
{
    protected $matches;

    public function __construct($matches)
    {
        $this->matches = $matches;
    }

    public function getMatch($i)
    {
        if (!isset($this->matches[$i])) {
            throw new \InvalidArgumentException(sprintf(
                'No matches for index "%s". Have: ',
                $i, print_r($this->matches, true)
            ));
        }

        return $this->matches[$i];
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function offsetGet($offset) 
    {
        return $this->matches[$offset];
    }

    public function offsetExists($offset)
    {
        return isset($this->matches[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('RegExResult is readonly.');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('RegExResult is readonly.');
    }
}
