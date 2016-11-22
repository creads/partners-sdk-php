<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
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

        $realFilePath = $input->getArgument('filepath');
        $filename = $input->getArgument('filename');

        //@todo create a command helper, will be used on several commands
        if (!$this->configuration->exists()
            || !isset($this->configuration['access_token'])
            || (isset($this->configuration['expires_at']) && time() > $this->configuration['expires_at'])
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
            if ($returnCode != 0) {
                return $returnCode;
            }
        }

        //@todo create a service
        $client = new Client(new BearerAccessToken($this->configuration['access_token']), [
            'base_uri' => $this->configuration['api_base_uri'],
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
