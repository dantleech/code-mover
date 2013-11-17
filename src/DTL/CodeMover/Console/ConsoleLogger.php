<?php

namespace DTL\CodeMover\Console;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    protected $output;
    protected $verbosity;
    protected $startTime;

    public function __construct(OutputInterface $output, $verbosity = 0) 
    {
        $this->output = $output;
        $this->verbosity = $verbosity;
        $this->startTime = microtime(true);
    }

    public function log($level, $message, array $context = array())
    {
        if (isset($context['style'])) {
            $message = sprintf('<%s>%s</%s>', $context['style'], $message, $context['style']);
        }

        $this->output->writeln(sprintf('[%s][%s] %s',
            number_format(microtime(true) - $this->startTime, 4), $level, $message
        ));
    }
}
