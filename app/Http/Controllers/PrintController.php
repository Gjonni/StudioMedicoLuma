<?php

namespace App\Http\Controllers;

use App\Services\CupsService;
use App\Settings\ModuleSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PrintController extends Controller
{
    public function __construct(protected CupsService $cups, protected ModuleSettings $settings)
    {
    }

    public function index(): View
    {
        $jobs = $this->cups->getJobs();

        return view('modules.print', [
            'status' => $this->cups->getPrinterStatus(),
            'columns' => [
                ['name' => 'Job'],
                ['name' => 'Utente'],
                ['name' => 'Dimensione'],
                ['name' => 'Inviato'],
                ['name' => '', 'html' => true],
            ],
            'rows' => array_map(fn (array $job) => [
                $job['id'],
                $job['user'],
                $job['size'],
                $job['submitted_at'],
                $this->cancelCell($job['id']),
            ], $jobs),
        ]);
    }

    protected function cancelCell(string $jobId): string
    {
        $token = csrf_token();
        $url = e(route('print.cancel', $jobId));

        return '<form method="POST" action="'.$url.'">'
            .'<input type="hidden" name="_token" value="'.e($token).'">'
            .'<button type="submit" class="text-red-600 hover:underline">Annulla</button>'
            .'</form>';
    }

    public function setup(Request $request): View
    {
        // Il dashboard gira in network_mode: host insieme a CUPS: l'host con
        // cui il browser ha raggiunto la dashboard è lo stesso su cui CUPS
        // espone l'IPP, quindi non serve una configurazione separata.
        $host = $request->getHost();
        $printerName = $this->settings->cups_printer_name;

        try {
            $status = $this->cups->getPrinterStatus();
        } catch (Throwable $e) {
            $status = 'Impossibile contattare CUPS: '.$e->getMessage();
        }

        return view('modules.print-setup', [
            'host' => $host,
            'printerName' => $printerName,
            'ippUrl' => "http://{$host}:631/printers/{$printerName}",
            'status' => $status,
        ]);
    }

    public function cancel(string $job): RedirectResponse
    {
        $this->cups->cancelJob($job);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['job' => $job])
            ->log('print_cancel');

        return redirect()->route('print.index')->with('status', "Job {$job} annullato.");
    }
}
