<?php

namespace App\Http\Controllers;

use App\Services\ScanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ScanController extends Controller
{
    public function __construct(protected ScanService $scan)
    {
    }

    public function index(): View
    {
        return view('modules.scan', [
            'devices' => $this->scan->listDevices(),
        ]);
    }

    public function store(): RedirectResponse
    {
        try {
            $path = $this->scan->scanAndSave();

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['path' => $path])
                ->log('scan');

            return redirect()->route('scan.index')->with('status', "Scansione salvata in {$path}");
        } catch (RuntimeException $e) {
            return redirect()->route('scan.index')->with('error', $e->getMessage());
        }
    }
}
