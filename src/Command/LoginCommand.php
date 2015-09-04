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
               'r',
               InputOption::VALUE_NONE,
               'Reset credentials'
            )
            ->addOption(
                '--no-password',
                null,
                InputOption::VALUE_NONE,
                'User credentials needed'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $reset = $input->getOption('reset', false);
        $noPassword = $input->getOption('no-password', false);

        //build the configuration file path
        if (isset($_SERVER['HOME'])) {
            $path = $_SERVER['HOME'];
        } else {
            $output->writeln('CLI failed to locate your home directory. Configuration file will be saved in current directory as `.partners.json`.');
            $path = getcwd();
        }
        $path = $path.'/.partners.json';

        if (file_exists($path)) {
            //load the configuration
            $config = json_decode(file_get_contents($path), true);
            if (!$config) {
                throw new \Exception(sprintf('Failed to load configuration file. Please run the command again with "--reset" option. If the problem persists, remove manually the file "%s".', $path));
            }
        }

        if (!file_exists($path) || $reset) {

            if (!$clientId = $dialog->ask(
                $output,
                sprintf('<question>Client ID</question>%s: ', $reset?(' [<comment>'.$config['client_id'].'</comment>]'):''),
                false
            )) {
                if ($reset) {
                    $clientId = $config['client_id'];
                } else {
                    throw new \InvalidArgumentException('Client ID is required');
                }
            }
            $config['client_id'] = $clientId;

            if (!$clientSecret = $dialog->ask(
                $output,
                sprintf('<question>Client Secret</question>%s: ', $reset?(' [<comment>'.$config['client_secret'].'</comment>]'):''),
                false
            )) {
                if ($reset) {
                    $clientSecret = $config['client_secret'];
                } else {
                    throw new \InvalidArgumentException('Client Secret is required');
                }
            }
            $config['client_secret'] = $clientSecret;

            if (!$noPassword) {
                if (!$username = $dialog->ask(
                    $output,
                    sprintf('<question>Username</question>%s: ', ($reset && isset($config['username']))?(' [<comment>'.$config['username'].'</comment>]'):''),
                    false
                )) {
                    if ($reset && isset($config['username'])) {
                        $username = $config['username'];
                    } else {
                        throw new \InvalidArgumentException('Username is required');
                    }
                }
                $config['username'] = $username;

                $config['grant_type'] = 'password';
            }
        }

        //even if we are not reseting credentials, we can switch to "no password" mode
        if ($noPassword) {
            $config['grant_type'] = 'client_credentials';
        }

        $params = [
            'grant_type' => $config['grant_type'],
            'scope' => 'base'
        ];

        if ('password' === $config['grant_type']) {

            if (!$password = $dialog->askHiddenResponse(
                $output,
                '<question>Password</question>: ',
                false
            )) {
                return 1;
            }

            $params = array_merge($params,[
                'username' => $config['username'],
                'password' => $password,
            ]);
        }

        $baseUri = 'https://connect-preprod.creads-partners.com';
        $client = new \GuzzleHttp\Client(['base_uri' => $baseUri]);
        try {
            $response = $client->post('/oauth2/token', [
                'auth' => [$config['client_id'], $config['client_secret']],
                'form_params' => $params
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