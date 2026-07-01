<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const CALENDAR_API = 'https://www.googleapis.com/calendar/v3';

    public function isConnected(User $user): bool
    {
        return ! empty($user->google_refresh_token);
    }

    public function getAccessToken(User $user): ?string
    {
        if (empty($user->google_refresh_token)) {
            return null;
        }

        $clientId = config('services.google_calendar.client_id');
        $clientSecret = config('services.google_calendar.client_secret');

        if (! $clientId || ! $clientSecret) {
            Log::warning('GoogleCalendar: credenziali non configurate in .env');

            return null;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $user->google_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            Log::warning('GoogleCalendar: refresh del token fallito', [
                'user' => $user->id,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json('access_token');
    }

    /** Eventi nel formato FullCalendar, marcati come non modificabili (provengono da Google). */
    public function getEvents(User $user, string $timeMin, string $timeMax): array
    {
        $token = $this->getAccessToken($user);
        if (! $token) {
            return [];
        }

        $calendarId = $user->google_calendar_id ?: 'primary';

        $response = Http::withToken($token)->get(
            self::CALENDAR_API.'/calendars/'.urlencode($calendarId).'/events',
            [
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
                'singleEvents' => 'true',
                'orderBy' => 'startTime',
                'maxResults' => 250,
            ]
        );

        if (! $response->successful()) {
            Log::warning('GoogleCalendar: getEvents fallito', ['user' => $user->id, 'status' => $response->status()]);

            return [];
        }

        return collect($response->json('items', []))
            ->map(fn (array $item) => [
                'title' => $item['summary'] ?? '(senza titolo)',
                'start' => $item['start']['dateTime'] ?? $item['start']['date'] ?? null,
                'end' => $item['end']['dateTime'] ?? $item['end']['date'] ?? null,
                'googleId' => $item['id'],
            ])
            ->values()
            ->toArray();
    }

    public function listCalendars(User $user): array
    {
        $token = $this->getAccessToken($user);
        if (! $token) {
            return [];
        }

        $response = Http::withToken($token)->get(self::CALENDAR_API.'/users/me/calendarList');

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('items', []))
            ->map(fn (array $item) => [
                'id' => $item['id'],
                'summary' => $item['summary'] ?? $item['id'],
                'primary' => $item['primary'] ?? false,
            ])
            ->sortByDesc('primary')
            ->values()
            ->toArray();
    }

    public function createEvent(User $user, Event $event): ?string
    {
        $token = $this->getAccessToken($user);
        if (! $token) {
            return null;
        }

        $calendarId = $user->google_calendar_id ?: 'primary';

        $response = Http::withToken($token)->post(
            self::CALENDAR_API.'/calendars/'.urlencode($calendarId).'/events',
            [
                'summary' => $event->title,
                'start' => ['dateTime' => $event->start->toAtomString(), 'timeZone' => 'Europe/Rome'],
                'end' => ['dateTime' => $event->end->toAtomString(), 'timeZone' => 'Europe/Rome'],
            ]
        );

        return $response->successful() ? $response->json('id') : null;
    }

    public function updateEvent(User $user, Event $event): bool
    {
        if (! $event->google_event_id) {
            return false;
        }

        $token = $this->getAccessToken($user);
        if (! $token) {
            return false;
        }

        $calendarId = $user->google_calendar_id ?: 'primary';

        $response = Http::withToken($token)->put(
            self::CALENDAR_API.'/calendars/'.urlencode($calendarId).'/events/'.urlencode($event->google_event_id),
            [
                'summary' => $event->title,
                'start' => ['dateTime' => $event->start->toAtomString(), 'timeZone' => 'Europe/Rome'],
                'end' => ['dateTime' => $event->end->toAtomString(), 'timeZone' => 'Europe/Rome'],
            ]
        );

        return $response->successful();
    }

    public function deleteEvent(User $user, string $googleEventId): bool
    {
        $token = $this->getAccessToken($user);
        if (! $token) {
            return false;
        }

        $calendarId = $user->google_calendar_id ?: 'primary';

        $response = Http::withToken($token)->delete(
            self::CALENDAR_API.'/calendars/'.urlencode($calendarId).'/events/'.urlencode($googleEventId)
        );

        return $response->successful() || $response->status() === 410;
    }
}
