<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Creads\Partners\Client;
use Creads\Partners\BearerAccessToken;

class UploadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('upload')
            ->setDescription('Upload a file resource')
            ->addArgument(
              'filepath',
              InputArgument::REQUIRED,
              'real file path of the file to upload'
            )
            ->addArgument(
              'filename',
              InputArgument::OPTIONAL,
              'Desired final filename for the file to upload'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $json = $this->getHelperSet()->get('json');
        $configuration = $this->getHelperSet()->get('configuration');

        $realFilePath = $input->getArgument('filepath');
        $filename = $input->getArgument('filename');

        if (0 != $returnCode = $this->login($output)) {
            return $returnCode;
        }

        //@todo create a service
        $client = new Client(new BearerAccessToken($configuration['access_token']), [
            'base_uri' => $configuration['api_base_uri'],
            'http_errors' => false,
        ]);

        $response = $client->postFile($realFilePath, $filename);

        $error = ($response->getStatusCode() >= 400);

        if ($error) {
            $reason = '<error>'.$response->getStatusCode().' '.$response->getReasonPhrase().'</error>';
            //for the output to stdderr to no break the output
            $output->getErrorOutput()->writeln($reason);
        }

        $body = (string) $response->getBody();

        $output->writeln(sprintf('Location: %s', $response->getHeader('Location')[0]));
        $output->writeln($body);

        return ($error) ? $response->getStatusCode() : 0;
    }
}
