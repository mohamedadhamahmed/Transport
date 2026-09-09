<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // المستخدم اللي عمل التعديل
            $table->decimal('old_quantity', 15, 2);
            $table->decimal('new_quantity', 15, 2);
            $table->decimal('difference', 15, 2); // الفارق (زيادة أو عجز)
            $table->string('reason')->nullable(); // السبب
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};