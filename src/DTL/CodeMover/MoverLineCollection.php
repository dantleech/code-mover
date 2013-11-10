<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;

class MoverLineCollection extends ArrayCollection
{
    public function match($pattern)
    {
        foreach ($this as $line) {
            if (preg_match($pattern, $this->line)) {
                return true;
            }
        }

        return false;
    }

    public function replace($pattern, $replacement)
    {
        foreach ($this as $line) {
            $line->replace($pattern, $replacement);
        }

        return $this;
    }

    public function delete()
    {
        foreach ($this as $line) {
            $line->delete();
        }

        return $this;
    }

    public function dump()
    {
        echo "Dumping ".$this->count()." lines\n";
        foreach ($this as $line) {
            echo $line."\n";
        }
        echo "Finished dumping\n";
        die(1);
    }

    public function findLine($pattern)
    {
        foreach ($this as $line) {
            if ($line->match($pattern)) {
                return $line;
            }
        }

        return null;
    }

    public function findLines($patterns)
    {
        $patterns = (array) $patterns;

        $lineCollection = new MoverLineCollection();
        foreach($this as $line) {
            if ($line->match($patterns)) {
                $lineCollection->add($line);
            }
        }

        return $lineCollection;
    }
}
