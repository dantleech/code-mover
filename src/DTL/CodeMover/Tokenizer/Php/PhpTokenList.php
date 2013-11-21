<?php

namespace DTL\CodeMover\Tokenizer\Php;

use Doctrine\Common\Collections\ArrayCollection;
use DTL\CodeMover\Util;
use DTL\CodeMover\LineCollection;
use DTL\CodeMover\Tokenizer\Php\PhpToken;
use DTL\CodeMover\Tokenizer\TokenListInterface;
use DTL\CodeMover\Tokenizer\TokenInterface;
use DTL\CodeMover\LineInterface;

class PhpTokenList extends ArrayCollection implements TokenListInterface, TokenInterface
{
    protected $position = 0;
    protected $bomb = true;

    protected function throwException(\Exception $e)
    {
        if ($this->bomb) {
            throw $e;
        }
    }

    public function getToken()
    {
        if ($this->offsetExists($this->position)) {
            return $this->offsetGet($this->position);
        }

        $this->throwException(new \RuntimeException(sprintf('No token found at offset "%d"', $this->position)));
    }

    public function reset()
    {
        $this->position = 0;
        return $this;
    }

    public function addRawTokenAfter(PhpToken $targetToken, $rawData)
    {
        $rawToken = new PhpToken(null, '_RAW', $rawData);
        $newList = array();

        foreach ($this as $token) {
            $newList[] = $token;

            if ($token === $targetToken) {
                $newList[] = $rawToken;
            }
        }

        $this->clear();
        foreach ($newList as $token) {
            $this->add($token);
        }

        return $this;
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

        $this->throwException(new \RuntimeException(sprintf(
            'Could not find token with value "%s", I have: "%s"',
            print_r($value, true),
            implode(",", $this->dump())
        )));

        return $this;
    }

    public function seekEncapsedString($value)
    {
        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($value == substr($token->getValue(), 1, -1)) {
                return $this;
            }

            $this->position++;
        }

        $this->throwException(new \RuntimeException(sprintf(
            'Could not find token with value "%s", I have: "%s"',
            print_r($value, true),
            implode(",", $this->dump())
        )));

        return $this;
    }

    public function subtract(TokenListInterface $tokenList)
    {
        $currentList = clone $this;
        $this->clear();

        foreach ($currentList as $token) {
            if (false === $tokenList->has($token)) {
                $this->add($token);
            }
        }

        return $this;
    }

    public function next()
    {
        $this->position++;
        return $this;
    }

    public function seekType($type)
    {
        $this->position++;

        $type = Util::tokenNormalizeTypeToString($type);

        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($type == $token->getType()) {
                return $this;
            }

            $this->position++;
        }

        $this->throwException(new \RuntimeException(sprintf(
            'Could not find token with type "%s", I have: "%s"',
            print_r($type, true),
            implode(",", $this->dump())
        )));
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

    public function filter(\Closure $closure)
    {
        $tokenList = new PhpTokenList;
        foreach ($this as $el) {
            if ($closure($el)) {
                $tokenList[] = $el;
            }
        }

        return $tokenList;
    }

    public function filterByType($type, $invert = false)
    {
        $type = Util::tokenNormalizeTypeToString($type);

        return $this->filter(function ($el) use ($type, $invert) {
            if ($el->getType() == $type) {
                return $invert ? false : true;
            }

            return $invert ? true : false;
        });
    }

    public function filterByValue($value, $invert)
    {
        return $this->filter(function ($el) use ($value, $invert) {
            if ($el->getValue() == $value) {
                return $invert ? false : true;
            }

            return $invert ? true : false;
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
        $lines = new LineCollection;
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

    public function has(PhpToken $targetToken)
    {
        foreach ($this as $token) {
            if ($token === $targetToken) {
                return true;
            }
        }

        return false;
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

    public function trim($leftOffset, $rightAmount)
    {
        $tokenList = new PhpTokenList;
        $i = 0;
        $rightLimit = $this->count() - $rightAmount;

        foreach ($this as $token) {
            if ($i >= $leftOffset && $i < $rightLimit) {
                $tokenList->add($token);
            }
            $i++;
        }

        return $tokenList;
    }

    public function __toString()
    {
        return implode("", $this->getValues());
    }

    public function setValue($value)
    {
        $this->getToken()->setValue();
    }

    public function getValue()
    {
        return $this->getToken()->getValue();
    }

    public function getType()
    {
        return $this->getToken()->getType();
    }

    public function getLine()
    {
        return $this->getToken()->getLine();
    }

    public function setLine(LineInterface $line)
    {
        throw new \BadMethodCallException('Cannot call setLine on TokenList');
    }

    public function setType($type)
    {
        throw new \BadMethodCallException('Cannot call setType on TokenList');
    }

    public function bomb($boolean)
    {
        $this->bomb = $boolean;
        return $this;
    }

    public function checkAhead($offset = 1)
    {
        return $this->offsetGet($this->position + $offset);
    }

    public function hasToken()
    {
        return $this->offsetExists($this->position);
    }

    public function castArray()
    {
        if ($this->getType() != 'ARRAY') {
            throw new \InvalidArgumentException(
                'Current token must be of type T_ARRAY to use the toArray method. Currently on ' . $this->getType()
            );
        }

        $tokens = $this->findBetween('(', ')')->filterByType(T_WHITESPACE, true)->trim(1, 1)->filterByValue(',', true);

        $ret = array();

        do {
            $key = $tokens->getValue();
            $next = $tokens->checkAhead();
            if ($next && $next->getType() == 'DOUBLE_ARROW') {
                $value = $tokens->next()->next()->getValue();
                $ret[$key] = $value;
            } else {
                $ret[] = $key;
            }

            $tokens->next();
        } while ($tokens->hasToken());

        return $ret;
    }
}
