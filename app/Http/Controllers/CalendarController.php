<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\GoogleCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(protected GoogleCalendarService $google)
    {
    }

    public function index(): View
    {
        return view('calendar.index', [
            'googleConnected' => $this->google->isConnected(auth()->user()),
        ]);
    }

    /**
     * Eventi nel formato atteso da FullCalendar. Se l'utente ha collegato
     * Google Calendar, prima sincronizza (pull) gli eventi Google nel
     * range richiesto verso la tabella locale, poi restituisce sempre e
     * solo i dati locali (fonte di verità unica per il frontend).
     */
    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $user = $request->user();

        if ($this->google->isConnected($user)) {
            $this->pullGoogleEvents($user, $validated['start'], $validated['end']);
        }

        $events = Event::where('user_id', $user->id)
            ->whereDate('start', '>=', $validated['start'])
            ->whereDate('start', '<', $validated['end'])
            ->get(['id', 'title', 'start', 'end']);

        return response()->json($events);
    }

    protected function pullGoogleEvents(\App\Models\User $user, string $start, string $end): void
    {
        $timeMin = Carbon::parse($start, 'Europe/Rome')->toAtomString();
        $timeMax = Carbon::parse($end, 'Europe/Rome')->toAtomString();

        $googleEvents = $this->google->getEvents($user, $timeMin, $timeMax);
        $activeIds = [];

        foreach ($googleEvents as $gEvent) {
            $activeIds[] = $gEvent['googleId'];

            $local = Event::where('google_event_id', $gEvent['googleId'])
                ->where('user_id', $user->id)
                ->first();

            if ($local) {
                $local->fill(['title' => $gEvent['title'], 'start' => $gEvent['start'], 'end' => $gEvent['end']]);
                if ($local->isDirty()) {
                    $local->save();
                }
            } else {
                Event::create([
                    'title' => $gEvent['title'],
                    'user_id' => $user->id,
                    'start' => $gEvent['start'],
                    'end' => $gEvent['end'] ?? $gEvent['start'],
                    'google_event_id' => $gEvent['googleId'],
                ]);
            }
        }

        Event::where('user_id', $user->id)
            ->whereNotNull('google_event_id')
            ->whereNotIn('google_event_id', $activeIds)
            ->where('start', '>=', $start)
            ->where('start', '<', $end)
            ->delete();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ]);

        $user = $request->user();

        $event = Event::create([...$validated, 'user_id' => $user->id]);

        if ($this->google->isConnected($user)) {
            $googleId = $this->google->createEvent($user, $event);
            if ($googleId) {
                $event->update(['google_event_id' => $googleId]);
            }
        }

        return response()->json($event);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ]);

        $event->update($validated);

        if ($this->google->isConnected($request->user())) {
            $this->google->updateEvent($request->user(), $event);
        }

        return response()->json($event);
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);

        $googleEventId = $event->google_event_id;
        $event->delete();

        if ($googleEventId && $this->google->isConnected($request->user())) {
            $this->google->deleteEvent($request->user(), $googleEventId);
        }

        return response()->json(['deleted' => true]);
    }
}
