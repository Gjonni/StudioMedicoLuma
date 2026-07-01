<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('modules.cups_host', env('CUPS_HOST', 'localhost'));
        $this->migrator->add('modules.cups_printer_name', env('CUPS_PRINTER_NAME', 'Canon_MG2500'));
        $this->migrator->add('modules.adguard_host', env('ADGUARD_HOST', 'http://localhost:3000'));
        $this->migrator->add('modules.adguard_username', env('ADGUARD_USERNAME', 'admin'));
        $this->migrator->add('modules.adguard_password', env('ADGUARD_PASSWORD'));
        $this->migrator->add('modules.samba_share_path', env('SAMBA_SHARE_PATH', '/mnt/usbdisk'));
        $this->migrator->add('modules.scan_output_path', env('SCAN_OUTPUT_PATH', '/mnt/usbdisk/Scansioni'));
        $this->migrator->add('modules.scan_device', env('SCAN_DEVICE'));
        $this->migrator->add('modules.saned_host', env('SANED_HOST', 'localhost'));
    }
};
