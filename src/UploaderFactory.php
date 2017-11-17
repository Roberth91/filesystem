<?php

namespace Filesystem\FileUpload;

use Filesystem\FileUpload\Configuration\Configuration;
use Filesystem\FileUpload\Driver\FtpDriver;
use Filesystem\FileUpload\Driver\LocalDriver;
use Filesystem\FileUpload\Driver\S3Driver;
use Filesystem\FileUpload\Driver\SftpDriver;

/**
 * Class UploaderFactory
 */
class UploaderFactory
{
    /**
     * @param $driver
     * @param Configuration $config
     * @return Uploader
     * @throws \InvalidArgumentException
     */
    public function getUploader($driver, Configuration $config)
    {
        switch ($driver)
        {
            case 's3':
                $client = [
                    'bucket'         => $config->getHost(),
                    'prefix'         => $config->getRemoteDirectory(),
                    'key'            => $config->getUsername(),
                    'secret'         => $config->getPassword(),
                    'region'         => $config->getPort() ?: 'us-east-1',
                    'version'        => 'latest',
                    'timeout'        => $config->getReadWriteTimeout() ?: 1200,
                    'connect_timeout'=> $config->getTimeout() ?: 90,
                    'encrypt'        => $config->getEncryption(),
                ];
                $adapter = new S3Driver($client);
                break;

            case 'ftp':
            case 'ftps':
                $adapter = new FtpDriver([
                    'host'      => $config->getHost(),
                    'port'      => $config->getPort() ?: 21,
                    'username'  => $config->getUsername(),
                    'password'  => $config->getPassword(),
                    'root'      => $config->getRemoteDirectory(),
                    'ssl'       => $driver === 'ftps',
                ]);
                break;

            case 'sftp':
                $adapter = new SftpDriver([
                    'host'      => $config->getHost(),
                    'port'      => $config->getPort() ?: 22,
                    'username'  => $config->getUsername(),
                    'password'  => $config->getPassword(),
                    'root'      => $config->getRemoteDirectory(),
                    'privateKey'=> $config->getSshKey(),
                    'timeout'   => $config->getTimeout() ?: 90
                ]);
                break;

            case 'local':
                $adapter = new LocalDriver(['root' => $config->getRemoteDirectory()]);
                break;

            default:
                throw new \InvalidArgumentException("Invalid driver: $driver");
        }

        $uploader = new Uploader($adapter);

        return $uploader;
    }
}
