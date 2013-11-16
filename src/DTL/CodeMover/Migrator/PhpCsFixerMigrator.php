<?php

namespace DTL\CodeMover\Migrator;

use Symfony\CS\Fixer;
use DTL\CodeMover\MigratorContext;

class PhpCsFixerMigrator extends AbstractMigrator
{
    public function getName()
    {
        return 'default.php_cs_fixer';
    }

    public function getDependencies()
    {
        return array();
    }

    public function accepts(MoverFile $file)
    {
        return true;
    }

    public function migrate(MigratorContext $context)
    {
        $file = $context->getFile();
        $file = $file->getSplFileInfo();

        $fixer = new Fixer;
        $fixer->registerBuiltInFixers();
        $fixers = $fixer->getFixers();

        foreach ($fixers as $fixer) {
            if ($fixer->supports($file)) {
                $content = $fixer->fix($file, $content);
            }
        }

        $mFile->setContent($content);
    }
}
