import './bootstrap';

// Alpine.js is bundled and started by Livewire 4 — do not import it again here.

// ── PWA: Service Worker registration ─────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // SW registration failed silently — app still works without it
        });
    });
}

// ── Push notifications ────────────────────────────────────────────────────────
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

async function subscribeToPush() {
    const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
    if (!vapidMeta) return;

    const registration = await navigator.serviceWorker.ready;
    let subscription = await registration.pushManager.getSubscription();

    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidMeta.content),
        });
    }

    const subJson = subscription.toJSON();
    await window.axios.post('/push-subscriptions', {
        endpoint: subJson.endpoint,
        keys: subJson.keys,
    });
}

window.ikRequestPushPermission = async function () {
    if (!('Notification' in window) || !('serviceWorker' in navigator)) return;

    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
        await subscribeToPush().catch((err) => console.error('[IK] Push subscribe failed:', err));
    }
};

// Auto-subscribe if permission was already granted (returning user)
if ('Notification' in window && Notification.permission === 'granted' && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.ready.then(() =>
            subscribeToPush().catch((err) => console.warn('[IK] Auto-subscribe failed:', err))
        );
    });
}
