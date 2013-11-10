<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverLine;

class MoverFile extends MoverLineCollection
{
    protected $file;
    protected $originalFile;

    public function __construct($file)
    {
        $this->file = $file;
        $this->init();
    }

    public function nameMatches($pattern)
    {
        return (boolean) preg_match($pattern, $this->file);
    }

    protected function init()
    {
        $this->originalFile = file($this->file);
        foreach ($this->originalFile as $fileLine) {
            $this->add(new MoverLine($this, $fileLine));
        }
    }

    public function getOriginalFile()
    {
        return $this->originalFile;
    }

    public function commit()
    {
        $this->originalFile = array();
        foreach ($this as $line) {
            $this->originalFile[] = $line->getLine();
        }
    }

    public function isModified()
    {
        if ($this->originalFile == $this->toArray()) {
            return false;
        }

        return true;
    }
}
