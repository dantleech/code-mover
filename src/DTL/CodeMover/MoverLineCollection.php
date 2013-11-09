<?php

namespace DTL\CodeMover;

use Doctrine\Common\Collections\ArrayCollection;

class MoverLineCollection extends ArrayCollection
{
    public function match($pattern)
    {
        return preg_match($pattern, $this->line);
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
}
