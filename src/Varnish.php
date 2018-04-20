<?php

namespace Spatie\Varnish;

use Symfony\Component\Process\Process;

class Varnish
{
    /*
     * Known exec types
     */
    const EXEC_SOCKET = 'socket';
    const EXEC_COMMAND = 'command';

    /**
     * @param string|array $host
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function flush($host = null)
    {
        $config = config('varnish');

        $host = $this->getHosts($host);
        $expr = $this->generateBanExpr($host);

        // Default to execution_type command when the config parameter is not set
        switch ($config['execution_type'] ?? self::EXEC_COMMAND) {
            case self::EXEC_SOCKET:
                return $this->executeSocketCommand($expr);
                break;
            case self::EXEC_COMMAND:
                $command = $this->generateBanCommand($expr);

                return $this->executeCommand($command);
                break;
            default:
                throw new \Exception(sprintf(
                    'Unknown execution type: %s', $config['execution_type']
                ));
        }
    }

    /**
     * @param array|string $host
     *
     * @return array
     */
    protected function getHosts($host = null): array
    {
        $host = $host ?? config('varnish.host');

        if (! is_array($host)) {
            $host = [$host];
        }

        return $host;
    }

    /**
     * @param string $expr
     *
     * @return string
     */
    public function generateBanCommand($expr = ''): string
    {
        $config = config('varnish');

        return "sudo varnishadm -S {$config['administrative_secret_file']} -T 127.0.0.1:{$config['administrative_port']} '{$expr}'";
    }

    /**
     * @param array $hosts
     *
     * @return string
     */
    public function generateBanExpr(array $hosts): string
    {
        if (! is_array($hosts)) {
            $hosts = [$hosts];
        }

        $hostsRegex = collect($hosts)
            ->map(function (string $host) {
                return "(^{$host}$)";
            })
            ->implode('|');

        return sprintf('ban req.http.host ~ %s', $hostsRegex);
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        $config = config('varnish');
        if (! $secret = $config['administrative_secret']) {
            $secret = '';
            if (file_exists($config['administrative_secret_file'])) {
                $secret = trim(file_get_contents($config['administrative_secret_file']));
            }
        }

        return $secret;
    }

    /**
     * @param string $command
     *
     * @return bool
     *
     * @throws \Exception When the command fails
     */
    protected function executeCommand(string $command): bool
    {
        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * @param string $command
     *
     * @return bool
     *
     * @throws \Exception When connection to socket or command failed
     */
    protected function executeSocketCommand(string $command): bool
    {
        $config = config('varnish');
        $socket = new VarnishSocket();

        try {
            $socket->connect(
                $config['administrative_host'],
                $config['administrative_port'],
                $this->getSecret()
            );
            $socket->command($command);
        } finally {
            $socket->quit();
        }

        return true;
    }
}
