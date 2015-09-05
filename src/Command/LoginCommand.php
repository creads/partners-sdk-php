<?php

namespace Creads\Partners\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Creads\Partners\Configuration;

class LoginCommand extends Command
{
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $reset = $input->getOption('reset', false);
        $noPassword = $input->getOption('no-password', false);

        $this->configuration->load();

        if (!$this->configuration->exists() || $reset) {

            $this->configuration['base_uri'] = 'https://connect-preprod.creads-partners.com';

            $this->configuration['client_id'] = $this->getConfigValue($output, 'Client ID', isset($this->configuration['client_id'])?$this->configuration['client_id']:null, $reset);

            $this->configuration['client_secret'] = $this->getConfigValue($output, 'Client Secret', isset($this->configuration['client_secret'])?$this->configuration['client_secret']:null, $reset);

            if (!$noPassword) {
                $this->configuration['username'] = $this->getConfigValue($output, 'Username', isset($this->configuration['username'])?$this->configuration['username']:null, $reset);
                $this->configuration['grant_type'] = 'password';
            }
        }

        //even if we are not reseting credentials, we can switch to "no password" mode
        if ($noPassword) {
            $this->configuration['grant_type'] = 'client_credentials';
        }

        //build form params
        $params = [
            'grant_type' => $this->configuration['grant_type'],
            'scope' => 'base'
        ];

        if ('password' === $this->configuration['grant_type']) {

            if (!$password = $dialog->askHiddenResponse(
                $output,
                '<question>Password</question>: ',
                false
            )) {
                return 1;
            }

            $params = array_merge($params,[
                'username' => $this->configuration['username'],
                'password' => $password,
            ]);
        }

        $client = new \GuzzleHttp\Client(['base_uri' => $this->configuration['base_uri']]);
        try {
            $response = $client->post('/oauth2/token', [
                'auth' => [$this->configuration['client_id'], $this->configuration['client_secret']],
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

        $this->configuration['access_token'] = $data['access_token'];
        $this->configuration['expires_at'] = isset($data['expires_in'])?(time()+$data['expires_in']):null;
        $this->configuration['refresh_token'] = isset($data['refresh_token'])?$data['refresh_token']:null;

        $this->configuration->store();

        $output->writeln('OK');
    }

    /**
     *
     * @param OutputInterface   $output
     * @param string            $label
     * @param string|null       $previous
     * @param bool              $reset
     */
    protected function getConfigValue($output, $label, $previous, $reset)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$value = $dialog->ask(
            $output,
            sprintf('<question>%s</question>%s: ', $label, $previous?(' [<comment>'.$previous.'</comment>]'):''),
            false
        )) {
            if ($reset) {
                $value = $previous;
            } else {
                throw new \InvalidArgumentException(sprintf('%s is required', $label));
            }
        }

        return $value;
    }
}