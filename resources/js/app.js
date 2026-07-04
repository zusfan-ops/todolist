import './bootstrap';
import './offline/sync';
import { capturePhoto } from './offline/photo-capture';
import { sendJson } from './offline/api';
import { promptInstall, canInstall, isStandalone } from './pwa-install';
import { isPushSupported, permissionState, enablePushNotifications } from './push-notifications';

// Livewire 3 ships and boots its own Alpine instance — do not import/start
// a separate copy here, register hooks via the 'alpine:init' event instead.
import Sortable from 'sortablejs';

window.Sortable = Sortable;
window.KerjaKuPhoto = { capturePhoto };
window.KerjaKuApi = { sendJson };
window.KerjaKuPWA = { promptInstall, canInstall, isStandalone };
window.KerjaKuPush = { isPushSupported, permissionState, enablePushNotifications };

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.error('SW registration failed', err);
        });
    });
}
