<?php

?>

    </div>
            
    </div>
    
    
    <div id="ep-rotate-hint" role="note">
        <p>Rotate your device to portrait<br>for the best experience.</p>
    </div>

    
    <div id="ep-confirm-modal" class="hidden fixed inset-0 ep-layer-modal">
        <div class="absolute inset-0 bg-white/80 backdrop-blur-md modal-backdrop" data-ep-confirm-backdrop></div>
        <div class="modal-shell">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center modal-content modal-panel">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <h3 id="ep-confirm-title" class="text-lg font-bold text-slate-900 mb-2">Are you sure?</h3>
                <p id="ep-confirm-message" class="text-sm text-slate-500 mb-6">This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button id="ep-confirm-cancel" type="button"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button id="ep-confirm-ok" type="button"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium transition-all shadow-md active:scale-95">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <script src="<?= assetUrl('assets/js/loader.js') ?>"></script>

    
    <script src="<?= assetUrl('assets/js/core/app-config.js') ?>"></script>
    <script src="<?= assetUrl('assets/js/core/api-client.js') ?>"></script>
    <script src="<?= assetUrl('assets/js/core/error-handler.js') ?>"></script>
    <script src="<?= assetUrl('assets/js/core/validation.js') ?>"></script>
    <script src="<?= assetUrl('assets/js/core/finance.js') ?>"></script>
    <script src="<?= assetUrl('assets/js/core/form-state.js') ?>"></script>

    
    <script src="<?= assetUrl('assets/js/app.js') ?>"></script>

    
    <script src="<?= assetUrl('assets/js/vendor.js') ?>"></script>

    
    <?php require_once __DIR__ . '/../layouts/scripts.php'; ?>

    
    <script src="<?= assetUrl('assets/js/pwa.js') ?>"></script>

</body>
</html>
