<?php

namespace DTL\CodeMover;

use DTL\CodeMover\PhpTokenList;
use DTL\CodeMover\PhpToken;

class MoverLine implements MoverLineInterface
{
    const REGEX_DELIMITER = '/';

    protected $file;
    protected $originalLine;
    protected $line;

    public function __construct(MoverLineCollection $file, $line)
    {
        $line = str_replace("\n", "", $line);
        $this->line = $line;
        $this->originalLine = $line;
        $this->file = $file;
    }

    public function getSingle()
    {
        return $this;
    }

    public function match($patterns)
    {
        $patterns = (array) $patterns;
        foreach ($patterns as $pattern) {
            $pattern = $this->delimitRegex($pattern);
            $match = preg_match($pattern, $this->line);

            if ($match) {
                return true;
            }
        }

        return false;
    }

    public function getLineNo()
    {
        return $this->file->indexOf($this) + 1;
    }

    public function replace($patterns, $replacements)
    {
        $patterns = (array) $patterns;
        $me = $this;
        array_walk($patterns, function (&$el) use ($me) {
            $el = $me->delimitRegex($el);
        });

        if ($replacements instanceof \Closure) {
            $this->line = preg_replace_callback($patterns, $replacements, $this->line);
        } else {
            $this->line = preg_replace($patterns, $replacements, $this->line);
        }

        return $this;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }

    public function nextLine()
    {
        return $this->file->getLineNeighbor($this);
    }

    public function prevLine()
    {
        return $this->file->getLineNeighbor($this, true);
    }

    public function getOriginalLine()
    {
        return $this->originalLine;
    }

    public function hasChanged()
    {
        return $this->originalLine != $this->line;
    }

    public function __toString()
    {
        return $this->line;
    }

    public function delete()
    {
        if (!$this->file->removeElement($this)) {
            throw new \RuntimeException('Could not delete element');
        }

        return $this;
    }

    private function delimitRegex($pattern)
    {
        if (substr($pattern, 0, 1) == self::REGEX_DELIMITER ) {
            return $pattern;
        }

        return self::REGEX_DELIMITER.$pattern.self::REGEX_DELIMITER;
    }

    /**
     * Tokenize - only works for PHP at the moment.
     *
     * @return array
     */
    public function tokenize()
    {
        $constants = get_defined_constants(true);
        $tokenMap = array_flip($constants['tokenizer']);

        $tokenList = new PhpTokenList();

        // need to add a php tag for tokenizer to work
        $tokens = token_get_all('<?php '.$this->line);

        // remove the php tag
        array_shift($tokens);

        foreach ($tokens as $token) {
            if (is_array($token)) {
                list($tokenType, $tokenValue) = $token;
                if (isset($tokenMap[$tokenType])) {
                    $tokenType = substr($tokenMap[$tokenType], 2);
                }

                $tokenList->add(new PhpToken($this, $tokenType, $tokenValue));
                continue;
            }

            $tokenList->add($token = new PhpToken($this, 'SINGLE_CHAR', $token));

            if ($token->isEndOfStatement()) {
                break;
            }
        }

        return $tokenList;
    }

    public function tokenizeStatement()
    {
        $statementTokens = new PhpTokenList;
        $line = $this;

        while ($line) {
            foreach ($line->tokenize() as $token) {
                $statementTokens[] = $token;

                if ($token->isEndOfStatement()) {
                    return $statementTokens;
                }
            }
            $line = $line->nextLine();
        }

        return $statementTokens;
    }

    /**
     * Tokenize until the number of $leftString equals the number of $rightString
     */
    public function tokenizeBetween($leftString, $rightString)
    {
        $tokenList = new PhpTokenList();
        $line = $this;
        $started = false;

        while ($line) {
            foreach ($line->tokenize() as $token) {
                $tokenList->add($token);
                if ($token->getValue() == $leftString) {
                    $started = true;
                }
            }

            if (null !== $betweenList = $tokenList->rewind()->findBetween($leftString, $rightString)) {
                return $betweenList;
            }

            $line = $line->nextLine();
        }

        if ($started) {
            throw new \RuntimeException(sprintf(
                'Could not find end string "%s" for tokenizeBetween method', $rightString
            ));
        }

        return new PhpTokenList();
    }
}
