<?php

namespace DTL\CodeMover;

class RegExResult
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
}
