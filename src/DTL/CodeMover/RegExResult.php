<?php

namespace DTL\CodeMover;

use DTL\CodeMover\AbstractCollection;

class RegExResult extends AbstractCollection
{
    protected $line;

    public function setLine(LineInterface $line)
    {
        $this->line = $line;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function dump()
    {
        return print_r($this->toArray(), true);
    }

    public function apply(\Closure $closure)
    {
        $args = array($this->line);
        foreach ($this->trim(1, 0) as $match) {
            $args[] = $match;
        }
        call_user_func_array($closure, $args);
    }

    public function getMatch($i)
    {
        if (!isset($this->elements[$i])) {
            throw new \InvalidArgumentException(sprintf(
                'No elements for index "%s". Have: ',
                $i, print_r($this->elements, true)
            ));
        }

        return $this->elements[$i];
    }

    public function getMatches()
    {
        return $this->elements;
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
