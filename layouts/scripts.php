<?php

if (!empty($pageScripts) && is_array($pageScripts)):
    foreach ($pageScripts as $script): ?>
            <script src="<?= assetUrl('assets/js/' . $script) ?>"></script>
    <?php endforeach;
endif; ?>
