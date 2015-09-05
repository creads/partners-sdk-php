<?php

namespace Creads\Partners\Console;

class Configuration implements \ArrayAccess
{
    protected $path;
    protected $parameters;

    public function __construct()
    {
        //build the configuration file path
        if (isset($_SERVER['HOME'])) {
            $this->path = $_SERVER['HOME'];
        } else {
            $output->writeln('CLI failed to locate your home directory. Configuration file will be saved in current directory as `.partners.json`.');
            $this->path = getcwd();
        }
        $this->path = $this->path.'/.partners.json';
    }

    /**
     * Doest the configuration exist
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Load the configuration
     * @return self
     */
    public function load()
    {
        if ($this->exists()) {
            $this->parameters = json_decode(file_get_contents($this->path), true);
            if (!$this->parameters) {
                throw new \Exception(sprintf('Failed to load configuration file. Please run the command again with "--reset" option. If the problem persists, remove manually the file "%s".', $path));
            }
        }

        return $this;
    }

    /**
     * Save the configuration
     * @return self
     */
    public function store()
    {
        $success = file_put_contents($this->path, json_encode($this->parameters, (version_compare(PHP_VERSION, '5.4.0') >= 0)?JSON_PRETTY_PRINT:0));
        if (false === $success) {
            throw new \Exception(sprintf('Failed to store configuration file "%s".', $this->path));
        }

        return $this;
    }

    public function offsetExists($name)
    {
        return (null !== $this->parameters && array_key_exists($name, $this->parameters));
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
        unset($this->paramaters[$name]);
    }
}