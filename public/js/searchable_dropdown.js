/**
 * Searchable Dropdown Combobox Widget
 * Transforms any <select class="searchable-select"> into an interactive searchable dropdown
 * with autocomplete suggestions and keyboard navigation while retaining the native select.
 */
document.addEventListener('DOMContentLoaded', function () {
    initSearchableDropdowns();
});

// Single delegated document click listener (BUG-FA-04)
document.addEventListener('click', function (e) {
    document.querySelectorAll('.searchable-combobox-wrap.open').forEach(function (wrap) {
        if (!wrap.contains(e.target)) {
            if (typeof wrap._closeDropdown === 'function') {
                wrap._closeDropdown();
            } else {
                wrap.classList.remove('open');
            }
        }
    });
});

function initSearchableDropdowns() {
    var selects = document.querySelectorAll('select.searchable-select, select[data-searchable="true"]');
    selects.forEach(function (select) {
        if (select.dataset.searchableInitialized === 'true') return;
        select.dataset.searchableInitialized = 'true';

        // BUG-TL-03: Remove HTML5 required constraint from hidden select so browser doesn't block with "not focusable"
        var isRequired = select.required;
        if (isRequired) {
            select.required = false;
        }

        // Hide native select
        select.style.display = 'none';

        // Create combobox container
        var wrap = document.createElement('div');
        wrap.className = 'searchable-combobox-wrap';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'searchable-combobox-input';
        input.placeholder = select.getAttribute('placeholder') || 'Ketik untuk mencari atau klik untuk memilih naskah...';
        input.autocomplete = 'off';
        if (isRequired) {
            input.required = true;
        }

        var arrow = document.createElement('span');
        arrow.className = 'searchable-combobox-arrow';
        arrow.innerHTML = '&#9662;'; // ▾

        var dropdown = document.createElement('div');
        dropdown.className = 'searchable-combobox-list';

        wrap.appendChild(input);
        wrap.appendChild(arrow);
        wrap.appendChild(dropdown);
        select.parentNode.insertBefore(wrap, select);

        var activeIndex = -1;

        // Populate options from select
        function renderOptions(filterText) {
            dropdown.innerHTML = '';
            activeIndex = -1;
            var query = (filterText || '').trim().toLowerCase();
            var count = 0;

            var options = select.querySelectorAll('option');
            options.forEach(function (opt) {
                var val = opt.value;
                var label = opt.textContent.trim();
                var sub = opt.getAttribute('data-sub') || '';
                var fullText = (label + ' ' + sub).toLowerCase();

                // BUG-FA-02: Include empty placeholder option so selection can be cleared
                if (!val) {
                    if (query === '') {
                        count++;
                        var emptyItem = document.createElement('div');
                        emptyItem.className = 'searchable-combobox-option opt-placeholder';
                        emptyItem.dataset.value = '';
                        var emptyTitle = document.createElement('div');
                        emptyTitle.className = 'opt-title';
                        emptyTitle.style.color = '#94a3b8';
                        emptyTitle.textContent = label || '-- Kosongkan Pilihan --';
                        emptyItem.appendChild(emptyTitle);
                        emptyItem.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                        });
                        emptyItem.addEventListener('click', function (e) {
                            e.stopPropagation();
                            chooseOption('', '');
                        });
                        dropdown.appendChild(emptyItem);
                    }
                    return;
                }

                if (query === '' || fullText.indexOf(query) !== -1) {
                    count++;
                    var item = document.createElement('div');
                    item.className = 'searchable-combobox-option';
                    item.dataset.value = val;

                    var titleDiv = document.createElement('div');
                    titleDiv.className = 'opt-title';
                    titleDiv.textContent = label;
                    item.appendChild(titleDiv);

                    if (sub) {
                        var subDiv = document.createElement('div');
                        subDiv.className = 'opt-sub';
                        subDiv.textContent = sub;
                        item.appendChild(subDiv);
                    }

                    item.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                    });
                    item.addEventListener('click', function (e) {
                        e.stopPropagation();
                        chooseOption(val, label);
                    });

                    dropdown.appendChild(item);
                }
            });

            if (count === 0) {
                var empty = document.createElement('div');
                empty.className = 'searchable-combobox-empty';
                empty.textContent = 'Tidak ada naskah yang cocok dengan pencarian.';
                dropdown.appendChild(empty);
            }
        }

        function chooseOption(val, label) {
            select.value = val;
            input.value = label;
            wrap.classList.remove('open');
            // When option is selected or cleared, reset customValidity so form submission is not blocked
            input.setCustomValidity('');
            var event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
        }

        function closeDropdown() {
            wrap.classList.remove('open');
            // BUG-FA-03: Handle blur/outside click smoothly without unprompted wipe
            var cur = select.options[select.selectedIndex];
            var curVal = cur ? cur.value : '';
            var curText = (cur && curVal) ? cur.textContent.trim() : '';

            if (input.value.trim() === '') {
                if (select.value !== '') {
                    select.value = '';
                    var event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                }
                // When input is cleared or optional, ensure customValidity is reset to '' so form submission is not blocked
                input.setCustomValidity('');
            } else if (!curVal || input.value.trim().toLowerCase() !== curText.toLowerCase()) {
                // Bug 21: If user types a custom query and does not select an option, do not silently submit stale previous selection; clear or validate.
                var matched = null;
                select.querySelectorAll('option').forEach(function (opt) {
                    if (opt.value && opt.textContent.trim().toLowerCase() === input.value.trim().toLowerCase()) {
                        matched = opt;
                    }
                });
                if (matched) {
                    chooseOption(matched.value, matched.textContent.trim());
                } else {
                    select.value = '';
                    if (isRequired) {
                        input.setCustomValidity('Harap pilih salah satu naskah.');
                    } else {
                        input.setCustomValidity('');
                    }
                    var event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                }
            } else {
                input.value = curText;
                input.setCustomValidity('');
            }
        }
        wrap._closeDropdown = closeDropdown;

        // Set initial value if an option is selected
        var selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            input.value = selectedOpt.textContent.trim();
        }

        // Event listeners
        input.addEventListener('focus', function () {
            renderOptions('');
            wrap.classList.add('open');
        });

        input.addEventListener('input', function () {
            renderOptions(input.value);
            wrap.classList.add('open');
            var cur = select.options[select.selectedIndex];
            var curVal = cur ? cur.value : '';
            var curText = (cur && curVal) ? cur.textContent.trim() : '';
            if (input.value.trim() === '') {
                // When input is cleared or optional, ensure customValidity is reset to '' so form submission is not blocked
                select.value = '';
                input.setCustomValidity('');
            } else if (input.value.trim().toLowerCase() !== curText.toLowerCase()) {
                // Bug 21: Invalidate / clear stale selection immediately when query changes
                select.value = '';
                if (isRequired) {
                    input.setCustomValidity('Harap pilih salah satu naskah.');
                } else {
                    input.setCustomValidity('');
                }
            } else {
                input.setCustomValidity('');
            }
        });

        // BUG-FA-10: Close dropdown on Tab / focusout (delay prevents race condition with option click)
        wrap.addEventListener('focusout', function (e) {
            if (!wrap.contains(e.relatedTarget)) {
                setTimeout(function () {
                    if (!wrap.contains(document.activeElement)) {
                        closeDropdown();
                    }
                }, 150);
            }
        });

        // Keyboard navigation
        input.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('.searchable-combobox-option');
            if (!wrap.classList.contains('open') && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                renderOptions(input.value);
                wrap.classList.add('open');
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length === 0) return;
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length === 0) return;
                activeIndex = (activeIndex <= 0) ? (items.length - 1) : (activeIndex - 1);
                updateActiveItem(items);
            } else if (e.key === 'Enter') {
                // BUG-FA-01 / Bug 7: Prevent default Enter submission; if query is empty and activeIndex === -1, keep current selection or close dropdown
                if (wrap.classList.contains('open')) {
                    e.preventDefault();
                    var query = (input.value || '').trim();
                    if (query === '' && activeIndex === -1) {
                        closeDropdown();
                        return;
                    }
                    if (items.length > 0) {
                        var chosenIdx = -1;
                        if (activeIndex >= 0 && activeIndex < items.length) {
                            chosenIdx = activeIndex;
                        } else if (query !== '') {
                            for (var i = 0; i < items.length; i++) {
                                if (items[i].dataset.value) {
                                    chosenIdx = i;
                                    break;
                                }
                            }
                        }
                        if (chosenIdx >= 0) {
                            var chosen = items[chosenIdx];
                            var chosenVal = chosen.dataset.value || '';
                            var titleEl = chosen.querySelector('.opt-title');
                            var chosenLabel = titleEl ? titleEl.textContent.trim() : chosen.textContent.trim();
                            chooseOption(chosenVal, chosenVal ? chosenLabel : '');
                        } else {
                            closeDropdown();
                        }
                    }
                }
            } else if (e.key === 'Tab') {
                // Bug 8: When activeIndex >= 0 and user presses Tab, commit the highlighted option before focus moves
                if (wrap.classList.contains('open') && activeIndex >= 0 && activeIndex < items.length) {
                    var chosen = items[activeIndex];
                    var chosenVal = chosen.dataset.value || '';
                    var titleEl = chosen.querySelector('.opt-title');
                    var chosenLabel = titleEl ? titleEl.textContent.trim() : chosen.textContent.trim();
                    chooseOption(chosenVal, chosenVal ? chosenLabel : '');
                }
                closeDropdown();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        function updateActiveItem(items) {
            items.forEach(function (it, idx) {
                if (idx === activeIndex) {
                    it.classList.add('active');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('active');
                }
            });
        }

        wrap.addEventListener('click', function (e) {
            if (e.target === arrow) {
                if (wrap.classList.contains('open')) {
                    closeDropdown();
                } else {
                    renderOptions('');
                    wrap.classList.add('open');
                    input.focus();
                }
            }
        });
    });
}
