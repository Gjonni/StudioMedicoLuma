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
        try {
            return view('modules.adguard', [
                'status' => $this->adguard->getStatus(),
                'stats' => $this->adguard->getStats(),
                'queryLog' => $this->adguard->getQueryLog(),
            ]);
        } catch (Throwable $e) {
            return view('modules.adguard', [
                'status' => [],
                'stats' => [],
                'queryLog' => [],
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
