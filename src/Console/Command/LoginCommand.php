<?php

namespace Creads\Partners\Console\Command;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoginCommand extends Command
{
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
            );
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

        $questionHelper = $this->getHelperSet()->get('question');
        $formatter = $this->getHelperSet()->get('formatter');

        $reset = $input->getOption('reset', false);
        $type = $input->getOption('grant-type', null);
        $savePassword = $input->getOption('save-password', false);

        $configuration = $this->getHelperSet()->get('configuration');
        if (!$configuration->exists()) {
            $configuration['grant_type'] = $type ? $type : 'password';
        } elseif ($type && $configuration['grant_type'] !== $type) {
            //if grant type was forced to change
            $configuration['grant_type'] = $type;

            //clear password for security concerns
            unset($configuration['password']);
        }

        if (!in_array($configuration['grant_type'], ['client_credentials', 'password'])) {
            throw new \InvalidArgumentException(sprintf('Unsupported grant type "%s"', $configuration['grant_type']));
        }

        if ('password' === $configuration['grant_type']
            && !isset($configuration['password'])
            && !$savePassword
        ) {
            //if we are using password grant type
            //and password was not saved
            //and user did not ask to save it
            $output->writeln($formatter->formatBlock([
                'Avoid to type your password each time token expires, using "client_credentials" grant type:',
                '',
                sprintf('    %s login --grant-type=client_credentials', $_SERVER['argv'][0]),
                '',
                'Or save your password locally (not recommended):',
                '',
                sprintf('    %s login --save-password', $_SERVER['argv'][0]),
            ], 'fire', true));
        }

        if (!$configuration->exists() || $reset) {
            $output->writeln("Please provide your credentials (won't be asked next time).");

            unset($configuration['password']);

            $configuration['client_id'] = $this->getConfigValue($input, $output, 'Client ID', isset($configuration['client_id']) ? $configuration['client_id'] : null, $reset);

            $configuration['client_secret'] = $this->getConfigValue($input, $output, 'Client Secret', isset($configuration['client_secret']) ? $configuration['client_secret'] : null, $reset);

            if ('password' === $configuration['grant_type']) {
                $configuration['username'] = $this->getConfigValue($input, $output, 'Username', isset($configuration['username']) ? $configuration['username'] : null, $reset);
            }
        }

        //build form params
        $params = [
            'grant_type' => $configuration['grant_type'],
            'scope'      => 'base',
        ];
        if ('password' === $configuration['grant_type']) {
            if (!isset($configuration['password'])) {
                $question = new Question('<question>Password</question>: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                if (!$password = $questionHelper->ask(
                    $input,
                    $output,
                    $question
                )) {
                    return 1;
                }

                if ($savePassword) {
                    $configuration['password'] = $password;
                }
            } else {
                $password = $configuration['password'];
            }

            $params = array_merge($params, [
                'username' => $configuration['username'],
                'password' => $password,
            ]);
        }

        //@todo build a service to do that
        $client = new \GuzzleHttp\Client(['base_uri' => $configuration['connect_base_uri']]);
        try {
            $response = $client->post('/oauth2/token', [
                'auth'        => [$configuration['client_id'], $configuration['client_secret']],
                'form_params' => $params,
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $data = json_decode($e->getResponse()->getBody(), true);
            $message = isset($data['error_description']) ? $data['error_description'] : ($e->getResponse()->getStatusCode().' '.$e->getResponse()->getReasonPhrase());
            throw new \Exception($message);
        }

        if (!($data = json_decode($response->getBody(), true))) {
            throw new \Exception('Failed to decode API response');
        }

        $configuration['access_token'] = $data['access_token'];
        $configuration['expires_at'] = isset($data['expires_in']) ? (time() + $data['expires_in']) : null;
        $configuration['refresh_token'] = isset($data['refresh_token']) ? $data['refresh_token'] : null;

        $configuration->store();

        $output->writeln('Login: <comment>OK</comment>');
    }

    /**
     * @param OutputInterface $output
     * @param string          $label
     * @param string|null     $previous
     * @param bool            $reset
     */
    protected function getConfigValue($input, $output, $label, $previous, $reset)
    {
        $questionHelper = $this->getHelperSet()->get('question');
        $question = new Question(sprintf('<question>%s</question>%s: ', $label, $previous ? (' [<comment>'.$previous.'</comment>]') : ''));
        if (!$value = $questionHelper->ask(
            $input,
            $output,
            $question
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
