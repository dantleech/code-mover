<?php

namespace DTL\CodeMover\File\Php;

use DTL\CodeMover\AbstractFile;
use DTL\CodeMover\File\Php\PhpMethod;

class PhpFile extends AbstractFile
{
    public function createMethod($type, $name, $argumentString = null)
    {
        $method = new PhpMethod($this, $type, $name, $argumentString);
        return $method;
    }

    public function saveMethod(PhpMethod $method)
    {
        $existing = $this->findLine(sprintf('function %s', $method->getName()));
        if ($existing->count()) {
            throw new \RuntimeException(sprintf('Method "%s" already exists', $method->getName()));
        }

        $lastBracket = $this->tokenize()->filterByValue('}')->last();
        $prevLine = $lastBracket->getLine()->prevLine();
        $this->addLinesAfter($prevLine, $method->getLines());
    }

    public function getSplFileInfo()
    {
        return $this->file;
    }
}

