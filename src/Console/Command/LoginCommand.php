<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Creads\Partners\Console\Configuration;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class LoginCommand extends Command
{
    /**
     * @var Creads\Partners\Console\Configuration
     */
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
                'save-password',
                null,
                InputOption::VALUE_NONE,
                'Save your password locally'
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED,
                'Use a given OAuth2 grant type (default: password)'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //for the output to stdderr to no break the output
        $output = $output->getErrorOutput();

        $style = new OutputFormatterStyle('black', 'white');
        $output->getFormatter()->setStyle('fire', $style);

        $dialog = $this->getHelperSet()->get('dialog');
        $formatter = $this->getHelperSet()->get('formatter');
        $reset = $input->getOption('reset', false);
        $type = $input->getOption('grant-type', null);
        $savePassword = $input->getOption('save-password', false);

        $this->configuration->load();

        if (!$this->configuration->exists()) {
            $this->configuration['grant_type'] = 'password';
        } else if ($type && $this->configuration['grant_type'] !== $type) {
            //if grant type was forced to change
            $this->configuration['grant_type'] = $type;

            //clear password for security concerns
            unset($this->configuration['password']);
        }

        if (!in_array($this->configuration['grant_type'], ['client_credentials', 'password'])) {
            throw new \InvalidArgumentException(sprintf('Unsupported grant type "%s"', $this->configuration['grant_type']));
        }

        if ('password' === $this->configuration['grant_type']
            && !isset($this->configuration['password'])
            && !$savePassword
        ) {
            //if we are using password grant type
            //and password was not saved
            //and user did not ask to save it
            $output->writeln($formatter->formatBlock([
                'Avoid to type your password each time, using "client_credentials" grant type:',
                '',
                sprintf('    %s login --grant-type=client_credentials', $_SERVER['argv'][0]),
                '',
                'Or save your password locally (not recommended):',
                '',
                sprintf('    %s login --save-password', $_SERVER['argv'][0]),
            ], 'fire', true));
        }

        if (!$this->configuration->exists() || $reset) {

            $output->writeln("Please provide your credentials (won't be asked next time).");

            unset($this->configuration['password']);

            $this->configuration['client_id'] = $this->getConfigValue($output, 'Client ID', isset($this->configuration['client_id'])?$this->configuration['client_id']:null, $reset);

            $this->configuration['client_secret'] = $this->getConfigValue($output, 'Client Secret', isset($this->configuration['client_secret'])?$this->configuration['client_secret']:null, $reset);

            if ('password' === $this->configuration['grant_type']) {
                $this->configuration['username'] = $this->getConfigValue($output, 'Username', isset($this->configuration['username'])?$this->configuration['username']:null, $reset);
            }
        }

        //build form params
        $params = [
            'grant_type' => $this->configuration['grant_type'],
            'scope' => 'base'
        ];

        if ('password' === $this->configuration['grant_type']) {
            if (!isset($this->configuration['password'])) {
                if (!$password = $dialog->askHiddenResponse(
                    $output,
                    '<question>Password</question>: ',
                    false
                )) {
                    return 1;
                }

                if ($savePassword) {
                    $this->configuration['password'] = $password;
                }
            } else {
                $password = $this->configuration['password'];
            }

            $params = array_merge($params, [
                'username' => $this->configuration['username'],
                'password' => $password,
            ]);
        }

        //@todo build a service to do that
        $client = new \GuzzleHttp\Client(['base_uri' => $this->configuration['connect_base_uri']]);
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
            throw new \Exception('Failed to decode API response');
        }

        $this->configuration['access_token'] = $data['access_token'];
        $this->configuration['expires_at'] = isset($data['expires_in'])?(time()+$data['expires_in']):null;
        $this->configuration['refresh_token'] = isset($data['refresh_token'])?$data['refresh_token']:null;

        $this->configuration->store();

        $output->writeln('Login: <comment>OK</comment>');
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