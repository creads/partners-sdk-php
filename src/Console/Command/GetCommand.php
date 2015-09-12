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
use Flow\JSONPath\JSONPath;

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
              'filter',
              'f',
              InputOption::VALUE_REQUIRED,
              'Filter results using JSON path (http://goessner.net/articles/JsonPath)'
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
        $filter = $input->getOption('filter', false);

        //@todo create a command helper, will be used on several commands
        //run login if configuration does not exists of if the access token is expired
        if (!$this->configuration->exists()
            || !isset($this->configuration['access_token'])
            || (isset($this->configuration['expires_at']) && time() > $this->configuration['expires_at'])
        ) {
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
        //

        $error = ($response->getStatusCode() >= 400);

        if ($include || $error) {
            $reason = $response->getStatusCode().' '.$response->getReasonPhrase();
            if ($error) {
                $reason = '<error>'.$reason.'</error>';
            }
            $output->writeln($reason);
        }

        if ($include) {
            foreach($response->getHeaders() as $name => $value) {
                $output->writeln($name.': '.$value[0]);
            }
        }

        $body = (string)$response->getBody();

        //@toto test content-type

        $body = json_decode($body, true);
        if (false === $body) {
            $output->writeln('<error>Malformed JSON body</error>');
        } else {
            if ($filter && !$error) {
                $body = $json->format((new JSONPath($body))->find($filter));
            } else {
                $body = $json->format($body);
            }
        }

        $output->writeln($body);

        return ($error)?$response->getStatusCode():0;
    }
}