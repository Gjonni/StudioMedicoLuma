<?php

namespace App\Http\Controllers;

use App\Services\SambaService;
use Illuminate\View\View;

class SambaController extends Controller
{
    public function __construct(protected SambaService $samba)
    {
    }

    public function index(): View
    {
        return view('modules.samba', [
            'disk' => $this->samba->getDiskUsage(),
            'users' => $this->samba->getConnectedUsers(),
        ]);
    }
}
