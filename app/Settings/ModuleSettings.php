<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ModuleSettings extends Settings
{
    public string $cups_host;

    public string $cups_printer_name;

    public string $adguard_host;

    public string $adguard_username;

    public ?string $adguard_password;

    public string $samba_share_path;

    public string $scan_output_path;

    public ?string $scan_device;

    public string $saned_host;

    public static function group(): string
    {
        return 'modules';
    }
}
