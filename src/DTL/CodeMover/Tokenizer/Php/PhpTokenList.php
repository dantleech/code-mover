<?php

namespace DTL\CodeMover\Tokenizer\Php;

use DTL\CodeMover\Util;
use DTL\CodeMover\LineCollection;
use DTL\CodeMover\Tokenizer\Php\PhpToken;
use DTL\CodeMover\Tokenizer\Php\PhpArray;
use DTL\CodeMover\Tokenizer\TokenListInterface;
use DTL\CodeMover\Tokenizer\TokenInterface;
use DTL\CodeMover\LineInterface;
use DTL\CodeMover\AbstractCollection;

class PhpTokenList extends AbstractCollection implements TokenListInterface, TokenInterface
{
    const TOKEN_TYPE_RAW = '_RAW_';

    protected $bracketMap = array(
        '{' => '}',
        '(' => ')',
        '[' => ']',
    );

    protected function getClosingBracket($left)
    {
        if (!isset($this->bracketMap[$left])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot automatically determine closing bracket for "%s" - I only know: "%s"',
                $left, implode(', ', array_keys($this->bracketMap))
            ));
        }

        return $this->bracketMap[$left];
    }

    public function getToken()
    {
        if ($this->offsetExists($this->position)) {
            return $this->offsetGet($this->position);
        }

        throw new \RuntimeException(sprintf('No token found at offset "%d"', $this->position));
    }

    public function addToken($type, $value = null)
    {
        if ($type instanceof PhpToken) {
            $token = $type;
        } else {
            $token = new PhpToken($type, $value);
        }

        $this->add($token);

        return $this;
    }

    public function addRawTokenAfter(PhpToken $targetToken = null, $rawData)

    {
        $rawToken = new PhpToken(self::TOKEN_TYPE_RAW, $rawData);
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

    public function addRawToken($rawData)
    {
        $rawToken = new PhpToken(self::TOKEN_TYPE_RAW, $rawData);
        $this->add($rawToken);
        return $this;
    }

    public function seekValue($value)
    {
        $originalPosition = $this->position;

        while ($this->valid()) {
            if ($value == $this->getToken()->getValue()) {
                return $this;
            }

            $this->next();
        }

        $this->throwException(new \RuntimeException(sprintf(
            'Could not find token with value "%s", I have: "%s"',
            print_r($value, true),
            implode(",", $this->dump())
        )));

        $this->position = $originalPosition;

        return $this;
    }

    public function seekEncapsedString($value)
    {
        while ($this->offsetExists($this->position)) {
            $token = $this->offsetGet($this->position);

            if ($value == substr($token->getValue(), 1, -1)) {
                return $this;
            }

            $this->next();
        }

        $this->throwException(new \RuntimeException(sprintf(
            'Could not find token with value "%s", I have: "%s"',
            print_r($value, true),
            implode(",", $this->dump())
        )));

        return $this;
    }

    public function seekType($type)
    {
        $originalPosition = $this->position;

        $this->position++;

        $type = Util::tokenNormalizeTypeToString($type);

        while ($this->valid()) {
            if ($type == $this->getToken()->getType()) {
                return $this;
            }

            $this->position++;
        }

        $this->throwException(new \RuntimeException(sprintf(
            'Could not find token with type "%s", I have: "%s"',
            print_r($type, true),
            implode(",", $this->dump())
        )));

        $this->position = $originalPosition;
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

    public function filterByValue($value, $invert = false)
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
            if (!$lines->has($token->getLine())) {
                $lines->add($token->getLine());
            }
        }

        return $lines;
    }

    /**
     * Find the right matching token for a given left token value
     *
     * @param string $left - Value of left token
     * @param string $right - (optional) Value of right token
     */
    public function findMatchingBracket($left, $right = null)
    {
        if (null === $right) {
            $right = $this->getClosingBracket($left);
        }

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

    public function findBetween($left, $right = null)
    {
        if (null === $right) {
            $right = $this->getClosingBracket($left);
        }

        $leftCount = 0;
        $rightCount = 0;

        $tokenList = new PhpTokenList();

        while ($this->valid()) {
            $token = $this->current();

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

            $this->next();
        }

        return new PhpTokenList();
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

    public function castArray()
    {
        $array = new PhpArray();

        if ($this->getType() != 'ARRAY') {
            $this->throwException(new \InvalidArgumentException(
                'Current token must be of type T_ARRAY to use the toArray method. Currently on ' . $this->getType()
            ));

            return $array;
        }

        $array->loadTokenList($this);

        return $array;
    }
}
