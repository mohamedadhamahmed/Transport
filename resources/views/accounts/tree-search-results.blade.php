{{--
    جزء HTML بيترجع من AccountController::treeSearch بـ AJAX - نتيجة
    مسطّحة (مش شجرة) لأول 50 حساب مطابق، كل واحد معاه مسار آباءه
    (breadcrumb) عشان يبان في سياقه حتى لو أبوه لسه متفتحش في الشجرة.
    مفيش layout هنا عمدًا (fragment بس) - راجع JS في tree.blade.php.

    المتغيرات المتوقعة: $results (array<{account, breadcrumb}>).
--}}
@foreach ($results as $result)
    <div class="tree-node" data-account-id="{{ $result['account']->id }}">
        <div class="tree-row flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-[#1456E8]/5 transition" style="padding-inline-start: 10px;">
            <span class="w-5 h-5 shrink-0"></span>
            @include('accounts._account-row', ['account' => $result['account'], 'breadcrumb' => $result['breadcrumb']])
        </div>
    </div>
@endforeach
