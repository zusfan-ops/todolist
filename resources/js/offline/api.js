import { enqueue } from './db';
import { uuidv4 } from './uuid';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * POST/PATCH a JSON payload straight to the API when online; queue it in the
 * Dexie outbox when offline so it replays once connectivity returns (see
 * WORKFLOW.md §4). Every payload carries a client_uuid for idempotent replay.
 */
export async function sendJson(endpoint, method, payload) {
    payload.client_uuid = payload.client_uuid ?? uuidv4();

    if (!navigator.onLine) {
        await enqueue({ endpoint, method, payload });

        return { queued: true };
    }

    try {
        const response = await fetch(endpoint, {
            method,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (!response.ok && response.status >= 500) {
            await enqueue({ endpoint, method, payload });

            return { queued: true };
        }

        return { queued: false, response };
    } catch (err) {
        await enqueue({ endpoint, method, payload });

        return { queued: true };
    }
}
