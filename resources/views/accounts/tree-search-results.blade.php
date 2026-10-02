{{--
    جزء HTML بيترجع من AccountController::treeSearch بـ AJAX - نتيجة
    مسطّحة (مش شجرة) لأول 50 حساب مطابق، كل واحد معاه مسار آباءه
    (breadcrumb) عشان يبان في سياقه حتى لو أبوه لسه متفتحش في الشجرة.
    مفيش layout هنا عمدًا (fragment بس) - راجع JS في tree.blade.php.

    المتغيرات المتوقعة: $results (array<{account, breadcrumb}>).
--}}
@foreach ($results as $result)
    <div class="tree-node" data-account-id="{{ $result['account']->id }}">
        <div class="tree-row flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-blue-50/60 transition" style="padding-inline-start: 10px;">
            <span class="w-6 h-6 shrink-0 flex items-center justify-center">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            </span>
            <span class="file-icon text-gray-400 shrink-0 flex items-center">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            @include('accounts._account-row', ['account' => $result['account'], 'breadcrumb' => $result['breadcrumb']])
        </div>
    </div>
@endforeach
