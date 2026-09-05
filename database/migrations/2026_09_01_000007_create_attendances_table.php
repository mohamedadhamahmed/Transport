<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| جدول attendances (الحضور والانصراف)
|--------------------------------------------------------------------------
| صف واحد لكل موظف لكل يوم - بيتعبى إما يدويًا (AttendanceController@store)
| أو دفعة واحدة من ملف بصمة إكسيل (AttendanceImporter). الحساب الفعلي
| (status/late_minutes/overtime_hours/discount_amount) بيحصل في
| App\Services\Hr\AttendanceCalculator بناءً على إعدادات hr_settings
| وجدول hr_holidays - نفس الحاسبة مستخدمة في الحالتين (يدوي واستيراد)
| عشان النتيجة تفضل متطابقة أيًا كان مصدر البيانات.
|
|  - status: present / absent / leave / holiday / weekend
|  - late_minutes: دقايق التأخير عن work_start_time بعد خصم السماحية
|  - overtime_hours: ساعات إضافية بعد work_end_time
|  - overtime_amount: قيمة الأوفرتايم بالريال (overtime_hours × قيمة
|    الساعة المحسوبة من راتب الموظف × hr_settings.overtime_multiplier)
|    - محسوبة ومخزنة وقت الحفظ عشان ميتغيرش لو راتب الموظف اتغير بعدين.
|  - discount_amount: خصم التأخير بالريال (بنفس منطق قيمة الساعة).
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->string('status', 20)->default('present');
            $table->unsignedInteger('late_minutes')->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);

            // manual = مدخلة يدويًا من الشاشة / import = من ملف بصمة إكسيل
            $table->string('source', 10)->default('manual');

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
