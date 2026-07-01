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
        $users = $this->samba->getConnectedUsers();

        return view('modules.samba', [
            'disk' => $this->samba->getDiskUsage(),
            'usersEmpty' => empty($users),
            'columns' => [
                ['name' => 'PID'],
                ['name' => 'Utente'],
                ['name' => 'Gruppo'],
                ['name' => 'Macchina'],
            ],
            'rows' => array_map(fn (array $user) => [
                $user['pid'],
                $user['username'],
                $user['group'],
                $user['machine'],
            ], $users),
        ]);
    }
}
