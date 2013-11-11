<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;

class PhpTokenList extends ArrayCollection
{
    protected $position = 0;

    public function dump()
    {
        $out = array();
        foreach ($this as $token) {
            $out[] = $token->getType().'::^'.$token->getValue().'$';
        }

        return $out;
    }

    public function nextToken($type = null)
    {
        $i = $this->position + 1;

        while ($this->offsetExists($i)) {
            $token = $this->offsetGet($i);

            if (null === $type) {
                return $token;
            }

            if ($token->getType() == $type) {
                return $token;
            }

            $i++;
        }
    }

    public function currentToken()
    {
        if ($this->offsetExists($this->position)) {
            return $this->offsetGet($this->position);
        }
    }

    public function seekToken($tokenValue, $type = null)
    {
        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($tokenValue instanceof \PhpToken) {
                if ($tokenValue === $token) {
                    return $this;
                }
            } elseif (null === $type && $tokenValue == $token->getValue()) {
                return $this;
            } elseif ($type == $token->getType() && $tokenValue == $token->getValue()) {
                return $this;
            }

            $this->position++;
        }

        return null;
    }

    public function seekTokenOrDie($tokenValue, $type = null)
    {
        $res = $this->seekToken($tokenValue, $type);
        if (null === $res) {
            throw new \RuntimeException(sprintf(
                'Could not find token with value "%s" and type "%s", I have: "%s"',
                print_r($tokenValue, true),
                $type ? : '**any**',
                implode(",", $this->dump())
            ));
        }

        return $res;
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

    public function valuesByType($type)
    {
        $list = $this->filterByType($type);
        return $list->getValues();
    }

    public function getValues()
    {
        $values = array();

        foreach ($this as $token) {
            $values[] = $token->getValue();
        }

        return $values;
    }

    public function lines()
    {
        $lines = new MoverLineCollection;
        foreach ($this as $token) {
            if (!$lines->contains($token->getLine())) {
                $lines->add($token->getLine());
            }
        }

        return $lines;
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
    }
}
