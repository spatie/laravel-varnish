<?php

namespace Spatie\Varnish;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Varnish
{
    /**
     * @param string|array $host
     * @param string $url
     *
     * @return \Symfony\Component\Process\Process
     */
    public function flush($host = null, string $url = null): Process
    {
        $host = $this->getHosts($host);

        $command = $this->generateBanCommand($host, $url);

        return $this->executeCommand($command);
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
     * @param array $hosts
     * @param string $url
     * @return string
     */
    public function generateBanCommand(array $hosts, string $url = null): string
    {
        $hostsRegex = collect($hosts)
            ->map(function (string $host) {
                return "(^{$host}$)";
            })
            ->implode('|');

        $config = config('varnish');

        $urlRegex = '';
        if (! empty($url)) {
            $urlRegex = " && req.url ~ {$url}";
        }

        return "sudo varnishadm -S {$config['administrative_secret']} -T 127.0.0.1:{$config['administrative_port']} 'ban req.http.host ~ {$hostsRegex}{$urlRegex}'";
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
