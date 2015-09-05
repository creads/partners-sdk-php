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
        $uri = ltrim($input->getArgument('URI'), '/');

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
            'base_uri' => $this->configuration['api_base_uri']
        ]);

        $response = $client->get($uri);
        // try {
        //     $response = $client->get($uri);
        // } catch (\GuzzleHttp\Exception\ClientException $e) {
        //     var_dump($e->getRequest());
        //     throw $e;
        // }
        //

        // if (!$input->isInteractive()) {
        //     $data = (string)$response->getBody();
        // } else {
            // $data = json_encode(json_decode((string)$response->getBody()), JSON_PRETTY_PRINT);
        // }
        //

        $json = $this->getHelperSet()->get('json');

        $output->writeln($json->format((string)$response->getBody()));
    }
}