<?php

namespace Creads\Partners;

use GuzzleHttp\Client as GuzzleClient;

class Client extends GuzzleClient
{
    /**
     * The API format to send/recieve : json, xml...
     *
     * @var string
     */
    protected $format = 'json';

    /**
     * Constructor
     * {@inheritdoc}
     */
    public function __construct(AuthenticationInterface $authentication = null, array $config = [])
    {
        $config = array_merge($this->getDefaultClientConfig(), $config);

        if ($authentication) {
            $config = array_merge($config, $authentication->getConfig());
        }
        if (!empty($config['format']) && in_array($config['format'], ['json'])) {
            $this->format = $config['format'];
        }
        parent::__construct($config);
    }

    /**
     * Get default configuration to apply the client.
     *
     * @return array
     */
    protected function getDefaultClientConfig()
    {
        return [
            'base_uri' => 'https://api.creads-partners.com/v1/',
        ];
    }

    public function put($uri, $body = [], $options = [])
    {
        $requestBody = array_merge($options, [$this->format => $body]);

        return parent::request('PUT', $uri, $requestBody);
    }

    public function post($uri, $body = [], $options = [])
    {
        $requestBody = array_merge($options, [$this->format => $body]);

        return parent::request('POST', $uri, $requestBody);
    }

    public function get($uri)
    {
        $response = parent::get($uri);
        switch ($this->format) {
            case 'json':
            default:
                $parsedResponse = json_decode($response->getBody(), true);
                break;
        }

        return $parsedResponse;
    }

    public function postFile($realFilePath, $uploadFileName = null)
    {
        if (!$uploadFileName) {
            // No specified filename, use the uploaded one
            $uploadFileName = pathinfo($realFilePath)['basename'];
        }
        $me = $this->get('me');
        $uploadUrl = $me['upload_form']['form_attributes']['action'];
        $uploadUrl = str_replace('${filename}', $uploadFileName, $uploadUrl);

        $multipartBody = [];

        // Add Amazon specific data needed for authentication (order matters)
        foreach ($me['upload_form']['form_inputs'] as $key => $value) {
            if ($key === 'key') {
                $value = str_replace('${filename}', $uploadFileName, $value);
            }
            $multipartBody[] = [
                'name' => $key,
                'contents' => $value,
            ];
        }

        // Build the multipart file upload (order matters)
        $multipartBody[] = [
            'name' => 'file',
            'contents' => fopen($realFilePath, 'rb'),
            'filename' => $uploadFileName,
        ];
        $multipartBody[] = [
            'name' => 'filepath',
            'contents' => '/'.$uploadFileName,
        ];

        return $this->request(
            'POST',
            $uploadUrl,
            [
                'headers' => [
                    'Authorization' => null,
                ],
                'multipart' => $multipartBody,
            ]
        );
    }

    // /**
    //  * Returns options to apply every request
    //  * @return array
    //  */
    // protected function getRequestOptions()
    // {
    //     return [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $this->getConfig('access_token')
    //         ]
    //     ];
    // }
}
