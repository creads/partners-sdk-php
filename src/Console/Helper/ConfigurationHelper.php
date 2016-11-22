<?php

namespace Creads\Partners\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Creads\Partners\Console\Configuration;

class ConfigurationHelper extends Helper implements \ArrayAccess
{
    protected $path;

    protected $parameters = [];

    public function __construct()
    {
        //build the configuration file path
        if (isset($_SERVER['HOME'])) {
            $this->path = $_SERVER['HOME'];
        } else {
            $this->path = getcwd();
        }
        $this->path = $this->path.'/.partners.json';

        $this->load();
    }

    protected function load()
    {
        if (!$this->exists()) {
            $this->parameters = $this->getDefaultParameters();
        } else {
            $this->parameters = json_decode(file_get_contents($this->path), true);
            if (!$this->parameters) {
                throw new \Exception(sprintf('Failed to load configuration file. Please run the command again with "--reset" option. If the problem persists, remove manually the file "%s".', $path));
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'configuration';
    }

    protected function getDefaultParameters()
    {
        return [
            'connect_base_uri' => 'https://connect-preprod.creads-partners.com/',
            'api_base_uri' => 'https://api-preprod.creads-partners.com/v1/',
        ];
    }

    /**
     * Doest the configuration exist.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Save the configuration.
     *
     * @return self
     */
    public function store()
    {
        $success = file_put_contents($this->path, json_encode($this->parameters, (version_compare(PHP_VERSION, '5.4.0') >= 0) ? JSON_PRETTY_PRINT : 0));
        if (false === $success) {
            throw new \Exception(sprintf('Failed to store configuration file "%s".', $this->path));
        }

        return $this;
    }

    public function offsetExists($name)
    {
        return null !== $this->parameters && array_key_exists($name, $this->parameters);
    }

    public function offsetGet($name)
    {
        if (!$this->offsetExists($name)) {
            throw new \Exception(sprintf('Configuration parameter "%s" is undefined', $name));
        }

        return $this->parameters[$name];
    }

    public function offsetSet($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function offsetUnset($name)
    {
        unset($this->parameters[$name]);
    }
}
