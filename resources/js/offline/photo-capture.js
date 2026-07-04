import { enqueue } from './db';
import { sha256Hex } from './hash';
import { uuidv4 } from './uuid';

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
    const ctx = canvas.getContext('2d');
    if (!ctx) throw new Error('Failed to get canvas 2D context');
    ctx.drawImage(bitmap, 0, 0, width, height);

    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (blob) resolve(blob);
            else reject(new Error('toBlob returned null'));
        }, 'image/jpeg', JPEG_QUALITY);
    });
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
    const hash = await sha256Hex(compressed);
    const clientUuid = uuidv4();

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
        if (response.status >= 500) {
            await enqueue({ endpoint, method: 'POST', payload, blob: compressed, blobField: 'file' });
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal unggah, dicoba lagi otomatis' } }));
        } else {
            const text = await response.text().catch(() => 'Unknown error');
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal: ' + text.slice(0, 80) } }));
        }
        return { queued: response.status >= 500, failed: true };
    }

    window.dispatchEvent(new CustomEvent('task-updated'));
    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Foto tersimpan 📷' } }));

    return { queued: false };
}
