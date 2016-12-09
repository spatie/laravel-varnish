<?php

namespace Spatie\Varnish;

use Symfony\Component\Process\Process;

class Varnish
{
    /**
     * @param string|array $hosts
     *
     * @return \Symfony\Component\Process\Process
     */
    public function flush($hosts = [])
    {
        $hosts = $this->getHosts($hosts);

        $command = $this->generateFlushCommand($hosts);

        return $this->executeCommand($command);
    }

    /**
     * @param array|string $hosts
     *
     * @return array
     */
    protected function getHosts($hosts): array
    {
        if (!is_array($hosts)) {
            $hosts = [$hosts];
        }

        if (!count($hosts)) {
            $hosts = config('laravel-varnish.hosts');
            return $hosts;
        }
        return $hosts;
    }

    protected function generateFlushCommand(array $hosts): string
    {
        if (!array($hosts)) {
            $hosts = [$hosts];
        }

        $hostsRegex = collect($hosts)
            ->map(function (string $host) {
                return "(^{$host}$)";
            })
            ->implode('|');

        return "sudo varnishadm -S /etc/varnish/secret -T 127.0.0.1:6082 'ban req.http.host ~ {$hostsRegex}'";
    }

    /**
     * @param $command
     *
     * @return \Symfony\Component\Process\Process
     */
    protected function executeCommand($command): Process
    {
        $process = new Process($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }


}
