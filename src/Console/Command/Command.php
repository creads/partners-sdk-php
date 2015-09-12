<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Creads\Partners\Console\Configuration;

abstract class Command extends BaseCommand
{
    /**
     * @var Creads\Partners\Console\Configuration
     */
    protected $configuration;

    /**
     * Constructor
     * @param Configuration $configuration
     * @param string        $name
     */
    public function __construct(Configuration $configuration, $name = null)
    {
        parent::__construct($name);
        $this->configuration = $configuration;

        $this->configuration->load();

        //configure common options and arguments
        //...
    }
}