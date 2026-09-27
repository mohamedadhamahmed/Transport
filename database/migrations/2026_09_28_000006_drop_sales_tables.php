<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * شيل قسم المبيعات بالكامل (فواتير المبيعات، المسودات، مرتجع المبيعات)
     * - الشركة بتشتغل بفواتير النقليات بس (transport_invoices).
     *
     * ⚠️ أي بيانات في الجداول دي بتتمسح نهائيًا ومفيش رجوع (down فاضية).
     * القيود المحاسبية القديمة (credittransaction) بتفضل زي ما هي عشان
     * أرصدة الحسابات متتغيرش.
     */
    private array $tables = [
        'invoice_return_items',
        'invoice_returns',
        'draft_invoice_items',
        'draft_invoices',
        'invoice_items',
        'invoices',
    ];

    private array $permissionKeys = [
        'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.returns',
        'reports_sales.summary', 'reports_sales.profits', 'reports_sales.employee_profits',
        'reports_sales.top_products', 'reports_sales.by_customer', 'reports_sales.by_employee',
        'reports_sales.by_product', 'reports_sales.returns',
        'reports_purchases.purchases_vs_sales',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

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
