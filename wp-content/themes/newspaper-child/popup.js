// ===== KATEGORI DROPDOWN HELPER =====
(function () {
    function removeCategoryDropdown() {
        const existing = document.querySelector('.category-dropdown');
        if (existing) existing.remove();
    }

    function createDropdown(createEditListItem, onSelect, excludeSlugs) {
        const dropdown = document.createElement('div');
        dropdown.className = 'category-dropdown';

        if (!window.listCategories || !window.listCategories.length) {
            dropdown.textContent = 'Ingen kategorier';
            return dropdown;
        }

        const excluded = Array.isArray(excludeSlugs) ? excludeSlugs : [];

        let hasItems = false;

        window.listCategories.forEach(cat => {
            // Skip kategorier som elementet allerede er med i:
            if (excluded.includes(cat.slug)) {
                return;
            }

            hasItems = true;

            const item = document.createElement('div');
            item.className = 'category-dropdown-item';
            item.textContent = "Flyt til '" + cat.name + "'";
            item.dataset.slug = cat.slug;

            item.addEventListener('click', function (e) {
                e.stopPropagation();
                removeCategoryDropdown();
                if (typeof onSelect === 'function') {
                    console.log("CLICK", "FALSE");
                    onSelect(cat, false);
                }
            });

            dropdown.appendChild(item);
        });

        window.listCategories.forEach(cat => {
            // Skip kategorier som elementet ikke er med i:
            if (!excluded.includes(cat.slug)) {
                return;
            }

            hasItems = true;

            const item = document.createElement('div');
            item.className = 'category-dropdown-item';
            item.textContent = "Fjern fra '" + cat.name + "'";
            item.dataset.slug = cat.slug;
            item.dataset.remove = true;
            item.style = "color:red";

            item.addEventListener('click', function (e) {
                e.stopPropagation();
                removeCategoryDropdown();
                if (typeof onSelect === 'function') {
                    onSelect(cat, true);
                }
            });

            dropdown.appendChild(item);
        });

        if (createEditListItem) {
            const item = document.createElement('div');
            item.className = 'category-dropdown-item';
            item.textContent = "Rediger lister";

            item.addEventListener('click', function (e) {
                e.stopPropagation();
                removeCategoryDropdown();
                if (typeof onSelect === 'function') {
                    onSelect(null);
                }
            });

            dropdown.appendChild(item);
        }




        if (!hasItems) {
            dropdown.textContent = 'Ingen flere lister at flytte til';
        }

        return dropdown;
    }

    /**
     * Gør et element "kategori-klikbart"
     * element: DOM-node eller jQuery-objekt
     * onSelect(cat, el): callback når der vælges en kategori
     */
    function attachCategoryDropdown(element, createEditListItem, onSelect) {
        // Support jQuery-objekter
        const el = element.jquery ? element[0] : element;

        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Fjern evt. tidligere dropdown
            removeCategoryDropdown();

            // Find postId via span[data-post-id] inde i elementet
            let excludeSlugs = [];
            const span = el.querySelector('span[data-post-id]');
            let postId = null;
            if (span) {
                postId = span.getAttribute('data-post-id');
                if (
                    window.postListCategories &&
                    window.postListCategories[postId]
                ) {
                    excludeSlugs = window.postListCategories[postId];
                }
            }

            const rect = el.getBoundingClientRect();
            const dropdown = createDropdown(createEditListItem, function (cat, remove) {
                if (typeof onSelect === 'function') {
                    onSelect(cat, postId, remove);
                } else {
                    // Default-adfærd: sæt tekst + data-slug på elementet
                    el.textContent = cat.name;
                    el.dataset.slug = cat.slug;
                }
            }, excludeSlugs);

            document.body.appendChild(dropdown);

            // Placér lige under elementet
            const top = rect.bottom + window.scrollY;
            const left = rect.left + window.scrollX;

            dropdown.style.position = 'absolute';
            dropdown.style.top = top + 'px';
            dropdown.style.left = left + 'px';

            // Luk når der klikkes udenfor
            setTimeout(() => {
                document.addEventListener('click', function handler(ev) {
                    if (!ev.target.closest('.category-dropdown')) {
                        removeCategoryDropdown();
                        document.removeEventListener('click', handler);
                    }
                });
            }, 0);
        });
    }

    // Gør funktionen global
    window.attachCategoryDropdown = attachCategoryDropdown;
})();
