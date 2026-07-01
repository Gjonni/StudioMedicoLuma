<?php

namespace App\Services;

use App\Settings\ModuleSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AdguardService
{
    protected string $host;

    protected string $username;

    protected ?string $password;

    public function __construct(ModuleSettings $settings)
    {
        $this->host = rtrim($settings->adguard_host, '/');
        $this->username = $settings->adguard_username;
        $this->password = $settings->adguard_password;
    }

    protected function client()
    {
        return Http::baseUrl($this->host)
            ->withBasicAuth($this->username, $this->password)
            ->timeout(5);
    }

    public function getStatus(): array
    {
        return $this->client()->get('/control/status')->json() ?? [];
    }

    public function getStats(): array
    {
        return $this->client()->get('/control/stats')->json() ?? [];
    }

    public function getQueryLog(int $limit = 50): array
    {
        $data = $this->client()->get('/control/querylog', ['limit' => $limit])->json();

        return $data['data'] ?? [];
    }

    public function isProtectionEnabled(): bool
    {
        return (bool) (Cache::remember(
            'adguard.status',
            5,
            fn () => $this->getStatus()
        )['protection_enabled'] ?? false);
    }

    public function toggleProtection(bool $enabled): bool
    {
        $response = $this->client()->post('/control/protection', [
            'enabled' => $enabled,
        ]);

        Cache::forget('adguard.status');

        return $response->successful();
    }
}
