<?php

namespace DTL\CodeMover\File;

use DTL\CodeMover\File\Php\PhpFile;
use DTL\CodeMover\File\Generic\GenericFile;

class Factory
{
    public function getFile(\SplFileInfo $file)
    {
        if ($file->getExtension() == 'php') {
            return new PhpFile($file);
        } else {
            return new GenericFile($file);
        }
    }
}
