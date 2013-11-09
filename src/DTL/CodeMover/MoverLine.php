<?php

namespace DTL\CodeMover;

class MoverLine
{
    const REGEX_DELIMITER = '/';

    protected $file;
    protected $originalLine;
    protected $line;

    public function __construct(MoverFile $file, $line)
    {
        $this->line = $line;
        $this->originalLine = $line;
        $this->file = $file;
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
        return $this->file->getLines()->indexOf($this) + 1;
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
        if (!$this->file->getLines()->removeElement($this)) {
            throw new \Exception('Could not delete element');
        }
    }

    private function delimitRegex($pattern)
    {
        if (substr($pattern, 0, 1) == self::REGEX_DELIMITER ) {
            return $pattern;
        }

        return self::REGEX_DELIMITER.$pattern.self::REGEX_DELIMITER;
    }
}
