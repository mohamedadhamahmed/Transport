<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * أقسام الموارد البشرية (قائمة ثابتة يتختار منها في شاشة الموظف).
     * اسم القسم بيتخزن زي ما هو في employees.department (نفس العمود القديم)
     * عشان التقارير والاستيراد يفضلوا شغالين من غير تغيير.
     */
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('name_en')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        $now = now();
        $names = collect(['السائقين'])
            ->merge(DB::table('employees')->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->reject(fn ($n) => $n === 'النقليات')
            ->unique();

        foreach ($names as $name) {
            if (!DB::table('departments')->where('name', $name)->exists()) {
                DB::table('departments')->insert([
                    'name' => $name,
                    'name_en' => $name === 'السائقين' ? 'Drivers' : null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // الموظفين المربوطين بسائقين يتحطوا في قسم السائقين
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'employee_id')) {
            $ids = DB::table('drivers')->whereNotNull('employee_id')->pluck('employee_id');
            DB::table('employees')->whereIn('id', $ids)
                ->where(fn ($q) => $q->whereNull('department')->orWhere('department', '')->orWhere('department', 'النقليات'))
                ->update(['department' => 'السائقين']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
