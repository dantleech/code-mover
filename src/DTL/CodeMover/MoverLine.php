<?php

namespace DTL\CodeMover;

class MoverLine
{
    protected $file;
    protected $originalLine;
    protected $line;

    public function __construct(MoverFile $file, $line)
    {
        $this->line = $line;
        $this->originalLine = $line;
        $this->file = $file;
    }

    public function match($pattern)
    {
        return preg_match($pattern, $this->line);
    }

    public function getLineNo()
    {
        return $this->file->getLines()->indexOf($this) + 1;
    }

    public function replace($pattern, $replacement)
    {
        $this->line = preg_replace($pattern, $replacement, $this->line);
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
}
