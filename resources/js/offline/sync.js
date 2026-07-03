import { db, OUTBOX_STATUS, enqueue, pendingCount, failedCount } from './db';

const MAX_ATTEMPTS = 5;
let syncing = false;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function sendItem(item) {
    const headers = { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' };
    let body;

    if (item.blob) {
        const form = new FormData();
        Object.entries(item.payload).forEach(([key, value]) => form.append(key, value ?? ''));
        form.append(item.blobField ?? 'file', item.blob, 'photo.jpg');
        body = form;
    } else {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(item.payload);
    }

    return fetch(item.endpoint, { method: item.method, headers, credentials: 'same-origin', body });
}

export async function processQueue() {
    if (syncing || !navigator.onLine) return;
    syncing = true;

    try {
        const items = await db.outbox.where('status').equals(OUTBOX_STATUS.PENDING).sortBy('created_at');

        for (const item of items) {
            try {
                const response = await sendItem(item);

                if (response.ok) {
                    await db.outbox.delete(item.id);
                    continue;
                }

                if (response.status >= 400 && response.status < 500) {
                    // Business conflict (409) or validation error (422) — needs a
                    // human decision, not a blind retry.
                    await db.outbox.update(item.id, { status: OUTBOX_STATUS.FAILED, attempts: item.attempts + 1 });
                    continue;
                }

                throw new Error(`Server error ${response.status}`);
            } catch (err) {
                const attempts = item.attempts + 1;
                await db.outbox.update(item.id, {
                    attempts,
                    status: attempts >= MAX_ATTEMPTS ? OUTBOX_STATUS.FAILED : OUTBOX_STATUS.PENDING,
                });
            }
        }
    } finally {
        syncing = false;
        window.dispatchEvent(new CustomEvent('kerjaku:outbox-changed'));
    }
}

export async function retryFailed() {
    await db.outbox.where('status').equals(OUTBOX_STATUS.FAILED).modify({ status: OUTBOX_STATUS.PENDING, attempts: 0 });
    return processQueue();
}

export async function discardFailed(id) {
    await db.outbox.delete(id);
    window.dispatchEvent(new CustomEvent('kerjaku:outbox-changed'));
}

window.addEventListener('online', processQueue);
window.addEventListener('kerjaku:enqueue', processQueue);
setInterval(processQueue, 60000);

if (navigator.onLine) {
    processQueue();
}

window.KerjaKuOffline = { db, enqueue, pendingCount, failedCount, processQueue, retryFailed, discardFailed };
