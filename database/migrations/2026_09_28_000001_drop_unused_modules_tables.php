<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * شيل جداول الأقسام اللي مش مستخدمة في شركة النقليات (بطلب صاحب
     * البرنامج): التسليمات بنوعيها، التصنيع، تحويلات وتسويات المخزون،
     * وعروض الأسعار القديمة (بقت عروض أسعار نقليات).
     *
     * ⚠️ أي بيانات في الجداول دي بتتمسح نهائيًا ومفيش رجوع (down فاضية).
     * + بتمسح صلاحيات الأقسام دي من جدول الصلاحيات.
     */
    private array $tables = [
        // التسليمات (سند التسليم + تسليم المنتج القديم)
        'delivery_invoice_links',
        'delivery_note_item',
        'delivery_note',
        'sales_withoud_taxes',
        'delivery_to_customer_withoud_tax_invoices',

        // التصنيع
        'manufacturing_order_indirect_costs',
        'manufacturing_order_items',
        'manufacturing_orders',
        'production_plans',
        'bom_items',
        'bill_of_materials',
        'workstations',
        'manufacturing_order_statuses',

        // تحويلات وتسويات المخزون
        'stock_transfer_items',
        'stock_transfers',
        'stock_adjustments',

        // عروض الأسعار القديمة (منتجات)
        'quotation_items',
        'quotations',
    ];

    private array $permissionKeys = [
        'delivery.view', 'delivery.create',
        'delivery_note.view', 'delivery_note.create', 'delivery_note.edit', 'delivery_note.approve',
        'reports_delivery.summary', 'reports_delivery.pending', 'reports_delivery.by_employee',
        'stock_transfers.view', 'stock_transfers.create', 'reports_products.stock_transfers',
        'quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // الـ FK checks مقفولة وقت المسح عشان ترتيب الجداول ميفرقش.
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        Schema::enableForeignKeyConstraints();

        if (Schema::hasTable('permissions')) {
            $ids = DB::table('permissions')->whereIn('key', $this->permissionKeys)->pluck('id');
            if ($ids->isNotEmpty()) {
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
                }
                DB::table('permissions')->whereIn('id', $ids)->delete();
            }
        }
    }

    public function down(): void
    {
        // مفيش رجوع: الجداول والبيانات اتمسحت.
    }
};
