<?php

namespace Creads\Partners\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Creads\Partners\Configuration;

class GetCommand extends Command
{
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();
        $this->configuration = $configuration;
    }

    protected function configure()
    {
        $this
            ->setName('get')
            ->setDescription('Get a resource')
            ->addArgument(
              'URI',
              InputArgument::REQUIRED,
              'URI of the resource'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configuration->load();

        //@todo create a command helper, will be used on several commands
        // if (!$this->configuration->exists()) {
            $command = $this->getApplication()->find('login');
            $arguments = array(
                'command' => 'login'
            );
            $input2 = new ArrayInput($arguments);
            $returnCode = $command->run($input2, $output);
            if ($returnCode != 0) {
                return $returnCode;
            }
        // }

        $output->writeln('OK');
    }
}