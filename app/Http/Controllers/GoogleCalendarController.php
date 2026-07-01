<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SCOPE = 'https://www.googleapis.com/auth/calendar';

    public function connect(Request $request): RedirectResponse
    {
        $clientId = config('services.google_calendar.client_id');

        if (! $clientId) {
            return redirect()->route('calendar.index')
                ->with('error', 'Google Calendar non configurato: imposta GOOGLE_CLIENT_ID/GOOGLE_CLIENT_SECRET nel .env.');
        }

        $state = Str::random(40);
        $request->session()->put('google_calendar_state', $state);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('google-calendar.callback'),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect(self::AUTH_URL.'?'.$params);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_calendar_state');

        if (! $expectedState || $request->get('state') !== $expectedState) {
            return redirect()->route('calendar.index')->with('error', 'Richiesta OAuth non valida o scaduta.');
        }

        if ($request->has('error')) {
            return redirect()->route('calendar.index')->with('error', 'Accesso a Google Calendar negato.');
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $request->get('code'),
            'client_id' => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'redirect_uri' => route('google-calendar.callback'),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful() || ! $response->json('refresh_token')) {
            Log::warning('GoogleCalendar: token exchange fallito', ['status' => $response->status()]);

            return redirect()->route('calendar.index')->with('error', 'Impossibile collegare Google Calendar. Riprova.');
        }

        $user = $request->user();
        $user->google_refresh_token = $response->json('refresh_token');
        $user->google_calendar_id = $user->google_calendar_id ?: 'primary';
        $user->save();

        return redirect()->route('calendar.index')->with('status', 'Google Calendar collegato con successo!');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->google_refresh_token = null;
        $user->google_calendar_id = null;
        $user->save();

        return redirect()->route('calendar.index')->with('status', 'Google Calendar disconnesso.');
    }

    public function listCalendars(Request $request, GoogleCalendarService $service): JsonResponse
    {
        return response()->json($service->listCalendars($request->user()));
    }

    public function setCalendar(Request $request): JsonResponse
    {
        $validated = $request->validate(['calendar_id' => ['required', 'string', 'max:255']]);

        $user = $request->user();
        $user->google_calendar_id = $validated['calendar_id'];
        $user->save();

        return response()->json(['ok' => true]);
    }
}
