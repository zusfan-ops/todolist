function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);

    return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function vapidPublicKey() {
    return document.querySelector('meta[name="vapid-public-key"]')?.content ?? '';
}

export function isPushSupported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && !!vapidPublicKey();
}

export function permissionState() {
    return isPushSupported() ? Notification.permission : 'unsupported';
}

/**
 * Ask for notification permission, subscribe with the push service, and
 * register the subscription server-side — this is what actually turns on
 * the due-date/timer/weekly-report reminders (see WORKFLOW.md §7).
 */
export async function enablePushNotifications() {
    if (!isPushSupported()) return { ok: false, reason: 'unsupported' };

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return { ok: false, reason: permission };

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey()),
    });

    const json = subscription.toJSON();
    const response = await fetch('/api/push/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify({ endpoint: json.endpoint, keys: json.keys }),
    });

    if (!response.ok) return { ok: false, reason: 'server' };

    return { ok: true };
}
