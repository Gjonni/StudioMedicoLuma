<?php

namespace App\Http\Controllers;

use App\Services\AdguardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdguardController extends Controller
{
    public function __construct(protected AdguardService $adguard)
    {
    }

    public function index(): View
    {
        $columns = [
            ['name' => 'Ora'],
            ['name' => 'Dominio'],
            ['name' => 'Client'],
            ['name' => 'Esito'],
        ];

        try {
            $queryLog = $this->adguard->getQueryLog();

            return view('modules.adguard', [
                'status' => $this->adguard->getStatus(),
                'stats' => $this->adguard->getStats(),
                'columns' => $columns,
                'rows' => array_map(fn (array $entry) => [
                    $entry['time'] ?? '',
                    $entry['question']['name'] ?? '',
                    $entry['client'] ?? '',
                    $entry['reason'] ?? '',
                ], $queryLog),
            ]);
        } catch (Throwable $e) {
            return view('modules.adguard', [
                'status' => [],
                'stats' => [],
                'columns' => $columns,
                'rows' => [],
                'connectionError' => 'Impossibile contattare AdGuard Home: '.$e->getMessage(),
            ]);
        }
    }

    public function toggleProtection(Request $request): RedirectResponse
    {
        $enabled = $request->boolean('enabled');

        $this->adguard->toggleProtection($enabled);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['enabled' => $enabled])
            ->log('dns_toggle');

        return redirect()->route('adguard.index')
            ->with('status', $enabled ? 'Protezione DNS attivata.' : 'Protezione DNS disattivata.');
    }
}
