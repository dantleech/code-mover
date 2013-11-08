<?php

namespace DTL\CodeMover;

trait LoggableTrait
{
    protected $logger;

    public function log($message, $type = null)
    {
        if ($logger = $this->logger) {
            $logger($message, $type);
        }
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }
}