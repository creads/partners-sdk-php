<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand
{
    protected function login(OutputInterface $output)
    {
        $configuration = $this->getHelperSet()->get('configuration');

        if (!$configuration->exists()
            || !isset($configuration['access_token'])
            || (isset($configuration['expires_at']) && time() > $configuration['expires_at'])
        ) {
            //run login if configuration does not exists
            //if the access token does not exist
            //or if the access token is expired
            $command = $this->getApplication()->find('login');
            $arguments = array(
                'command' => 'login',
            );
            $input2 = new ArrayInput($arguments);
            $returnCode = $command->run($input2, $output);

            return $returnCode;
        }
    }
}
