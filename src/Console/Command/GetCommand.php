<?php

namespace Creads\Partners\Console\Command;

use Creads\Partners\BearerAccessToken;
use Creads\Partners\ClientFactory;
use Flow\JSONPath\JSONPath;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetCommand extends Command
{
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uri = ltrim($input->getArgument('URI'), '/');
        $include = $input->getOption('include', false);
        $filter = $input->getOption('filter', false);

        if (0 != ($returnCode = $this->login($output))) {
            return $returnCode;
        }

        $client = ClientFactory::create($this->getHelperSet()->get('configuration'));

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

        if ($include) {
            $reason = $response->getStatusCode().' '.$response->getReasonPhrase();
            $output->writeln($reason);
        } elseif ($error) {
            $reason = '<error>'.$response->getStatusCode().' '.$response->getReasonPhrase().'</error>';
            //for the output to stdderr to no break the output
            $output->getErrorOutput()->writeln($reason);
        }

        if ($include) {
            foreach ($response->getHeaders() as $name => $value) {
                $output->writeln($name.': '.$value[0]);
            }
        }

        $body = (string) $response->getBody();

        //@toto test content-type

        $body = json_decode($body, true);
        if (false === $body) {
            $output->getErrorOutput()->writeln('<error>Malformed JSON body</error>');
        } else {

            $json = $this->getHelperSet()->get('json');
            if ($filter && !$error) {
                $body = $json->format((new JSONPath($body))->find($filter));
            } else {
                $body = $json->format($body);
            }
        }

        $output->writeln($body);

        return ($error) ? $response->getStatusCode() : 0;
    }
}
