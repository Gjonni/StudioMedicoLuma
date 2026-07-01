<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('settings.roles.index', [
            'roles' => Role::with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('settings.roles.form', [
            'role' => new Role(),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('settings.roles.index')->with('status', "Ruolo {$role->name} creato.");
    }

    public function edit(Role $role): View
    {
        return view('settings.roles.form', [
            'role' => $role,
            'permissions' => Permission::all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validateData($request, $role);

        $role->name = $data['name'];
        $role->save();
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('settings.roles.index')->with('status', "Ruolo {$role->name} aggiornato.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Il ruolo admin non può essere eliminato.');
        }

        $role->delete();

        return redirect()->route('settings.roles.index')->with('status', 'Ruolo eliminato.');
    }

    protected function validateData(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role?->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);
    }
}
