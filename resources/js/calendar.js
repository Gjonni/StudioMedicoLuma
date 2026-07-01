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

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) {
        return;
    }

    const colors = {
        success: 'bg-emerald-600',
        error: 'bg-red-600',
    };

    const toast = document.createElement('div');
    toast.className = `${colors[type] ?? colors.success} text-white text-sm px-4 py-3 rounded-lg shadow-lg transition-opacity duration-300`;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Apre il modal di creazione/modifica evento e risolve con l'azione scelta
 * dall'utente: { action: 'save' | 'delete' | 'cancel', title }.
 */
function openEventModal({ heading, initialTitle = '', allowDelete = false }) {
    const modal = document.getElementById('event-modal');
    const title = document.getElementById('event-modal-title');
    const input = document.getElementById('event-modal-input');
    const confirmBtn = document.getElementById('event-modal-confirm');
    const cancelBtn = document.getElementById('event-modal-cancel');
    const deleteBtn = document.getElementById('event-modal-delete');

    title.textContent = heading;
    input.value = initialTitle;
    deleteBtn.classList.toggle('hidden', !allowDelete);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    input.focus();

    return new Promise((resolve) => {
        const close = (result) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            deleteBtn.removeEventListener('click', onDelete);
            input.removeEventListener('keydown', onKeydown);
            resolve(result);
        };

        const onConfirm = () => close({ action: 'save', title: input.value.trim() });
        const onCancel = () => close({ action: 'cancel' });
        const onDelete = () => close({ action: 'delete' });
        const onKeydown = (e) => {
            if (e.key === 'Enter') onConfirm();
            if (e.key === 'Escape') onCancel();
        };

        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);
        deleteBtn.addEventListener('click', onDelete);
        input.addEventListener('keydown', onKeydown);
    });
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
            const result = await openEventModal({ heading: 'Nuovo evento' });
            calendar.unselect();

            if (result.action !== 'save' || !result.title) {
                return;
            }

            try {
                await api(storeUrl, {
                    method: 'POST',
                    body: JSON.stringify({ title: result.title, start: info.startStr, end: info.endStr }),
                });
                calendar.refetchEvents();
                showToast('Evento creato.');
            } catch (e) {
                showToast('Impossibile creare l\'evento.', 'error');
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
                showToast('Evento spostato.');
            } catch (e) {
                showToast('Impossibile spostare l\'evento.', 'error');
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
                showToast('Evento aggiornato.');
            } catch (e) {
                showToast('Impossibile ridimensionare l\'evento.', 'error');
                info.revert();
            }
        },

        eventClick: async (info) => {
            const result = await openEventModal({
                heading: 'Modifica evento',
                initialTitle: info.event.title,
                allowDelete: true,
            });

            if (result.action === 'delete') {
                try {
                    await api(`${storeUrl}/${info.event.id}`, { method: 'DELETE' });
                    info.event.remove();
                    showToast('Evento eliminato.');
                } catch (e) {
                    showToast('Impossibile eliminare l\'evento.', 'error');
                }
                return;
            }

            if (result.action === 'save' && result.title) {
                try {
                    await api(`${storeUrl}/${info.event.id}`, {
                        method: 'PUT',
                        body: JSON.stringify({
                            title: result.title,
                            start: info.event.startStr,
                            end: info.event.endStr || info.event.startStr,
                        }),
                    });
                    info.event.setProp('title', result.title);
                    showToast('Evento aggiornato.');
                } catch (e) {
                    showToast('Impossibile modificare l\'evento.', 'error');
                }
            }
        },
    });

    calendar.render();
});
