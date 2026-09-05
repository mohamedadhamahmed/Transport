<x-app-layout>
    <div class="py-6">
        <div class="max-w-[1200px] mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="rounded-2xl bg-gradient-to-l from-[#0F1B4C] to-[#1B2C63] px-5 sm:px-6 py-5 shadow-lg shadow-[#0F1B4C]/15 flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#F5811E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 21V10l8-6 8 6v11" /><path d="M9 21v-6h6v6" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ __('branches.title') }}</h2>
                        <p class="text-white/45 text-xs mt-0.5">{{ __('branches.subtitle') }}</p>
                    </div>
                </div>
                @can('branches.create')
                    <a href="{{ route('branches.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-[#1456E8] to-[#6B2FD6] hover:opacity-90 transition whitespace-nowrap">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        {{ __('branches.new_branch') }}
                    </a>
                @endcan
            </div>

            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-2.5">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-xl p-6">
                <div class="overflow-x-auto rounded-xl border border-[#0F1B4C]/10">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[#0F1B4C] text-white/80">
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('branches.name') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('branches.location') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('branches.type') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase">{{ __('branches.parent_branch') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($branches as $branch)
                                <tr class="hover:bg-[#1456E8]/5 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $branch->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $branch->location ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $branch->type === 'main' ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700' }}">
                                            {{ $branch->type === 'main' ? __('branches.type_main') : __('branches.type_sub') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $branch->parentBranch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @can('branches.edit')
                                                <a href="{{ route('branches.edit', $branch) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-[#1456E8] bg-[#1456E8]/10 hover:bg-[#1456E8]/20 transition">
                                                    {{ __('branches.edit') }}
                                                </a>
                                            @endcan
                                            @can('branches.delete')
                                                <form method="POST" action="{{ route('branches.destroy', $branch) }}"
                                                      onsubmit="return confirm('{{ __('branches.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 transition">
                                                        {{ __('branches.delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-400">{{ __('branches.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($branches->hasPages())
                    <div class="mt-4">{{ $branches->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
