import Dexie from 'dexie';
import { uuidv4 } from './uuid';

// Offline mutation queue — see WORKFLOW.md §4. Each row is one API call that
// couldn't reach the server yet; `client_uuid` inside `payload` is what makes
// replay idempotent server-side (see API.md §1).
export const db = new Dexie('kerjaku');

db.version(1).stores({
    outbox: '++id, uuid, endpoint, status, created_at',
});

export const OUTBOX_STATUS = {
    PENDING: 'pending',
    SYNCING: 'syncing',
    FAILED: 'failed',
};

export async function enqueue({ endpoint, method, payload, blob = null, blobField = null }) {
    const uuid = payload.client_uuid ?? uuidv4();
    payload.client_uuid = uuid;

    await db.outbox.add({
        uuid,
        endpoint,
        method,
        payload,
        blob,
        blobField,
        created_at: Date.now(),
        attempts: 0,
        status: OUTBOX_STATUS.PENDING,
    });

    return uuid;
}

export async function pendingCount() {
    return db.outbox.where('status').notEqual(OUTBOX_STATUS.FAILED).count();
}

export async function failedCount() {
    return db.outbox.where('status').equals(OUTBOX_STATUS.FAILED).count();
}
