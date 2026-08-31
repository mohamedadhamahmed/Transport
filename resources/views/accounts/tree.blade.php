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

            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl">

                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <input type="text" id="tree-search" autocomplete="off" placeholder="{{ __('accounts.search_placeholder') }}"
                           class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8] text-sm">
                    <div class="flex items-center gap-2">
                        <button type="button" id="expand-all-btn" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition whitespace-nowrap">
                            {{ __('accounts.expand_all') }}
                        </button>
                        <button type="button" id="collapse-all-btn" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition whitespace-nowrap">
                            {{ __('accounts.collapse_all') }}
                        </button>
                    </div>
                </div>

                <div class="px-2 py-2 border-b border-gray-100 hidden sm:flex items-center gap-2 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
                    <span class="w-5 shrink-0"></span>
                    <span class="w-16 shrink-0">{{ __('accounts.account_number') }}</span>
                    <span class="flex-1">{{ __('accounts.name') }}</span>
                    <span class="w-24 text-end shrink-0">{{ __('accounts.debtor') }}</span>
                    <span class="w-24 text-end shrink-0">{{ __('accounts.creditor') }}</span>
                    <span class="w-24 text-end shrink-0">{{ __('accounts.current_balance') }}</span>
                    <span class="w-[68px] shrink-0"></span>
                </div>

                <div id="tree-root" class="p-2">
                    @forelse ($roots as $node)
                        @include('accounts.tree-node', ['node' => $node, 'depth' => 0])
                    @empty
                        <div class="px-4 py-10 text-center text-gray-400">
                            {{ __('accounts.no_accounts_found') }}
                        </div>
                    @endforelse
                </div>

                <div id="tree-empty-search" class="px-4 py-10 text-center text-gray-400 hidden">
                    {{ __('accounts.no_accounts_found') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .toggle-icon { transition: transform .15s ease; transform: rotate(90deg); }
        .toggle-btn[aria-expanded="false"] .toggle-icon { transform: rotate(0deg); }
    </style>

    <script>
    (function () {
        const treeRoot = document.getElementById('tree-root');
        const searchInput = document.getElementById('tree-search');
        const emptySearchBox = document.getElementById('tree-empty-search');

        function setExpanded(node, expanded) {
            const btn = node.querySelector(':scope > .tree-row > .toggle-btn');
            const childrenWrap = node.querySelector(':scope > .children-wrap');
            if (btn) btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (childrenWrap) childrenWrap.classList.toggle('hidden', !expanded);
        }

        treeRoot.addEventListener('click', (e) => {
            const btn = e.target.closest('.toggle-btn');
            if (!btn) return;
            const node = btn.closest('.tree-node');
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            setExpanded(node, !expanded);
        });

        document.getElementById('expand-all-btn').addEventListener('click', () => {
            treeRoot.querySelectorAll('.tree-node').forEach((node) => setExpanded(node, true));
        });
        document.getElementById('collapse-all-btn').addEventListener('click', () => {
            treeRoot.querySelectorAll('.tree-node').forEach((node) => setExpanded(node, false));
        });

        // فلترة الشجرة بالبحث: أي حساب اسمه أو رقمه يطابق النص بيفضل
        // ظاهر هو وكل أجداده (اتوسّعوا تلقائيًا)، والباقي بيتخفي.
        function filterNode(node, query) {
            const ownMatch = node.dataset.name.includes(query) || node.dataset.number.includes(query);
            const childrenWrap = node.querySelector(':scope > .children-wrap');
            let childMatch = false;

            if (childrenWrap) {
                childrenWrap.querySelectorAll(':scope > .tree-node').forEach((child) => {
                    if (filterNode(child, query)) childMatch = true;
                });
            }

            const visible = query === '' || ownMatch || childMatch;
            node.classList.toggle('hidden', !visible);

            if (query !== '' && childMatch) {
                setExpanded(node, true);
            }

            return visible;
        }

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            let anyVisible = false;

            treeRoot.querySelectorAll(':scope > .tree-node').forEach((node) => {
                if (filterNode(node, query)) anyVisible = true;
            });

            emptySearchBox.classList.toggle('hidden', anyVisible || query === '');
        });
    })();
    </script>
</x-app-layout>
