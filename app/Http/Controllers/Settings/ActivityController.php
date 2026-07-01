<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(): View
    {
        $activities = Activity::with('causer')->latest()->paginate(30);

        return view('settings.activity.index', [
            'activities' => $activities,
            'columns' => [
                ['name' => 'Quando'],
                ['name' => 'Utente'],
                ['name' => 'Azione'],
                ['name' => 'Dettagli'],
            ],
            'rows' => $activities->map(fn (Activity $activity) => [
                $activity->created_at->format('d/m/Y H:i'),
                $activity->causer?->name ?? 'Sistema',
                $activity->description,
                $activity->properties->toJson(),
            ])->all(),
        ]);
    }
}
