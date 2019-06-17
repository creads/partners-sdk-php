<?php

namespace Creads\Partners\Console\Command;

use Creads\Partners\SignedAuthenticationUrlFactory;
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
                InputArgument::REQUIRED,
                'Set user email (Required)'
            )->addOption(
                'organizationName',
                null,
                InputOption::VALUE_REQUIRED,
                'Set organization name (Required by RFC0)'
            )->addOption(
                'firstname',
                null,
                InputOption::VALUE_REQUIRED,
                'Set organization remote ID'
            )->addOption(
                'lastname',
                null,
                InputOption::VALUE_REQUIRED,
                'Set user lastname'
            )->addOption(
                'organizationRid',
                null,
                InputOption::VALUE_REQUIRED,
                'Set organization Remote ID'
            )->addOption(
                'userRid',
                null,
                InputOption::VALUE_REQUIRED,
                'Set user remote ID (Required by RFC2)'
            )->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Set user nickname'
            )->addOption(
                'protocol',
                null,
                InputOption::VALUE_REQUIRED,
                'Set protocol to another version than default (values: 0 for RFC0 or 2 for RFC2)',
                SignedAuthenticationUrlFactory::RFC2_SIGNATURE_PROTOCOL
            )->addOption(
                'api-base-uri',
                null,
                InputOption::VALUE_REQUIRED,
                'Set api base URI than default'
            )->addOption(
                'client-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Set client ID than default'
            )->addOption(
                'client-secret',
                null,
                InputOption::VALUE_REQUIRED,
                'Set client secret than default'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 != ($returnCode = $this->login($output))) {
            return $returnCode;
        }

        $configuration = $this->getHelperSet()->get('configuration');
        $protocol = $input->getOption('protocol');
        $apiBaseUri = $input->getOption('api-base-uri');
        $clientId = $input->getOption('client-id');
        $clientSecret = $input->getOption('client-secret');

        if (($clientId && !$clientSecret) || (!$clientId && $clientSecret)) {
            throw new \RuntimeException('Options --client-id & --client-secret have to be set together');
        } elseif (!in_array($protocol, SignedAuthenticationUrlFactory::getAvailableProtocols())) {
            throw new \RuntimeException('Invalid value for protocol');
        }

        if ($apiBaseUri) {
            $configuration['api_base_uri'] = $apiBaseUri;
        }

        if ($clientId && $clientSecret) {
            $configuration['client_id'] = $clientId;
            $configuration['client_secret'] = $clientSecret;
        }

        $signedUrl = SignedAuthenticationUrlFactory::create(
            $configuration,
            [
                'userRid' => $input->getOption('userRid'),
                'email' => $input->getArgument('email'),
                'firstname' => $input->getOption('firstname'),
                'lastname' => $input->getOption('lastname'),
                'username' => $input->getOption('username'),
                'organizationRid' => $input->getOption('organizationRid'),
                'organizationName' => $input->getOption('organizationName'),
            ],
            $protocol
        );

        $output->writeln(sprintf('<comment>%s</comment>', $signedUrl));
    }
}
