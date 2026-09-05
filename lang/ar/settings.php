<?php
return [
    // عام
    'title'                 => 'الإعدادات العامة',
    'branch'                => 'الفرع',
    'save'                  => 'حفظ',

    // بيانات الشركة
    'company_info'          => 'بيانات الشركة العامة',
    'company_general_info'  => 'بيانات الشركة العامة', // أُضيف للمطابقة
    'name_ar'               => 'الاسم بالعربي',
    'name_en'               => 'الاسم بالإنجليزي',
    'sr'                    => 'السجل التجاري',
    'commercial_record'     => 'السجل التجاري', // أُضيف للمطابقة
    'tax'                   => 'الرقم الضريبي',
    'tax_number'            => 'الرقم الضريبي', // أُضيف للمطابقة
    'address_ar'            => 'العنوان بالعربي',
    'address_en'            => 'العنوان بالإنجليزي',
    'service_cost'          => 'تكلفة الخدمة',
    'delivery_cost'         => 'تكلفة التوصيل',
    'bank_name'             => 'اسم البنك',
    'bank_account_number'   => 'رقم الحساب البنكي',
    'bank_account_iban'     => 'رقم الآيبان (IBAN)',
    'iban'                  => 'رقم الآيبان (IBAN)', // أُضيف للمطابقة
    'logo'                  => 'شعار الشركة',
    'company_logo'          => 'شعار الشركة', // أُضيف للمطابقة
    'save_company_info'     => 'حفظ بيانات الشركة',

    // إعدادات الزكاة والفوترة
    'zakat_settings'        => 'إعدادات الزكاة والفوترة الإلكترونية (ZATCA)',
    'zakat_zatca_settings'  => 'إعدادات الزكاة والفوترة الإلكترونية (ZATCA)', // أُضيف للمطابقة
    'entity_name'           => 'اسم المنشأة',
    'facility_name'         => 'اسم المنشأة', // أُضيف للمطابقة
    'mobile'                => 'رقم الجوال',
    'trn'                   => 'الرقم الضريبي (TRN)',
    'crn'                   => 'السجل التجاري (CRN)',
    'street_name'           => 'اسم الشارع',
    'building_number'       => 'رقم المبنى',
    'plot_identification'   => 'رقم قطعة الأرض',
    'postal_number'         => 'الرمز البريدي',
    'region'                => 'المنطقة',
    'city'                  => 'المدينة',
    'business_category'     => 'نوع النشاط',
    'invoice_type'          => 'نوع الفاتورة',
    'email_address'         => 'البريد الإلكتروني',
    'production_mode'       => 'وضع الإنتاج (Production)',
    'save_zakat_settings'   => 'حفظ إعدادات الزكاة والفوترة',
    'save_zakat_info'       => 'حفظ إعدادات الزكاة والفوترة', // أُضيف للمطابقة

    // بيانات الشهادة الرقمية (مطلوبة للربط مع زكاة)
    'certificate_fields_hint'   => 'هذه البيانات مطلوبة عند الربط مع منظومة زكاة والفوترة الإلكترونية (شاشة "الربط مع زكاة")',
    'common_name'               => 'الاسم الشائع (Common Name)',
    'organization_name'         => 'اسم المنشأة (Organization Name)',
    'organization_unit_name'    => 'اسم الوحدة التنظيمية (Organization Unit)',
    'country_name'               => 'كود الدولة',
    'egs_serial_number'         => 'الرقم التسلسلي لجهاز الفوترة (EGS Serial Number)',
    'registered_address'        => 'العنوان المسجل',
    'go_to_onboarding'          => 'الربط مع زكاة',

    // شاشة الربط مع زكاة (Onboarding)
    'onboarding_title'              => 'الربط مع منظومة زكاة والفوترة الإلكترونية',
    'onboarding_subtitle'           => 'يتم من خلال هذه الشاشة توليد الشهادة الرقمية وربط الفرع فعليًا مع بوابة فاتورة',
    'onboarding_branch_info'        => 'بيانات الفرع الحالية',
    'onboarding_missing_zakat_info' => 'يجب استكمال بيانات الزكاة والفوترة الإلكترونية أولاً (بما فيها بيانات الشهادة) من شاشة الإعدادات قبل محاولة الربط',
    'connection_type'               => 'نوع البيئة',
    'connection_type_simulation'    => 'بيئة تجريبية (Simulation)',
    'connection_type_production'    => 'بيئة الإنتاج الفعلية (Production)',
    'invoice_type_both'             => 'فاتورة ضريبية وفاتورة مبسطة (Both)',
    'invoice_type_standard'         => 'فاتورة ضريبية (Standard - B2B)',
    'invoice_type_simplified'       => 'فاتورة مبسطة (Simplified - B2C)',
    'otp_label'                     => 'رمز التحقق (OTP) من بوابة فاتورة',
    'otp_hint'                      => 'يمكنك الحصول على رمز التحقق من بوابة فاتورة التابعة لهيئة الزكاة والضريبة والجمارك',
    'get_otp_from_fatoora'          => 'الذهاب إلى بوابة فاتورة للحصول على الرمز',
    'connect_now'                   => 'ربط الآن',
    'onboarding_success'            => 'تم الربط مع منظومة زكاة بنجاح',
    'onboarding_failed'             => 'تعذر إتمام الربط مع منظومة زكاة',
    'certificate_status'            => 'حالة الشهادة',
    'certificate_issued'            => 'تم إصدار شهادة إنتاجية بنجاح',
    'certificate_not_issued'        => 'لم يتم إصدار شهادة بعد',

    // الخصم للموظفين
    'employee_discounts_title'     => 'الخصم المسموح للموظفين',
    'branch_default_discount'      => 'النسبة الافتراضية لهذا الفرع',
    'max_default_discount'         => 'أقصى نسبة خصم افتراضية (%)',
    'requires_approval_above'      => 'يتطلب موافقة المدير إذا كانت نسبة الخصم أعلى من (%)',
    'save_default_discount'        => 'حفظ النسبة الافتراضية',
    'custom_employee_discounts'    => 'نسب مخصصة لكل موظف (اختياري)',
    'employee'                     => 'الموظف',
    'current_rate'                 => 'النسبة الحالية',
    'action'                       => 'إجراء',
    'custom_label'                 => 'مخصصة',
    'default_label'                => 'افتراضية',
    'leave_empty_for_default'      => 'اتركه فارغًا للافتراضي',

    // رسائل النجاح
    'company_info_updated'   => 'تم تحديث بيانات الشركة بنجاح',
    'zakat_settings_updated' => 'تم تحديث إعدادات الزكاة والفوترة بنجاح',
    'branch_default_updated' => 'تم تحديث نسبة الخصم الافتراضية للفرع بنجاح',
    'user_override_updated'  => 'تم تحديث نسبة خصم الموظف بنجاح',
    'user_override_removed'  => 'تم إلغاء النسبة الخاصة، الموظف يستخدم نسبة الفرع الافتراضية الآن',
];