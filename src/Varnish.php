<?php

namespace Spatie\Varnish;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Varnish
{
    /**
     * @param string|array $host
     *
     * @return \Symfony\Component\Process\Process
     */
    public function flush($host = null)
    {
        $host = $this->getHosts($host);

        $command = $this->generateBanCommand($host);

        return $this->executeCommand($command);
    }

    /**
     * @param array|string $host
     *
     * @return array
     */
    protected function getHosts($host = null): array
    {
        $host = $host ?? config('laravel-varnish.host');

        if (! is_array($host)) {
            $host = [$host];
        }

        return $host;
    }

    public function generateBanCommand(array $hosts): string
    {
        if (! is_array($hosts)) {
            $hosts = [$hosts];
        }

        $hostsRegex = collect($hosts)
            ->map(function (string $host) {
                return "(^{$host}$)";
            })
            ->implode('|');

        $config = config('laravel-varnish');

        return "sudo varnishadm -S {$config['administrative_secret']} -T 127.0.0.1:{$config['administrative_port']} 'ban req.http.host ~ {$hostsRegex}'";
    }

    protected function executeCommand(string $command): Process
    {
        $process = new Process($command);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
