<?php

namespace App\Services;

use App\Settings\ModuleSettings;
use Illuminate\Support\Facades\Process;

class SambaService
{
    protected string $sharePath;

    public function __construct(ModuleSettings $settings)
    {
        $this->sharePath = $settings->samba_share_path;
    }

    public function getDiskUsage(): array
    {
        $result = Process::run(['df', '-h', $this->sharePath]);

        $lines = array_values(array_filter(explode("\n", trim($result->output()))));

        if (count($lines) < 2) {
            return [];
        }

        $values = preg_split('/\s+/', trim($lines[1]));

        return [
            'filesystem' => $values[0] ?? null,
            'size' => $values[1] ?? null,
            'used' => $values[2] ?? null,
            'available' => $values[3] ?? null,
            'use_percent' => $values[4] ?? null,
            'mounted_on' => $values[5] ?? null,
        ];
    }

    public function getConnectedUsers(): array
    {
        $result = Process::run(['smbstatus', '-b']);

        $lines = array_values(array_filter(explode("\n", trim($result->output()))));

        // Le prime righe di smbstatus -b sono intestazione/separatore.
        $entries = array_slice($lines, 2);

        return array_map(function (string $line) {
            $values = preg_split('/\s+/', trim($line));

            return [
                'pid' => $values[0] ?? null,
                'username' => $values[1] ?? null,
                'group' => $values[2] ?? null,
                'machine' => $values[3] ?? null,
            ];
        }, $entries);
    }
}
