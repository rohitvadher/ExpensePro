var deferredPrompt = null;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        var workerUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : './') + 'sw.js';
        navigator.serviceWorker.register(workerUrl).then(function (registration) {
            registration.addEventListener('updatefound', function () {
                var newWorker = registration.installing;
                if (!newWorker) return;
                pendingWorker = newWorker;
                newWorker.addEventListener('statechange', function () {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateBanner('sw');
                    }
                });
            });
        }).catch(function () {

        });

        var reloadedForUpdate = false;
        navigator.serviceWorker.addEventListener('controllerchange', function () {
            var accepted = false;
            try { accepted = sessionStorage.getItem('ep_update_accepted') === '1'; } catch (e) {}
            if (accepted && !reloadedForUpdate) {
                reloadedForUpdate = true;
                try { sessionStorage.removeItem('ep_update_accepted'); } catch (e) {}
                window.location.reload();
            }
        });
    });
}

function checkAppUpdate() {
    if (typeof BASE_URL === 'undefined' || typeof EP_BUILD === 'undefined') return;
    if (typeof apiGet !== 'function') return;
    apiGet(BASE_URL + 'api/version.php')
        .then(function (res) {
            if (res && res.success && res.data && res.data.build && res.data.build !== EP_BUILD) {
                showUpdateBanner('build');
            }
        })
        .catch(function () {});
}

function showUpdateBanner(source) {
    var currentBuild = (typeof EP_BUILD !== 'undefined') ? EP_BUILD : 'unknown';
    var seenKey = 'ep_update_seen_' + source + '_' + currentBuild;
    try {
        if (sessionStorage.getItem(seenKey) === '1') return;
        sessionStorage.setItem(seenKey, '1');
    } catch (e) {}
    if (document.getElementById('ep-update-banner')) return;

    var banner = document.createElement('div');
    banner.id = 'ep-update-banner';
    banner.setAttribute('role', 'alert');
    banner.style.cssText =
        'position: fixed; left: 12px; right: 12px; bottom: 76px; z-index: var(--z-banner);' +
        'max-width: 480px; margin: 0 auto; background: #1e293b; color: #fff;' +
        'border-radius: 14px; padding: 14px 16px; display: flex; gap: 12px;' +
        'align-items: center; box-shadow: 0 12px 40px rgba(0,0,0,0.3);';
    banner.innerHTML =
        '<div style="flex:1;font-size:13px;line-height:1.4">A new version of ExpensePro is available.</div>' +
        '<button type="button" id="ep-update-now" style="background:#6366f1;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer">Update Now</button>' +
        '<button type="button" id="ep-update-later" style="background:transparent;color:#cbd5e1;border:1px solid #475569;border-radius:8px;padding:8px 14px;font-size:13px;cursor:pointer">Later</button>';

    document.body.appendChild(banner);
    document.getElementById('ep-update-later').addEventListener('click', function () {
        banner.remove();
    });
    document.getElementById('ep-update-now').addEventListener('click', function () {
        applyAppUpdate(source);
    });
}

var pendingWorker = null;

function applyAppUpdate(source) {
    var banner = document.getElementById('ep-update-banner');
    if (banner) banner.remove();
    if (source === 'sw' && pendingWorker) {
        try { sessionStorage.setItem('ep_update_accepted', '1'); } catch (e) {}
        try {
            pendingWorker.postMessage({ type: 'SKIP_WAITING' });

            setTimeout(function () { window.location.reload(); }, 1500);
            return;
        } catch (e) {}
    }
    window.location.reload();
}

document.addEventListener('visibilitychange', function () {
    if (!document.hidden) checkAppUpdate();
});
setInterval(checkAppUpdate, 15 * 60 * 1000);
window.addEventListener('load', function () {
    setTimeout(checkAppUpdate, 5000);
});

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
}

window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    if (!isStandalone()) {
        showInstallButton();
    }
});

function showInstallButton() {
    var btn = document.getElementById('install-btn');
    if (btn) {
        btn.classList.remove('hidden');
    }
}

function hideInstallButton() {
    var btn = document.getElementById('install-btn');
    if (btn) {
        btn.classList.add('hidden');
    }
    deferredPrompt = null;
}

function handleInstallClick() {
    if (!deferredPrompt) return;

    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(function (choiceResult) {
        if (choiceResult.outcome === 'accepted') {

        } else {

        }
        hideInstallButton();
    });
}

window.addEventListener('appinstalled', function () {
    hideInstallButton();
});

if (isStandalone()) {
    hideInstallButton();
}

window.addEventListener('online', function () {
    document.body.classList.remove('is-offline');
    if (typeof showToast === 'function') {
        showToast('You are back online!', 'success', 3000);
    }
});

window.addEventListener('offline', function () {
    document.body.classList.add('is-offline');
    if (typeof showToast === 'function') {
        showToast('You are offline. Some features may be unavailable.', 'warning', 5000);
    }
});
