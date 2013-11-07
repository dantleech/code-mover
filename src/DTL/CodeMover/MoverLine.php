<?php

namespace DTL\CodeMover;

class MoverLine
{
    protected $file;
    protected $line;

    public function __construct(MoverFile $file, $line)
    {
        $this->line = $line;
        $this->file = $file;
    }

    public function match($pattern)
    {
        $delimiter = '&';
        return preg_match($delimiter.$pattern.$delimiter, $this->line);
    }

    public function getLineNo()
    {
        return $this->file->getLines()->indexOf($this) + 1;
    }
}
