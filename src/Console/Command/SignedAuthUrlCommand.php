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
                'organizationName',
                InputArgument::REQUIRED
            )->addArgument(
                'email',
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
                SignedAuthenticationUrlFactory::RFC1_SIGNATURE_PROTOCOL
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
        $api_base_uri = $input->getOption('api-base-uri');
        $client_id = $input->getOption('client-id');
        $client_secret = $input->getOption('client-secret');

        if (($client_id && !$client_secret) || (!$client_id && $client_secret)) {
            throw new \RuntimeException('Options --client-id & --client-secret have to be set together');
        } elseif (!in_array($protocol, SignedAuthenticationUrlFactory::getAvailableProtocols())) {
            throw new \RuntimeException('Invalid value for protocol');
        }

        if ($api_base_uri) {
            $configuration['api_base_uri'] = $api_base_uri;
        }

        if ($client_id && $client_secret) {
            $configuration['client_id'] = $client_id;
            $configuration['client_secret'] = $client_secret;
        }

        $signedUrl = SignedAuthenticationUrlFactory::create(
            $configuration,
            [
                'organizationRid' => $input->getArgument('organizationRid'),
                'organizationName' => $input->getArgument('organizationName'),
                'email' => $input->getArgument('email'),
                'firstname' => $input->getArgument('firstname'),
                'lastname' => $input->getArgument('lastname'),
            ],
            $protocol
        );

        $output->writeln(sprintf('<comment>%s</comment>', $signedUrl));
    }
}
