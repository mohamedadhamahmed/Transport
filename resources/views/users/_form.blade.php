@php
    $targetUser = $targetUser ?? null;
    $isSelf = $targetUser && $targetUser->id === auth()->id();
@endphp

@if ($errors->any())
    <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-2.5 mb-4">
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.name') }} *</label>
        <input type="text" name="name" value="{{ old('name', $targetUser->name ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.email') }} *</label>
        <input type="email" name="email" value="{{ old('email', $targetUser->email ?? '') }}" required
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('users.password') }} @if (! $targetUser) * @endif
        </label>
        <input type="password" name="password" autocomplete="new-password"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
        @if ($targetUser)
            <p class="text-xs text-gray-400 mt-1">{{ __('users.password_hint_edit') }}</p>
        @endif
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.branch') }}</label>
        <select name="branch_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
            @foreach ($branches as $id => $name)
                <option value="{{ $id }}" @selected((string) old('branch_id', $targetUser->branch_id ?? '') === (string) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>

    @if ($isSelf)
        {{-- مانعينش المستخدم يغيّر دوره أو يعطّل حسابه وهو داخل بيه دلوقتي --}}
        <div class="md:col-span-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-2.5">
            {{ __('users.cannot_deactivate_self') }}
        </div>
    @else
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('users.role') }}</label>
            <select name="role_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1456E8] focus:ring-[#1456E8]">
                <option value="">{{ __('users.role_placeholder') }}</option>
                @foreach ($roles as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('role_id', $targetUser->role_id ?? '') === (string) $id)>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 mt-6">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" id="active" name="active" value="1"
                   @checked(old('active', $targetUser->active ?? true))
                   class="rounded border-gray-300 text-[#1456E8] focus:ring-[#1456E8]">
            <label for="active" class="text-sm text-gray-700">{{ __('users.active') }}</label>
        </div>
    @endif
</div>

<div class="flex items-center gap-3 mt-6">
    <button type="submit" class="px-5 py-2 rounded-lg bg-[#0F1B4C] text-white text-sm font-medium hover:bg-[#0F1B4C]/90 transition">
        {{ __('users.save') }}
    </button>
    <a href="{{ route('users.index') }}" class="px-5 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">
        {{ __('users.cancel') }}
    </a>
</div>
