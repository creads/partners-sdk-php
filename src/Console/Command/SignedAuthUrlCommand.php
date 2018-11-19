<?php

namespace Creads\Partners\Console\Command;

use Creads\Partners\ClientFactory;
use Creads\Partners\SignedAuthUrlFactory;
use Creads\Partners\V0SignedAuthUrlFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SignedAuthUrlCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('signed-auth-url')
            ->setDescription('Generate a signed auth URL')
            ->addArgument(
                'email',
                InputArgument::REQUIRED
            )->addArgument(
                'organizationName',
                InputArgument::REQUIRED
            )->addArgument(
                'organizationRid',
                InputArgument::OPTIONAL
            )->addArgument(
                'firstname',
                InputArgument::OPTIONAL
            )->addArgument(
                'lastname',
                InputArgument::OPTIONAL
            )->addOption(
                'protocol',
                null,
                InputOption::VALUE_REQUIRED,
                'Set protocol to another version than default',
                1
            );
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 != $returnCode = $this->login($output)) {
            return $returnCode;
        }

        $configuration = $this->getHelperSet()->get('configuration');

        $organizationRid = $input->getArgument('organizationRid');
        $organizationName = $input->getArgument('organizationName');
        $email = $input->getArgument('email');
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');

        if (0 == $input->getOption('protocol')) {
            $signedAuthUrl = V0SignedAuthUrlFactory::create(
                $configuration,
                $organizationName,
                $email
            );
        } else {
            if (!$organizationRid) {
                throw new \RuntimeException('Undefined "organizationRid" argument');
            }
            if (!$firstname) {
                throw new \RuntimeException('Undefined "firstname" argument');
            }
            if (!$lastname) {
                throw new \RuntimeException('Undefined "lastname" argument');
            }
            $signedAuthUrl = SignedAuthUrlFactory::create(
                $configuration,
                $organizationRid,
                $organizationName,
                $email,
                $firstname,
                $lastname
            );
        }

        $output->writeln(sprintf('<comment>%s</comment>', $signedAuthUrl));
    }

}
