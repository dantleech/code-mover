<?php

namespace DTL\CodeMover\File\Php;

use DTL\CodeMover\AbstractFile;
use DTL\CodeMover\LineCollection;

class PhpMethod
{
    protected $lines = array();
    protected $file;
    protected $type;
    protected $name;
    protected $argumentString;

    public function __construct(AbstractFile $file, $type = 'public', $name, $argumentString)
    {
        $this->type = $type;
        $this->name = $name;
        $this->file = $file;
        $this->argumentString = $argumentString;
    }

    public function getType() 
    {
        return $this->type;
    }

    public function getName() 
    {
        return $this->name;
    }

    public function getArgumentString() 
    {
        return $this->argumentString;
    }

    public function getLines() 
    {
        $lines = new LineCollection();
        $lines->addLines(array(
            sprintf('%s function %s(%s)'."\n", $this->type, $this->name, $this->argumentString),
            '{'
        ));

        foreach ($this->lines as $line) {
            $lines->addLine($line);
        }

        $lines->addLine('}');

        return $lines;
    }

    public function addLine($line)
    {
        $this->lines[] = $line;
    }
}
