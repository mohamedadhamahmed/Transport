<?php

return [
    // عام
    'manufacturing' => 'التصنيع',
    'code' => 'الكود',
    'name' => 'الاسم',
    'status' => 'الحالة',
    'actions' => 'الإجراءات',
    'save' => 'حفظ',
    'add_new' => 'إضافة جديد',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'confirm_delete' => 'هل أنت متأكد من الحذف؟',
    'empty' => 'لا توجد بيانات لعرضها',
    'active' => 'نشط',
    'inactive' => 'غير نشط',

    // محطات العمل
    'workstations_title' => 'محطات العمل',
    'workstation_name' => 'اسم المحطة',
    'workstation_description' => 'الوصف',
    'workstation_cost' => 'التكلفة الإجمالية',
    'workstation_added' => 'تم إضافة محطة العمل بنجاح',
    'workstation_updated' => 'تم تحديث محطة العمل بنجاح',
    'workstation_deleted' => 'تم حذف محطة العمل بنجاح',

    // حالات الأوامر
    'statuses_title' => 'حالات الأوامر',
    'status_name' => 'اسم الحالة',
    'status_color' => 'اللون',
    'status_type' => 'النوع',
    'status_added' => 'تم إضافة الحالة بنجاح',
    'status_updated' => 'تم تحديث الحالة بنجاح',
    'status_deleted' => 'تم حذف الحالة بنجاح',
    'cannot_delete_default_status' => 'لا يمكن حذف الحالة الافتراضية',

    // قوائم مواد الإنتاج (BOM)
    'bom_title' => 'قوائم مواد الإنتاج',
    'bom_add' => 'أضف قائمة مواد الإنتاج',
    'bom_product' => 'المنتج الرئيسي',
    'bom_production_quantity' => 'كمية الإنتاج',
    'bom_total_cost' => 'التكلفة الإجمالية',
    'bom_is_default' => 'افتراضي',
    'bom_items' => 'المواد الخام',
    'bom_item_add' => 'إضافة مادة خام',
    'bom_added' => 'تم إضافة قائمة مواد الإنتاج بنجاح',
    'bom_updated' => 'تم تحديث قائمة مواد الإنتاج بنجاح',
    'bom_deleted' => 'تم حذف قائمة مواد الإنتاج بنجاح',

    // خطط الإنتاج
    'plans_title' => 'خطط الإنتاج',
    'plan_add' => 'أضف خطة الإنتاج',
    'plan_customer' => 'العميل',
    'plan_date' => 'التاريخ',
    'plan_starts' => 'يبدأ',
    'plan_ends' => 'ينتهي',
    'plan_added' => 'تم إضافة خطة الإنتاج بنجاح',
    'plan_updated' => 'تم تحديث خطة الإنتاج بنجاح',
    'plan_deleted' => 'تم حذف خطة الإنتاج بنجاح',
    'plan_convert' => 'تحويل لأمر تصنيع',
    'plan_converted' => 'تم تحويل الخطة لأمر تصنيع بنجاح',

    // أوامر التصنيع
    'orders_title' => 'أوامر التصنيع',
    'order_add' => 'أضف أمر التصنيع',
    'order_quantity' => 'الكمية',
    'order_workstation' => 'محطة العمل',
    'order_added' => 'تم إضافة أمر التصنيع بنجاح',
    'order_updated' => 'تم تحديث أمر التصنيع بنجاح',
    'order_deleted' => 'تم حذف أمر التصنيع بنجاح',
    'order_completed' => 'تم إتمام أمر التصنيع وتحديث المخزون بنجاح',
    'cannot_delete_completed_order' => 'لا يمكن حذف أمر تصنيع مكتمل',
    'complete_order' => 'إتمام الأمر',
    'confirm_complete' => 'هل أنت متأكد من إتمام أمر التصنيع؟ سيتم سحب المواد الخام من المخزون وإضافة المنتج التام.',

    // بنود أمر التصنيع
    'items_title' => 'المواد الخام المطلوبة',
    'item_required_quantity' => 'الكمية المطلوبة',
    'item_consumed_quantity' => 'الكمية المستهلكة فعليًا',
    'item_updated' => 'تم تحديث الكمية بنجاح',

    // التكاليف غير المباشرة
    'indirect_costs_title' => 'التكاليف غير المباشرة',
    'indirect_cost_add' => 'أضف تكلفة غير مباشرة',
    'indirect_cost_name' => 'اسم التكلفة',
    'indirect_cost_amount' => 'المبلغ',
    'indirect_cost_added' => 'تم إضافة التكلفة بنجاح',
    'indirect_cost_deleted' => 'تم حذف التكلفة بنجاح',

    // ملخص التكلفة
    'cost_summary' => 'ملخص التكلفة',
    'direct_materials_cost' => 'تكلفة المواد المباشرة',
    'indirect_costs_total' => 'إجمالي التكاليف غير المباشرة',
    'total_cost' => 'التكلفة الإجمالية',
];
