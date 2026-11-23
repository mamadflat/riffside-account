(function () {
    'use strict';
    const root = document.querySelector('[data-ra-navigation-admin]');
    if (!root) return;
    const editor = root.querySelector('[data-ra-menu-editor]');
    const preview = root.querySelector('[data-ra-menu-preview]');
    const status = root.querySelector('[data-ra-menu-save]');
    const shortcuts = root.querySelector('[data-ra-preview-shortcuts]');
    const previewItems = new Map(Array.from(preview.children, item => [item.dataset.raPreviewKey, item]));

    function sync() {
        const visible = new Set();
        for (const row of editor.children) {
            const key = row.dataset.raMenuKey;
            const item = previewItems.get(key);
            item.hidden = !row.querySelector('input[type=checkbox]').checked;
            if (!item.hidden) visible.add(key);
            preview.append(item);
            const up = row.querySelector('[data-ra-menu-move=up]');
            const down = row.querySelector('[data-ra-menu-move=down]');
            if (up) up.setAttribute('aria-disabled', String(!row.previousElementSibling));
            if (down) down.setAttribute('aria-disabled', String(!row.nextElementSibling || row.nextElementSibling.hasAttribute('data-ra-fixed')));
        }
        for (const item of root.querySelectorAll('[data-ra-if-menu]')) {
            item.hidden = !visible.has(item.dataset.raIfMenu);
        }
        shortcuts.hidden = !shortcuts.querySelector(':scope > :not([hidden])');
    }

    editor.addEventListener('click', event => {
        const button = event.target.closest('[data-ra-menu-move]');
        if (!button || button.getAttribute('aria-disabled') === 'true') return;
        const row = button.closest('[data-ra-menu-key]');
        if (button.dataset.raMenuMove === 'up') row.previousElementSibling.before(row);
        else row.nextElementSibling.after(row);
        sync();
        button.focus();
        status.textContent = 'ترتیب تغییر کرد. برای اعمال، منو را ذخیره کن.';
    });

    editor.addEventListener('change', () => {
        sync();
        status.textContent = 'تغییرات هنوز ذخیره نشده‌اند.';
    });

    root.querySelector('[data-ra-preview-theme]').addEventListener('change', event => {
        root.querySelector('.ra-navigation-preview').dataset.raTheme = event.target.value;
    });
    root.querySelector('[data-ra-mobile-preview]').addEventListener('toggle', event => {
        if (!event.currentTarget.open) return;
        const iframe = event.currentTarget.querySelector('iframe');
        if (!iframe.hasAttribute('src')) iframe.src = iframe.dataset.src;
    });
    sync();
}());
