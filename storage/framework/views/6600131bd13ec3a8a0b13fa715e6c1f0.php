
<?php if (! $__env->hasRenderedOnce('38832419-8f24-4ec3-b2de-7217e79f4a17')): $__env->markAsRenderedOnce('38832419-8f24-4ec3-b2de-7217e79f4a17'); ?>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <style>
        .ts-wrapper {
            position: relative;
            width: 100%;
        }
        .ts-wrapper.single .ts-control {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 2.375rem;
            box-sizing: border-box;
            padding-inline-start: 0.75rem;
            padding-inline-end: 1.75rem;
            padding-block: 0.45rem;
            background-color: #fff;
            border: 1px solid rgb(209 213 219);
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: rgb(17 24 39);
            cursor: pointer;
            overflow: hidden;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .ts-wrapper.single.focus .ts-control,
        .ts-wrapper.single.dropdown-active .ts-control {
            border-color: #1456E8;
            box-shadow: 0 0 0 3px rgb(20 86 232 / 0.15);
        }
        .ts-wrapper.single .ts-control::after {
            content: "";
            position: absolute;
            inset-inline-end: 0.85rem;
            top: 50%;
            width: 0.4rem;
            height: 0.4rem;
            border-inline-end: 1.5px solid rgb(156 163 175);
            border-block-end: 1.5px solid rgb(156 163 175);
            transform: translateY(-70%) rotate(45deg);
            pointer-events: none;
        }
        .ts-control input {
            color: inherit;
            font-size: inherit;
            background: transparent;
            min-width: 2rem;
            cursor: pointer;
        }
        .ts-control input::placeholder {
            color: rgb(156 163 175);
        }
        .ts-wrapper.single .ts-control > .item {
            color: rgb(17 24 39);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ts-dropdown {
            margin-top: 0.25rem;
            background: #fff;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            overflow: hidden;
            z-index: 60;
            font-size: 0.875rem;
            text-align: start;
        }
        .ts-dropdown .ts-dropdown-content {
            max-height: 15rem;
            overflow-y: auto;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            color: rgb(55 65 81);
        }
        .ts-dropdown .option.active,
        .ts-dropdown .option:hover {
            background-color: rgb(239 246 255);
            color: #1456E8;
        }
        .ts-dropdown .no-results,
        .ts-dropdown .optgroup-header {
            padding: 0.5rem 0.75rem;
            color: rgb(156 163 175);
            font-size: 0.8125rem;
        }
        .ts-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            clip-path: inset(50%) !important;
            height: 1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
            white-space: nowrap !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-ajax-select]').forEach(function (el) {
                if (el.tomselect || !window.TomSelect) {
                    return;
                }
                var url = el.getAttribute('data-ajax-url');
                new TomSelect(el, {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: [],
                    create: false,
                    allowEmptyOption: true,
                    placeholder: el.getAttribute('data-ajax-placeholder') || '',
                    load: function (query, callback) {
                        if (!query || query.length < 2) {
                            callback();
                            return;
                        }
                        fetch(url + '?q=' + encodeURIComponent(query))
                            .then(function (res) { return res.json(); })
                            .then(function (json) { callback(json); })
                            .catch(function () { callback(); });
                    },
                });
            });
        });
    </script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\factory\resources\views/partials/ajax-select-assets.blade.php ENDPATH**/ ?>