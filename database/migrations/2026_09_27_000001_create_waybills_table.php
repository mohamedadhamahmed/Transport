<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * بوالص الشحن: مستند الشحنة نفسها (الشاحن / المستلم / البضاعة /
     * الشاحنة والسائق / من - إلى). ممكن تسجّل تحميل الشاحنة على لوحة
     * الشاحنات (truck_load_id)، وممكن يطلع منها فاتورة نقليات.
     */
    public function up(): void
    {
        Schema::create('waybills', function (Blueprint $table) {
            $table->id();
            $table->string('waybill_number')->nullable()->index();
            $table->date('issue_date');

            $table->foreignId('truck_id')->constrained('trucks')->restrictOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete(); // العميل (صاحب الحساب)

            $table->string('shipper_name');                 // الشاحن / المرسل
            $table->string('shipper_phone')->nullable();
            $table->string('consignee_name');               // المستلم
            $table->string('consignee_phone')->nullable();

            $table->string('from_region', 50);
            $table->string('from_city')->nullable();
            $table->string('from_address')->nullable();
            $table->string('to_region', 50);
            $table->string('to_city')->nullable();
            $table->string('to_address')->nullable();

            $table->string('goods_description');            // وصف البضاعة / نوع التحميل
            $table->unsignedInteger('packages_count')->nullable(); // عدد الطرود
            $table->decimal('weight', 10, 2)->nullable();   // الوزن (طن)

            $table->dateTime('loaded_at');
            $table->dateTime('expected_unload_at');
            $table->dateTime('delivered_at')->nullable();

            $table->decimal('freight_amount', 12, 2)->default(0);           // أجرة النقل
            $table->enum('freight_payer', ['shipper', 'consignee', 'customer'])->default('customer');

            $table->enum('status', ['open', 'delivered', 'cancelled'])->default('open');
            $table->foreignId('truck_load_id')->nullable()->constrained('truck_loads')->nullOnDelete();
            $table->foreignId('transport_invoice_id')->nullable()->constrained('transport_invoices')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waybills');
    }
};
