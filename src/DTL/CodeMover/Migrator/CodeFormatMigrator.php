<?php

namespace DTL\CodeMover\Migrator;

use DTL\CodeMover\AbstractFile;
use DTL\CodeMover\AbstractMigrator;
use DTL\CodeMover\MigratorContext;

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

    public function accepts(AbstractFile $file)
    {
        return $file->nameMatches('/.*\.php/');
    }

    public function migrate(MigratorContext $context)
    {
        $file = $context->getFile();
        //$this->fixNamespaceAndUse($file);
        //$this->fixExtraSpaces($file);
        $this->fixIndentation($file);
        //$this->vimify($file);
    }

    protected function vimify(AbstractFile $file)
    {
        $tmpfile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'code_mover_vimify.'.$file->getSplFileInfo()->getExtension();
        file_put_contents($tmpfile, $file->getContent());
        exec(sprintf('vim %s -c "normal =G" -c "wq"', $tmpfile));
        $content = file_get_contents($tmpfile);
        $file->setContent($content);
    }

    protected function beautify(AbstractFile $file)
    {
        $beautifier = new \PHP_Beautifier();
        $beautifier->setInputString($file->getContent());
        $filters = $beautifier->getFilterList();
        new \PHP_Beautifier_Filter_IndentStyles($beautifier);
        $beautifier->process();
        $file->setContent($beautifier->get());
    }

    protected function fixNamespaceAndUse(AbstractFile $file)
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

    public function fixExtraSpaces(AbstractFile $file)
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
            if (!$endToken = $line->tokenizeBetween('{', '}')->last()) {
                continue;
            }
            if ($endToken->getLine()->nextLine()) {
                if (
                    !$endToken->getLine()->nextLine()->match('^ *$')
                    && !$endToken->getLine()->nextLine()->match('^ *} *$')
                ) {
                    $file->addLineAfter($endToken->getLine(), '');
                }
            }
        }
    }

    public function fixIndentation(AbstractFile $file)
    {
        $i = 0;
        foreach ($file as $line) {
            $tokens = $line->tokenize();
            $firstToken = $tokens->filterByType('WHITESPACE', true)->first();

            // special cases
            $additionalIndent = 0;
            if ($firstToken) {
                switch ($firstToken->getValue()) {
                    case 'array' :
                        $arrayLine = $line->getLine();
                        $before = $file->getLineNeighbor($firstToken->getLine(), true);
                        $beforeLastToken = $before->tokenize()->filterByType('WHITESPACE', true)->last();
                        $space = ' ';
                        if ($beforeLastToken->getValue() == '(') {
                            $space = '';
                        } else {
                            $i++;
                        }

                    //    $before->setLine(rtrim($before->getLine()).$space.trim($arrayLine));
                    //    $firstToken->getLine()->delete();
                        break;
                    case '->':
                        $additionalIndent = 4;
                        break;
                    case '*':
                        $continue = true;
                        $additionalIndent = 1;
                        break;
                    }
            }

            $line->replace(' *(.*)$', str_repeat(' ', ($i * 4) + $additionalIndent).'\1');

            $tokens = $tokens->filterByType('SINGLE_CHAR');

            if (!$tokens->count()) {
                continue;
            }

            if (in_array($tokens->first()->getValue(), array('}', ')'))) {
                $i -= 1;;

                if ($i < 0) {
                    $i = 0;
                }

                $line->replace(' *(.*)$', str_repeat('    ', $i).'\1');
            }

            if (in_array($tokens->last()->getValue(), array('(', '{'))) {
                $i += 1;;
            }
        }
    }
}
