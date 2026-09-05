{{--
    جزء HTML بيترجع من AccountController::treeChildren بـ AJAX - بيتحط
    مباشرة جوه .children-wrap بتاع الحساب اللي اتفتح (راجع JS في
    tree.blade.php). مفيش layout هنا عمدًا (fragment بس).

    المتغيرات المتوقعة: $nodes (array<{account, hasChildren}>)، $depth (int).
--}}
@foreach ($nodes as $node)
    @include('accounts.tree-node', ['account' => $node['account'], 'depth' => $depth, 'hasChildren' => $node['hasChildren']])
@endforeach
