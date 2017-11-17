<?php

namespace Filesystem\FileUpload\Configuration;

/**
 * Class Configuration
 * @package Filesystem\FileUpload\Configuration
 */
class Configuration
{
    private $host;

    private $port;

    private $username;

    private $password;

    private $remoteDirectory;

    private $sshKey;

    private $timeout;

    private $encrypt;

    private $read_write_timeout;

    public function __construct($host, $username, $password, $remoteDirectory, $sshKey = null, $encrypt = false)
    {
        list($this->host, $this->port) = $this->parseHost($host);

        $this->username = $username;

        $this->password = $password;

        $this->remoteDirectory = $remoteDirectory;

        $this->sshKey = $sshKey;

        $this->encrypt = $encrypt;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSshKey()
    {
        return $this->sshKey;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getRemoteDirectory()
    {
        return $this->remoteDirectory;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function getReadWriteTimeout()
    {
        return $this->read_write_timeout;
    }

    public function getEncryption()
    {
        return $this->encrypt;
    }

    public function setTimeout($timeout)
    {
        if(!(is_int($timeout) && $timeout > 0))
        {
            throw new \Exception("Invalid timeout: " . $timeout);
        }

        $this->timeout = $timeout;
    }

    public function setReadWriteTimeout($timeout)
    {
        if(!(is_int($timeout) && $timeout > 0))
        {
            throw new \Exception("Invalid read write timeout: " . $timeout);
        }

        $this->read_write_timeout = $timeout;
    }

    private function parseHost($host)
    {
        $parts = explode(':', $host, 2);

        if(!isset($parts[1]))
        {
            $parts[1] = null;
        }

        return $parts;
    }
}
