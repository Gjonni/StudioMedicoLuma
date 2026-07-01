<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Settings\ModuleSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleSettingsController extends Controller
{
    public function edit(ModuleSettings $settings): View
    {
        return view('settings.modules.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, ModuleSettings $settings): RedirectResponse
    {
        $data = $request->validate([
            'cups_host' => ['required', 'string', 'max:255'],
            'cups_printer_name' => ['required', 'string', 'max:255'],
            'adguard_host' => ['required', 'string', 'max:255'],
            'adguard_username' => ['required', 'string', 'max:255'],
            'adguard_password' => ['nullable', 'string', 'max:255'],
            'samba_share_path' => ['required', 'string', 'max:255'],
            'scan_output_path' => ['required', 'string', 'max:255'],
            'scan_device' => ['nullable', 'string', 'max:255'],
            'saned_host' => ['required', 'string', 'max:255'],
        ]);

        if (empty($data['adguard_password'])) {
            unset($data['adguard_password']);
        }

        $settings->fill($data);
        $settings->save();

        return redirect()->route('settings.modules.edit')->with('status', 'Configurazione moduli aggiornata.');
    }
}
