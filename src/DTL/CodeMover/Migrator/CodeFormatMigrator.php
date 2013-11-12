<?php

namespace DTL\CodeMover\Migrator;

use DTL\CodeMover\MoverFile;
use DTL\CodeMover\AbstractMigrator;

class CodeFormatMigrator extends AbstractMigrator
{
    public function getName()
    {
        return 'default.code_formatter';
    }

    public function getDependencies()
    {
        return array();
    }

    public function accepts(MoverFile $file)
    {
        return $file->nameMatches('/.*\.php/');
    }

    public function migrate(MoverFile $file)
    {
        $this->fixNamespaceAndUse($file);
        $this->fixExtraSpaces($file);
        $this->fixIndentation($file);
    }

    protected function fixNamespaceAndUse(MoverFile $file)
    {
        $namespace = $file->findLine('namespace');

        // ensure blank line after namespace
        if (!$namespace->nextLine()->match('^$')) {
            $file->addLineAfter($namespace, '');
        }

        if (!$namespace->prevLine()->match('^$')) {
            $file->addLineBefore($namespace, '');
        }

        $uses = $file->findLines('use .*');
        $uses->delete();
        $newUses = clone $uses;

        $file->addLinesAfter($namespace->nextLine(), $newUses);

    }

    public function fixExtraSpaces(MoverFile $file)
    {
        // ensure only single spaces
        $firstBlank = false;
        foreach ($file as $line) {
            if (false == $firstBlank && $line->match('^ *$')) {
                $firstBlank = true;
                continue;
            }

            if (!$line->match('^$')) {
                $firstBlank = false;
            }

            if ($firstBlank && $line->match('^$')) {
                $line->delete();
                continue;
            }
        }

        foreach ($file->findLines('function') as $line) {
            $endToken = $line->tokenizeBetween('{', '}')->last();
            if (
                !$endToken->getLine()->nextLine()->match('^ *$')
                && !$endToken->getLine()->nextLine()->match('^ *} *$')
            ) {
                $file->addLineAfter($endToken->getLine(), '');
            }
        }
    }

    public function fixIndentation(MoverFile $file)
    {
        $i = 0;
        foreach ($file as $line) {
            $line->replace(' *(.*)$', str_repeat('    ', $i).'\1');

            $tokens = $line->tokenize();
            if (in_array('{', $tokens->values())) {
                $i += 1;;
            }
            if (in_array('}', $tokens->values())) {
                $i -= 1;;
                $line->replace(' *(.*)$', str_repeat('    ', $i).'\1');
            }
        }
    }
}
