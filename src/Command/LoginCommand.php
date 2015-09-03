<?php

namespace Creads\Partners\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoginCommand extends Command
{
	protected function configure()
    {
        $this
            ->setName('login')
            ->setDescription('Log onto the API')
            ->addOption(
               'reset',
               null,
               InputOption::VALUE_NONE,
               'Reset the client credentials'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        //build the configuration file path
        if (isset($_SERVER['HOME'])) {
            $path = $_SERVER['HOME'];
        } else {
            $output->writeln('CLI failed to locate your home directory. Configuration file will be saved in current directory as `.partners.json`.');
            $path = getcwd();
        }
        $path = $path.'/.partners.json';

        if (!file_exists($path) || $input->getOption('reset', false)) {

            //if there is no configuration file or if user passed --reset option
            $config = [];
            if (!$config['client_id'] = $dialog->ask(
                $output,
                '<question>Your client ID:</question> ',
                false
            )) {
                return 1;
            }

            if (!$config['client_secret'] = $dialog->ask(
                $output,
                '<question>Your client secret:</question> ',
                false
            )) {
                return 1;
            }
        } else {
            //load the configuration
            $config = json_decode(file_get_contents($path), true);
            if (!$config) {
                throw new \Exception(sprintf('Failed to load configuration file. Please run the command again with "--reset" option. If the problem persists, remove manually the file "%s".', $path));
            }
        }

        $baseUri = 'https://connect-preprod.creads-partners.com';
        $client = new \GuzzleHttp\Client(['base_uri' => $baseUri]);
        try {
            $response = $client->post('/oauth2/token', [
                'auth' => [$config['client_id'], $config['client_secret']],
                'form_params' => [
                    'grant_type' => 'password', //@todo change for client_credentials
                    'scope' => 'base',
                    'username' => 'd.pitard@creads.org', //@todo remove
                    'password' => 'br152qT3',
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $data = json_decode($e->getResponse()->getBody(), true);
            $message = isset($data['error_description'])?$data['error_description']:($e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase());
            throw new \Exception($message);
        }

        if (!($data = json_decode($response->getBody(), true))) {
            throw new \Exception('Failed to decode API response', $path);
        }

        $config['access_token'] = $data['access_token'];
        $config['expires_at'] = isset($data['expires_in'])?(time()+$data['expires_in']):null;
        $config['refresh_token'] = isset($data['refresh_token'])?$data['refresh_token']:null;

        //save the config
        if (false === file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT))) {
            throw new \Exception(sprintf('Failed to store configuration file "%s". Can not continue.', $path));
        }

        $output->writeln('OK');
    }
}