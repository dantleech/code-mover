<?php

namespace DTL\CodeMover;

use DTL\CodeMover\MoverLine;

class MoverFile extends MoverLineCollection
{
    protected $file;
    protected $originalFile;

    public function __construct($file)
    {
        parent::__construct();
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
            $this->addLine($fileLine);
        }
    }

    public function setContent($text)
    {
        $this->clear();
        $lines = explode("\n", $text);
        $this->addLines($lines);
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

    public function createMethod($type, $name, $argumentString = null)
    {
        $method = new PhpMethod($this, $type, $name, $argumentString);
        return $method;
    }

    public function saveMethod(PhpMethod $method)
    {
        $existing = $this->findLine(sprintf('function %s', $method->getName()));
        if ($existing->count()) {
            throw new \Exception(sprintf('Method "%s" already exists', $method->getName()));
        }

        $lastBracket = $this->tokenize()->filterByValue('}')->last();
        $prevLine = $lastBracket->getLine()->prevLine();
        $this->addLinesAfter($method->getLines(), $prevLine);
    }

    public function addLine($line, $offset = null)
    {
        return $this->addLines(array($line), $offset);
    }

    public function addLines($lines, $offset = null)
    {
        $offset = $offset === null ? '-1' : $offset;

        $newLines = array();

        foreach ($this as $i => $existingLine) {
            if ($i == $offset) {

                foreach ($lines as $line) {
                    $newLines[] = new MoverLine($this, $line);
                }
            }

            $newLines[] = $existingLine;
        }

        if ($offset == -1) {
            foreach ($lines as $line) {
                $newLines[] = new MoverLine($this, $line);
            }
        }

        $this->clear();

        foreach ($newLines as $newLine) {
            $this->add($newLine);
        }

        return $this;
    }

    public function addLinesAfter($lines, MoverLine $targetLine)
    {
        $offset = $this->indexOf($targetLine);
        $this->addLines($lines, $offset + 1);
    }
}
