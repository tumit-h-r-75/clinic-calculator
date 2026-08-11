(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-derma-roi-copy-shortcode]');
        if (!button) {
            return;
        }

        const box = button.closest('.derma-roi-shortcode-box');
        const code = box ? box.querySelector('code') : document.getElementById('derma-roi-shortcode-code');
        if (!code || !navigator.clipboard) {
            return;
        }

        navigator.clipboard.writeText(code.textContent).then(function () {
            button.textContent = 'Copied';
            window.setTimeout(function () {
                button.textContent = 'Copy to Clipboard';
            }, 1600);
        });
    });
}());
