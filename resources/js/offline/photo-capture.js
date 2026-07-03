import { enqueue } from './db';

const MAX_DIMENSION = 1600;
const JPEG_QUALITY = 0.8;

async function compress(file) {
    const bitmap = await createImageBitmap(file);
    const scale = Math.min(1, MAX_DIMENSION / Math.max(bitmap.width, bitmap.height));
    const width = Math.round(bitmap.width * scale);
    const height = Math.round(bitmap.height * scale);

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height);

    return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY));
}

async function sha256(blob) {
    const buffer = await blob.arrayBuffer();
    const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);

    return Array.from(new Uint8Array(hashBuffer))
        .map((b) => b.toString(16).padStart(2, '0'))
        .join('');
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Compress, hash, and upload a captured photo — online it goes straight to
 * the API; offline it lands in the Dexie outbox for later replay (see
 * WORKFLOW.md §3 and §4).
 */
export async function capturePhoto(file, { taskId, type, caption = '' }) {
    const compressed = await compress(file);
    const hash = await sha256(compressed);
    const clientUuid = crypto.randomUUID();

    const payload = { type, sha256: hash, caption, client_uuid: clientUuid };
    const endpoint = `/api/tasks/${taskId}/photos`;

    if (!navigator.onLine) {
        await enqueue({ endpoint, method: 'POST', payload, blob: compressed, blobField: 'file' });
        window.dispatchEvent(new CustomEvent('toast', { detail: { message: '1 foto menunggu sinkron' } }));
        return { queued: true };
    }

    const form = new FormData();
    Object.entries(payload).forEach(([key, value]) => form.append(key, value));
    form.append('file', compressed, 'photo.jpg');

    const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
        credentials: 'same-origin',
        body: form,
    });

    if (!response.ok) {
        // Network is up but the request failed (validation, hash mismatch, etc.)
        // — queue it so it isn't silently lost, and let the user retry from
        // the sync panel like any other failed mutation.
        await enqueue({ endpoint, method: 'POST', payload, blob: compressed, blobField: 'file' });
        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal unggah, dicoba lagi otomatis' } }));
        return { queued: true, failed: true };
    }

    window.dispatchEvent(new CustomEvent('task-updated'));
    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Foto tersimpan 📷' } }));

    return { queued: false };
}
