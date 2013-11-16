<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;

class PhpTokenList extends ArrayCollection
{
    protected $position = 0;

    public function getToken()
    {
        if ($this->offsetExists($this->position)) {
            return $this->offsetGet($this->position);
        }

        throw new \RuntimeException(sprintf('No token found at offset "%d"', $this->position));
    }

    public function seekValue($value)
    {
        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($value == $token->getValue()) {
                return $this;
            }

            $this->position++;
        }

        throw new \RuntimeException(sprintf(
            'Could not find token with value "%s", I have: "%s"',
            print_r($value, true),
            $value,
            implode(",", $this->dump())
        ));
    }

    public function seekType($type)
    {
        $type = Util::tokenNormalizeTypeToString($type);

        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($type == $token->getType()) {
                return $this;
            }

            $this->position++;
        }

        throw new \RuntimeException(sprintf(
            'Could not find token with type "%s", I have: "%s"',
            print_r($type, true),
            $type ? : '**any**',
            implode(",", $this->dump())
        ));
    }

    public function dump()
    {
        $out = array();
        foreach ($this as $token) {
            $out[] = $token->getType().'::^'.$token->getValue().'$';
        }

        return $out;
    }

    public function getTokensAsArray()
    {
        $tokens = array();
        foreach ($this as $token) {
            $tokens[] = array($token->getType(), $token->getValue());
        }

        return $tokens;
    }

    public function filterByType($type)
    {
        $type = Util::tokenNormalizeTypeToString($type);
        return $this->filter(function ($el) use ($type) {
            if ($el->getType() == $type) {
                return true;
            }

            return false;
        });
    }

    public function filterByValue($value)
    {
        return $this->filter(function ($el) use ($value) {
            if ($el->getValue() == $value) {
                return true;
            }

            return false;
        });
    }

    public function getValuesByType($type)
    {
        $type = Util::tokenNormalizeTypeToString($type);

        $list = $this->filterByType($type);
        return $list->getValues();
    }

    public function rewind()
    {
        $this->position = 0;

        return $this;
    }

    public function getValues()
    {
        $values = array();

        foreach ($this as $token) {
            $values[] = $token->getValue();
        }

        return $values;
    }

    public function getLines()
    {
        $lines = new MoverLineCollection;
        foreach ($this as $token) {
            if (!$lines->contains($token->getLine())) {
                $lines->add($token->getLine());
            }
        }

        return $lines;
    }

    public function findMatchingEnd($left, $right)
    {
        $leftCount = 0;
        $rightCount = 0;

        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($token->getValue() == $left) {
                $leftCount++;
            }

            if ($token->getValue() == $right) {
                $rightCount++;
            }

            if ($leftCount && $leftCount == $rightCount) {
                return $token;
            }

            $this->position++;
        }

        return null;
    }

    public function findBetween($left, $right)
    {
        $leftCount = 0;
        $rightCount = 0;

        $tokenList = new PhpTokenList();

        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($token->getValue() == $left) {
                $leftCount++;
            }

            if ($token->getValue() == $right) {
                $rightCount++;
            }

            if ($leftCount) {
                $tokenList->add($token);
            }

            if ($leftCount && $leftCount == $rightCount) {
                return $tokenList;
            }

            $this->position++;
        }

        return null;
    }
}
