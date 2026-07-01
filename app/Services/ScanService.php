<?php

namespace App\Services;

use App\Settings\ModuleSettings;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ScanService
{
    protected string $outputPath;

    protected ?string $device;

    public function __construct(ModuleSettings $settings)
    {
        $this->outputPath = $settings->scan_output_path;
        $this->device = $settings->scan_device;
    }

    public function listDevices(): string
    {
        $result = Process::run(['scanimage', '-L']);

        return trim($result->output() ?: $result->errorOutput());
    }

    /**
     * Avvia una scansione e salva il risultato direttamente nello share Samba.
     */
    public function scanAndSave(string $format = 'png'): string
    {
        if (! is_dir($this->outputPath)) {
            throw new RuntimeException("Percorso di destinazione non disponibile: {$this->outputPath}");
        }

        $filename = 'scan-'.date('Y-m-d_H-i-s').'.'.$format;
        $destination = rtrim($this->outputPath, '/').'/'.$filename;

        $command = ['scanimage', '--format', $format, '-o', $destination];

        if ($this->device) {
            array_splice($command, 1, 0, ['-d', $this->device]);
        }

        $result = Process::timeout(120)->run($command);

        if (! $result->successful()) {
            throw new RuntimeException('Scansione fallita: '.trim($result->errorOutput()));
        }

        return $destination;
    }
}
