<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(): View
    {
        return view('settings.activity.index', [
            'activities' => Activity::with('causer')->latest()->paginate(30),
        ]);
    }
}
