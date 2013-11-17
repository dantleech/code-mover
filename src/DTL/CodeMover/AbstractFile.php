<?php

namespace DTL\CodeMover;

use DTL\CodeMover\Line;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFile extends LineCollection
{
    protected $file;
    protected $originalFile;
    protected $originalPath;
    protected $path;

    public function __construct(\SplFileInfo $file)
    {
        parent::__construct();

        $this->file = $file;
        $this->path = $file->getRealPath();
        $this->originalPath = $this->path;;

        $this->init();
    }

    public function nameMatches($pattern)
    {
        $pattern = Util::delimitRegex($pattern);
        return (boolean) preg_match($pattern, $this->file);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function write()
    {
        $filesystem = new Filesystem;
        $dirname = dirname($this->path);

        if (!file_exists($dirname)) {
            $filesystem->mkdir($dirname);
        }

        return file_put_contents($this->path, implode("\n", $this->originalFile->toArray()));
    }

    protected function init()
    {
        $lines = new LineCollection();
        $fileArray = file($this->file);

        array_walk($fileArray, function ($line) use ($lines) {
            $lines->addLine($line);
        });

        $this->originalFile = clone $lines;
        $this->addLines($lines);
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
        $this->originalFile = new LineCollection();
        foreach ($this as $line) {
            $this->originalFile[] = $line->getLine();
        }
    }

    public function isModified()
    {
        if ($this->originalFile->getRaw() == $this->getRaw()) {
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
