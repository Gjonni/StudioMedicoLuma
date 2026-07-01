<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdguardService;
use App\Services\CupsService;
use App\Services\SambaService;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        protected CupsService $cups,
        protected SambaService $samba,
        protected AdguardService $adguard,
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();

        return view('dashboard', [
            'printerStatus' => $user->can('modulo-stampa') ? $this->safe(fn () => $this->cups->getPrinterStatus()) : null,
            'printJobsCount' => $user->can('modulo-stampa') ? $this->safe(fn () => count($this->cups->getJobs()), 0) : 0,
            'printsByUser' => $user->can('modulo-stampa') ? $this->printsByUser() : [],
            'scansByUser' => $user->can('modulo-scansione') ? $this->scansByUser() : [],
            'diskUsage' => $user->can('modulo-samba') ? $this->safe(fn () => $this->samba->getDiskUsage(), []) : [],
            'adguardEnabled' => $user->can('modulo-adguard') ? $this->safe(fn () => $this->adguard->isProtectionEnabled(), false) : false,
        ]);
    }

    /**
     * Job in coda/recenti raggruppati per utente di rete (attributo CUPS
     * "user"), non per utente della dashboard: la stampa avviene via IPP
     * diretto (vedi /print/setup), la dashboard non fa da proxy di stampa.
     */
    protected function printsByUser(): array
    {
        $jobs = $this->safe(fn () => $this->cups->getJobs(), []);

        $networkUsernames = User::whereNotNull('network_username')->pluck('name', 'network_username');

        $counts = [];
        foreach ($jobs as $job) {
            $owner = $job['user'] ?? 'sconosciuto';
            $label = $networkUsernames[$owner] ?? $owner;
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        arsort($counts);

        return $counts;
    }

    /**
     * Scansioni avviate dalla dashboard, per utente autenticato (tracciate
     * via spatie/laravel-activitylog al momento dell'azione).
     */
    protected function scansByUser(): array
    {
        return Activity::query()
            ->where('description', 'scan')
            ->with('causer')
            ->get()
            ->groupBy(fn (Activity $activity) => $activity->causer?->name ?? 'sconosciuto')
            ->map->count()
            ->sortDesc()
            ->all();
    }

    protected function safe(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $default;
        }
    }
}
