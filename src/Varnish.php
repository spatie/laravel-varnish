<?php

namespace Spatie\Varnish;

use Symfony\Component\Process\Process;

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

        $command = $this->generateFlushCommand($host);

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

    protected function generateFlushCommand(array $hosts): string
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

        return "sudo varnishadm -S {$config['secret']} -T 127.0.0.1:{$config['administrative_port']} 'ban req.http.host ~ {$hostsRegex}'";
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
