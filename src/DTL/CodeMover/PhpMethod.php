<?php

namespace DTL\CodeMover;

class PhpMethod
{
    protected $lines = array();
    protected $file;
    protected $type;
    protected $name;
    protected $argumentString;

    public function __construct(MoverFile $file, $type = 'public', $name, $argumentString)
    {
        $this->type = $type;
        $this->name = $name;
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
        $method = array(
            sprintf('%s function %s(%s)'."\n", $this->type, $this->name, $this->argumentString),
            '{'."\n"
        );

        foreach ($this->lines as $line) {
            $method[] = $line.",\n";
        }

        $method[] = '}'."\n";

        return $method;
    }

    public function addLine($line)
    {
        $this->lines[] = $line;
    }
}
