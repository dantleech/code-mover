<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;

class PhpTokenList extends ArrayCollection
{
    public function dump()
    {
        $out = array();
        foreach ($this as $token) {
            echo $token->getType().'::^'.$token->getValue().'$'."\n";
        }

        die(1);
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
}
