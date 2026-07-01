<?php

namespace App\Services;

use App\Settings\ModuleSettings;
use Illuminate\Support\Facades\Process;

class CupsService
{
    protected string $host;

    protected string $printer;

    public function __construct(ModuleSettings $settings)
    {
        $this->host = $settings->cups_host;
        $this->printer = $settings->cups_printer_name;
    }

    public function getPrinterStatus(): string
    {
        $result = Process::run(['lpstat', '-h', $this->host, '-p', $this->printer]);

        return trim($result->output() ?: $result->errorOutput());
    }

    public function getJobs(): array
    {
        $result = Process::run(['lpstat', '-h', $this->host, '-o']);

        $lines = array_filter(explode("\n", trim($result->output())));

        return array_map(function (string $line) {
            // Formato lpstat -o: "<job-id> <utente> <dimensione> <data>"
            preg_match('/^(\S+)\s+(\S+)\s+(\d+)\s+(.*)$/', $line, $matches);

            return [
                'id' => $matches[1] ?? $line,
                'user' => $matches[2] ?? null,
                'size' => $matches[3] ?? null,
                'submitted_at' => $matches[4] ?? null,
            ];
        }, $lines);
    }

    public function cancelJob(string $jobId): bool
    {
        $result = Process::run(['cancel', '-h', $this->host, $jobId]);

        return $result->successful();
    }
}
