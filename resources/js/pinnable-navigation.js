(() => {
    const namespace = window.PinnableNavigation ??= {};

    if (namespace.booted) {
        namespace.refresh?.(document);
        return;
    }

    namespace.booted = true;

    namespace.getPinnedKeys = function (storageKey) {
        if (!storageKey) {
            return [];
        }

        try {
            const value = JSON.parse(localStorage.getItem(storageKey) ?? '[]');

            return Array.isArray(value) ? value : [];
        } catch (error) {
            return [];
        }
    };

    namespace.setPinnedKeys = function (storageKey, keys) {
        if (!storageKey) {
            return;
        }

        localStorage.setItem(storageKey, JSON.stringify([...new Set(keys)]));
    };

    namespace.togglePinnedKey = function (storageKey, navigationKey) {
        const keys = namespace.getPinnedKeys(storageKey);
        const nextKeys = keys.includes(navigationKey)
            ? keys.filter((key) => key !== navigationKey)
            : [...keys, navigationKey];

        namespace.setPinnedKeys(storageKey, nextKeys);
    };

    namespace.decodeIcon = function (button, state) {
        const encoded = state === 'pinned' ? button.dataset.filledIcon : button.dataset.outlinedIcon;

        if (!encoded) {
            return null;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = window.atob(encoded).trim();

        return wrapper.firstElementChild;
    };

    namespace.updateButtonState = function (button, isPinned) {
        if (!button) {
            return;
        }

        const label = isPinned ? button.dataset.unpinLabel : button.dataset.pinLabel;
        const icon = namespace.decodeIcon(button, isPinned ? 'pinned' : 'unpinned');
        const currentIcon = button.querySelector('svg');

        button.dataset.pinned = isPinned ? '1' : '0';

        if (label) {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        }

        if (icon && currentIcon) {
            currentIcon.replaceWith(icon);
        }
    };

    namespace.updateItemState = function (item, isPinned) {
        if (!item) {
            return;
        }

        item.dataset.pinned = isPinned ? '1' : '0';

        item.querySelectorAll('[data-localstorage-pin-button]').forEach((button) => {
            namespace.updateButtonState(button, isPinned);
        });
    };

    namespace.syncGroupedBorder = function (item, isFirst, isLast) {
        const border = item.querySelector('.fi-sidebar-item-grouped-border');

        if (!border) {
            return;
        }

        border.querySelectorAll('.fi-sidebar-item-grouped-border-part-not-first').forEach((element) => element.remove());
        border.querySelectorAll('.fi-sidebar-item-grouped-border-part-not-last').forEach((element) => element.remove());

        if (!isFirst) {
            const before = document.createElement('div');
            before.className = 'fi-sidebar-item-grouped-border-part-not-first';
            border.prepend(before);
        }

        if (!isLast) {
            const after = document.createElement('div');
            after.className = 'fi-sidebar-item-grouped-border-part-not-last';
            const stem = border.querySelector('.fi-sidebar-item-grouped-border-part');

            if (stem) {
                stem.before(after);
            } else {
                border.appendChild(after);
            }
        }
    };

    namespace.refreshSidebar = function (nav) {
        const storageKey = nav.dataset.localstorageKey;
        const pinnedKeys = namespace.getPinnedKeys(storageKey);
        const pinnedGroup = nav.querySelector('[data-localstorage-pinned-group]');
        const pinnedItemsContainer = pinnedGroup?.querySelector('[data-localstorage-pinned-items]');
        const pinnedDropdownContainer = pinnedGroup?.querySelector('[data-localstorage-pinned-dropdown-items]');

        if (!pinnedGroup || !pinnedItemsContainer) {
            return;
        }

        pinnedItemsContainer.innerHTML = '';

        if (pinnedDropdownContainer) {
            pinnedDropdownContainer.innerHTML = '';
        }

        const sourceItems = [...nav.querySelectorAll('.fi-sidebar-item[data-navigation-key][data-pinnable="1"]')]
            .filter((item) => !item.closest('[data-localstorage-pinned-group]'));

        sourceItems.forEach((item) => {
            const navigationKey = item.dataset.navigationKey;
            const isPinned = pinnedKeys.includes(navigationKey);

            namespace.updateItemState(item, isPinned);

            if (!isPinned) {
                return;
            }

            const clone = item.cloneNode(true);
            clone.dataset.pinnedClone = '1';
            namespace.updateItemState(clone, true);
            pinnedItemsContainer.appendChild(clone);

            if (pinnedDropdownContainer) {
                const label = item.querySelector('.fi-sidebar-item-label')?.textContent?.trim() ?? '';
                const linkEl = item.querySelector('a[href]');
                const url = linkEl?.href ?? '#';

                const a = document.createElement('a');
                a.href = url;
                a.className = 'fi-dropdown-list-item';

                const span = document.createElement('span');
                span.className = 'fi-dropdown-list-item-label';
                span.textContent = label;

                a.appendChild(span);
                pinnedDropdownContainer.appendChild(a);
            }
        });

        [...pinnedItemsContainer.children].forEach((item, index, items) => {
            namespace.syncGroupedBorder(item, index === 0, index === items.length - 1);
        });

        pinnedGroup.hidden = pinnedItemsContainer.children.length === 0;
    };

    namespace.refresh = function (root = document) {
        root.querySelectorAll('[data-persistence-mode="localstorage"][data-localstorage-key]').forEach((nav) => {
            namespace.refreshSidebar(nav);
        });

        root.querySelectorAll('[data-localstorage-page-pin]').forEach((button) => {
            namespace.updateButtonState(
                button,
                namespace.getPinnedKeys(button.dataset.localstorageKey).includes(button.dataset.navigationKey),
            );
        });
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-localstorage-pin-button], [data-localstorage-page-pin]');

        if (!button) {
            return;
        }

        const navigationKey = button.dataset.navigationKey;
        const storageKey = button.dataset.localstorageKey
            ?? button.closest('[data-localstorage-key]')?.dataset.localstorageKey;

        if (!navigationKey || !storageKey) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        namespace.togglePinnedKey(storageKey, navigationKey);
        namespace.refresh(document);
    });

    document.addEventListener('DOMContentLoaded', () => namespace.refresh(document));
    document.addEventListener('livewire:navigated', () => namespace.refresh(document));

    document.addEventListener('livewire:init', () => {
        if (typeof window.Livewire?.hook !== 'function') {
            return;
        }

        window.Livewire.hook('morph.updated', ({ el }) => namespace.refresh(el));
    });

    namespace.refresh(document);
})();
