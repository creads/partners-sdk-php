<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Creads\Partners\Console\Configuration;
use Creads\Partners\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

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
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_NONE,
                'Include the HTTP-header in the output'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $json = $this->getHelperSet()->get('json');
        $this->configuration->load();
        $uri = ltrim($input->getArgument('URI'), '/');
        $include = $input->getOption('include', false);

        //@todo create a command helper, will be used on several commands
        //run login if configuration does not exists of if the access token is expired
        if (!isset($this->configuration['expires_at']) || time() > $this->configuration['expires_at']) {
            $command = $this->getApplication()->find('login');
            $arguments = array(
                'command' => 'login'
            );
            $input2 = new ArrayInput($arguments);
            $returnCode = $command->run($input2, $output);
            if ($returnCode != 0) {
                return $returnCode;
            }
        }

        //@todo create a service
        $client = new Client([
            'access_token' => $this->configuration['access_token'],
            'base_uri' => $this->configuration['api_base_uri'],
            'http_errors' => false
        ]);

        $request = new Request('GET', $uri);
        $response = $client->send($request);

        //@todo use handler
        // if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
        //     foreach($request->getHeaders() as $name => $value) {
        //         $output->writeln($name.': '.$value[0]);
        //     }
        // }

        if ($include || $response->getStatusCode() >= 400) {
            $reason = $response->getStatusCode().' '.$response->getReasonPhrase();
            if ($response->getStatusCode() >= 400) {
                $reason = '<error>'.$reason.'</error>';
            }
            $output->writeln($reason);
        }

        if ($include) {
            foreach($response->getHeaders() as $name => $value) {
                $output->writeln($name.': '.$value[0]);
            }
            // $output->writeln('');
        }

        $output->writeln($json->format((string)$response->getBody()));

        return ($response->getStatusCode() >= 400)?$response->getStatusCode():0;
    }
}