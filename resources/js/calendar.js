import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import itLocale from '@fullcalendar/core/locales/it';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function api(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...options.headers,
        },
    });

    if (!response.ok) {
        throw new Error(`Richiesta fallita (${response.status})`);
    }

    return response.status === 204 ? null : response.json();
}

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('calendar');
    if (!el) {
        return;
    }

    const eventsUrl = el.dataset.eventsUrl;
    const storeUrl = el.dataset.storeUrl;

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        locale: itLocale,
        timeZone: 'Europe/Rome',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        initialView: 'timeGridWeek',
        selectable: true,
        editable: true,
        eventOverlap: false,
        events: { url: eventsUrl },

        select: async (info) => {
            const title = window.prompt('Titolo evento:');
            calendar.unselect();
            if (!title) {
                return;
            }

            try {
                await api(storeUrl, {
                    method: 'POST',
                    body: JSON.stringify({ title, start: info.startStr, end: info.endStr }),
                });
                calendar.refetchEvents();
            } catch (e) {
                window.alert('Impossibile creare l\'evento.');
            }
        },

        eventDrop: async (info) => {
            try {
                await api(`${storeUrl}/${info.event.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        title: info.event.title,
                        start: info.event.startStr,
                        end: info.event.endStr || info.event.startStr,
                    }),
                });
            } catch (e) {
                window.alert('Impossibile spostare l\'evento.');
                info.revert();
            }
        },

        eventResize: async (info) => {
            try {
                await api(`${storeUrl}/${info.event.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        title: info.event.title,
                        start: info.event.startStr,
                        end: info.event.endStr,
                    }),
                });
            } catch (e) {
                window.alert('Impossibile ridimensionare l\'evento.');
                info.revert();
            }
        },

        eventClick: async (info) => {
            const action = window.prompt('Scrivi "modifica" per rinominare o "elimina" per rimuovere:', 'modifica');

            if (action === 'elimina') {
                try {
                    await api(`${storeUrl}/${info.event.id}`, { method: 'DELETE' });
                    info.event.remove();
                } catch (e) {
                    window.alert('Impossibile eliminare l\'evento.');
                }
                return;
            }

            if (action === 'modifica') {
                const title = window.prompt('Nuovo titolo:', info.event.title);
                if (!title) {
                    return;
                }

                try {
                    await api(`${storeUrl}/${info.event.id}`, {
                        method: 'PUT',
                        body: JSON.stringify({
                            title,
                            start: info.event.startStr,
                            end: info.event.endStr || info.event.startStr,
                        }),
                    });
                    info.event.setProp('title', title);
                } catch (e) {
                    window.alert('Impossibile modificare l\'evento.');
                }
            }
        },
    });

    calendar.render();
});
