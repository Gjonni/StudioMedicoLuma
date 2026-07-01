<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['roles', 'permissions'])->orderBy('name')->get();

        return view('settings.users.index', [
            'columns' => [
                ['name' => 'Nome'],
                ['name' => 'Email'],
                ['name' => 'Username di rete'],
                ['name' => 'Ruoli'],
                ['name' => 'Permessi diretti'],
                ['name' => '', 'html' => true],
            ],
            'rows' => $users->map(fn (User $user) => [
                $user->name,
                $user->email,
                $user->network_username ?: '—',
                $user->roles->pluck('name')->join(', ') ?: '—',
                $user->permissions->pluck('name')->join(', ') ?: '—',
                $this->actionsCell($user),
            ])->all(),
        ]);
    }

    protected function actionsCell(User $user): string
    {
        $edit = '<a href="'.e(route('settings.users.edit', $user)).'" class="text-sky-600 hover:underline">Modifica</a>';

        if ($user->id === auth()->id()) {
            return $edit;
        }

        $token = csrf_token();
        $url = e(route('settings.users.destroy', $user));

        return $edit.' <form method="POST" action="'.$url.'" class="inline" onsubmit="return confirm(\'Eliminare questo utente?\');">'
            .'<input type="hidden" name="_token" value="'.e($token).'">'
            .'<input type="hidden" name="_method" value="DELETE">'
            .'<button type="submit" class="text-red-600 hover:underline ml-2">Elimina</button>'
            .'</form>';
    }

    public function create(): View
    {
        return view('settings.users.form', [
            'user' => new User(),
            'roles' => Role::all(),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'network_username' => $data['network_username'] ?? null,
            'password' => bcrypt($data['password']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('settings.users.index')->with('status', "Utente {$user->name} creato.");
    }

    public function edit(User $user): View
    {
        return view('settings.users.form', [
            'user' => $user,
            'roles' => Role::all(),
            'permissions' => Permission::all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateData($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->network_username = $data['network_username'] ?? null;

        if (! empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();
        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('settings.users.index')->with('status', "Utente {$user->name} aggiornato.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Non puoi eliminare il tuo stesso utente.');
        }

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('error', 'Non puoi eliminare l\'unico amministratore rimasto.');
        }

        $user->delete();

        return redirect()->route('settings.users.index')->with('status', 'Utente eliminato.');
    }

    protected function validateData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'network_username' => ['nullable', 'string', 'max:255'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);
    }
}
