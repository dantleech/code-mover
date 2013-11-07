<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;
use DTL\CodeMover\MoverLine;

class MoverFile extends ArrayCollection
{
    protected $_initialized = false;
    protected $file;
    protected $lines;

    public function __construct($file)
    {
        $this->lines = new ArrayCollection();
        $this->file = $file;
    }

    public function init()
    {
        if ($this->_initialized) {
            return;
        }

        foreach (file($this->file) as $fileLine) {
            $this->lines->add(new MoverLine($this, $fileLine));
        }

        $this->initialized = true;
    }

    public function findLine($pattern)
    {
        $this->init();

        $line = $this->lines->current();

        while ($line) {
            if ($line->match($pattern)) {
                return $line;
            }
            $line = $this->lines->next();
        }

        return null;
    }

    public function findLines($pattern)
    {
        $this->init();

        $lines = $this->lines->filter(function ($line) use ($pattern) {
            return $line->match($pattern);
        });

        return $lines;
    }

    public function getLines()
    {
        return $this->lines;
    }
}
