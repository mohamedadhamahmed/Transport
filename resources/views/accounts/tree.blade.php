<x-app-layout>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                        </svg>
                    </span>
                    <h2 class="text-white font-bold text-lg">{{ __('accounts.tree_title') }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('accounts.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition whitespace-nowrap">
                        {{ __('accounts.tree_view_as_list') }}
                    </a>
                    <a href="{{ route('accounts.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-white/10 hover:bg-white/15 border border-white/10 transition whitespace-nowrap">
                        + {{ __('accounts.new_account') }}
                    </a>
                </div>
            </div>

            @include('partials.sweet-alert-flash')

            <div id="tree-wrapper" class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <input type="text" id="tree-search" autocomplete="off" placeholder="{{ __('accounts.search_placeholder') }}"
                           class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                    <div class="flex items-center gap-2">
                        {{-- زر إظهار/إخفاء الأرصدة المالية - افتراضياً مخفية لتظل الشجرة نقية وهيكلية --}}
                        <button type="button" id="toggle-balances-btn"
                                class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200 transition whitespace-nowrap flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span id="balances-btn-text">{{ __('accounts.show_balances') }}</span>
                        </button>

                        <button type="button" id="collapse-all-btn" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition whitespace-nowrap">
                            {{ __('accounts.collapse_all') }}
                        </button>
                    </div>
                </div>

                <div id="tree-table-header" class="px-2 py-2.5 border-b border-gray-100 hidden sm:flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase tracking-wide bg-gray-50/60">
                    <span class="w-6 shrink-0"></span>
                    <span class="w-4 shrink-0"></span>
                    <span class="min-w-[3.5rem] shrink-0 text-center">{{ __('accounts.account_number') }}</span>
                    <span class="flex-1">{{ __('accounts.name') }}</span>
                    <div class="account-balances items-center gap-3 shrink-0 hidden">
                        <span class="w-24 text-end shrink-0">{{ __('accounts.debtor') }}</span>
                        <span class="w-24 text-end shrink-0">{{ __('accounts.creditor') }}</span>
                        <span class="w-24 text-end shrink-0">{{ __('accounts.current_balance') }}</span>
                    </div>
                    <span class="w-32 shrink-0 text-center">{{ __('accounts.actions') }}</span>
                </div>

                <div id="tree-container">
                    <div id="tree-root" class="p-2">
                        @forelse ($roots as $node)
                            @include('accounts.tree-node', ['account' => $node['account'], 'depth' => 0, 'hasChildren' => $node['hasChildren']])
                        @empty
                            <div class="px-4 py-10 text-center text-gray-400">
                                {{ __('accounts.no_accounts_found') }}
                            </div>
                        @endforelse
                    </div>

                    {{-- نتائج البحث (AJAX - راجع AccountController::treeSearch)
                         بتحل محل الشجرة العادية مؤقتًا لحد ما مربع البحث يتفضى. --}}
                    <div id="tree-search-results" class="p-2 hidden"></div>

                    <div id="tree-empty-search" class="px-4 py-10 text-center text-gray-400 hidden">
                        {{ __('accounts.no_accounts_found') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .toggle-icon { transition: transform .18s ease-in-out; }
        [dir="rtl"] .toggle-btn[aria-expanded="false"] .toggle-icon { transform: rotate(180deg); }
        [dir="ltr"] .toggle-btn[aria-expanded="false"] .toggle-icon { transform: rotate(0deg); }
        .toggle-btn[aria-expanded="true"] .toggle-icon { transform: rotate(90deg) !important; }
        .toggle-btn[disabled] { opacity: .5; cursor: wait; }

        /* إظهار الأرصدة عند تفعيلها فقط */
        .tree-show-balances .account-balances {
            display: flex !important;
        }
    </style>

    <script>
    (function () {
        const treeContainer = document.getElementById('tree-container');
        const treeRoot = document.getElementById('tree-root');
        const searchInput = document.getElementById('tree-search');
        const searchResultsBox = document.getElementById('tree-search-results');
        const emptySearchBox = document.getElementById('tree-empty-search');
        const treeWrapper = document.getElementById('tree-wrapper');
        const toggleBalancesBtn = document.getElementById('toggle-balances-btn');
        const balancesBtnText = document.getElementById('balances-btn-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function setExpanded(node, expanded) {
            const btn = node.querySelector(':scope > .tree-row .toggle-btn');
            const childrenWrap = node.querySelector(':scope > .children-wrap');
            const folderClosed = node.querySelector(':scope > .tree-row .folder-closed');
            const folderOpen = node.querySelector(':scope > .tree-row .folder-open');

            if (btn) btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (childrenWrap) childrenWrap.classList.toggle('hidden', !expanded);
            if (folderClosed && folderOpen) {
                folderClosed.classList.toggle('hidden', expanded);
                folderOpen.classList.toggle('hidden', !expanded);
            }
        }

        /**
         * بتجيب أبناء حساب معيّن بـ AJAX أول مرة يتفتح بس (راجع
         * AccountController::treeChildren) - عشان شجرة فيها عشرات آلاف
         * الحسابات متتحملش كلها دفعة واحدة، بس اللي المستخدم فعلاً فتحه.
         * بعد أول تحميل، btn.dataset.loaded بيبقى "1" فمنعملش الطلب تاني.
         */
        function loadChildren(btn, node, childrenWrap) {
            btn.disabled = true;
            childrenWrap.classList.remove('hidden');
            childrenWrap.innerHTML = '<div class="py-2 px-2 text-xs text-gray-400">' + @json(__('accounts.loading')) + '</div>';

            const nextDepth = (parseInt(btn.dataset.depth, 10) || 0) + 1;

            fetch(btn.dataset.childrenUrl + '?depth=' + nextDepth, {
                headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((res) => {
                    if (!res.ok) throw new Error('load_children_failed');
                    return res.text();
                })
                .then((html) => {
                    childrenWrap.innerHTML = html;
                    btn.dataset.loaded = '1';
                    setExpanded(node, true);
                })
                .catch(() => {
                    childrenWrap.innerHTML = '<div class="py-2 px-2 text-xs text-red-500">' + @json(__('accounts.load_children_failed')) + '</div>';
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'error',
                            title: @json(__('accounts.load_children_failed')),
                            showConfirmButton: false,
                            timer: 2500,
                        });
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                });
        }

        function toggleNode(node, btn) {
            const childrenWrap = node.querySelector(':scope > .children-wrap');
            if (!childrenWrap) return;

            const expanded = btn.getAttribute('aria-expanded') === 'true';
            const willExpand = !expanded;

            if (willExpand && btn.dataset.loaded === '0') {
                loadChildren(btn, node, childrenWrap);
                return;
            }

            setExpanded(node, willExpand);
        }

        // النقر على الحساب الأب (السطر بالكامل أو السهم أو الاسم أو الأيقونة) يفتح ما تحته
        treeContainer.addEventListener('click', (e) => {
            // استبعاد النقر على العناصر التفاعلية (الروابط، سويتش التفعيل، الأزرار)
            if (e.target.closest('a, input, select, textarea, label.account-active-switch')) {
                return;
            }

            const row = e.target.closest('.tree-row');
            if (!row) return;

            const node = row.closest('.tree-node');
            if (!node) return;

            const btn = row.querySelector('.toggle-btn');
            if (!btn || btn.disabled) return;

            toggleNode(node, btn);
        });

        // زر تبديل إظهار/إخفاء الأرصدة المالية
        function updateBalancesVisibility(show) {
            if (show) {
                treeWrapper.classList.add('tree-show-balances');
                balancesBtnText.textContent = @json(__('accounts.hide_balances'));
                try { localStorage.setItem('accounts_tree_show_balances', '1'); } catch (err) {}
            } else {
                treeWrapper.classList.remove('tree-show-balances');
                balancesBtnText.textContent = @json(__('accounts.show_balances'));
                try { localStorage.setItem('accounts_tree_show_balances', '0'); } catch (err) {}
            }
        }

        let savedBalancesState = false;
        try {
            savedBalancesState = localStorage.getItem('accounts_tree_show_balances') === '1';
        } catch (err) {}
        updateBalancesVisibility(savedBalancesState);

        toggleBalancesBtn.addEventListener('click', () => {
            const isCurrentlyShown = treeWrapper.classList.contains('tree-show-balances');
            updateBalancesVisibility(!isCurrentlyShown);
        });

        // سويتش تفعيل/تعطيل الحساب مباشرة (AJAX بدون إعادة تحميل
        // الصفحة) - على مستوى الحاوية الكلية عشان يشتغل مع الشجرة
        // العادية ونتائج البحث المحمّلة ديناميكيًا سوا.
        treeContainer.addEventListener('change', (e) => {
            const checkbox = e.target.closest('.account-active-checkbox');
            if (!checkbox) return;

            const previousState = !checkbox.checked;
            checkbox.disabled = true;

            fetch(checkbox.dataset.toggleUrl, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
                .then((res) => {
                    if (!res.ok) throw new Error('toggle_failed');
                    return res.json();
                })
                .then((data) => {
                    checkbox.checked = !!data.active;
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        });
                    }
                })
                .catch(() => {
                    checkbox.checked = previousState;
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'error',
                            title: @json(__('accounts.toggle_failed')),
                            showConfirmButton: false,
                            timer: 2500,
                        });
                    }
                })
                .finally(() => {
                    checkbox.disabled = false;
                });
        });

        document.getElementById('collapse-all-btn').addEventListener('click', () => {
            treeRoot.querySelectorAll('.tree-node').forEach((node) => setExpanded(node, false));
        });

        // البحث بقى بـ AJAX (راجع AccountController::treeSearch) بدل
        // فلترة العميل على كل الشجرة - مع شجرة فيها عشرات آلاف الحسابات
        // مش كل الحسابات محمّلة في المتصفح أصلاً، فمفيش حاجة تتفلتر.
        let searchDebounceTimer = null;
        let searchRequestToken = 0;

        function resetToTreeView() {
            searchResultsBox.classList.add('hidden');
            searchResultsBox.innerHTML = '';
            emptySearchBox.classList.add('hidden');
            treeRoot.classList.remove('hidden');
        }

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            clearTimeout(searchDebounceTimer);

            if (query === '') {
                resetToTreeView();
                return;
            }

            treeRoot.classList.add('hidden');
            emptySearchBox.classList.add('hidden');

            const requestToken = ++searchRequestToken;

            searchDebounceTimer = setTimeout(() => {
                fetch('{{ route('accounts.tree.search') }}?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then((res) => res.text())
                    .then((html) => {
                        if (requestToken !== searchRequestToken) return; // نتيجة بحث قديمة اتأخرت - نتجاهلها.

                        searchResultsBox.innerHTML = html;
                        const hasResults = searchResultsBox.querySelector('.tree-node') !== null;
                        searchResultsBox.classList.toggle('hidden', !hasResults);
                        emptySearchBox.classList.toggle('hidden', hasResults);
                    });
            }, 300);
        });
    })();
    </script>
</x-app-layout>
