<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\FinancialAccount;
use App\Models\Truck;
use App\Models\TruckLoad;
use App\Services\TruckAssetService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * بيانات تجريبية للنقليات: عملاء + سائقين + شاحنات + أحمال (آخر 60 يوم)
 * + شاحنات محمّلة دلوقتي (منها متأخرة) + تحميل وتفريغ النهارده عشان يظهروا
 * في جرس الإشعارات.
 *
 * التشغيل:   php artisan db:seed --class=TransportDemoSeeder
 * المسح:     php artisan db:seed --class=TransportDemoCleanupSeeder
 *
 * كل حاجة بتتعلّم بـ [DEMO] (في الملاحظات) عشان تتمسح بسهولة. آمن إنه
 * يشتغل أكتر من مرة: العملاء/السائقين/الشاحنات بيتعملوا مرة واحدة،
 * والأحمال بتتعمل بس لو مفيش أحمال تجريبية قبل كده.
 */
class TransportDemoSeeder extends Seeder
{
    public const TAG = '[DEMO]';

    private string $tz;

    public function run(): void
    {
        $this->tz = config('app.timezone', 'UTC');
        mt_srand(20260927); // نفس البيانات كل مرة

        $userId = DB::table('users')->orderBy('id')->value('id');

        DB::transaction(function () use ($userId) {
            $customers = $this->customers($userId);
            $drivers = $this->drivers($userId);
            $trucks = $this->trucks($drivers, $userId);

            if (TruckLoad::where('notes', 'like', self::TAG . '%')->exists()) {
                $this->command?->warn('الأحمال التجريبية موجودة بالفعل - اتعمل بس العملاء/السائقين/الشاحنات الناقصين.');
                return;
            }

            $this->loads($trucks, $drivers, $customers, $userId);
        });

        $this->command?->info('تم: ' . Customer::where('notes', 'like', self::TAG . '%')->count() . ' عميل، '
            . Driver::where('notes', 'like', self::TAG . '%')->count() . ' سائق، '
            . Truck::where('notes', 'like', self::TAG . '%')->count() . ' شاحنة، '
            . TruckLoad::where('notes', 'like', self::TAG . '%')->count() . ' حمل.');
    }

    // ------------------------------------------------------------------
    // العملاء
    // ------------------------------------------------------------------
    private function customers(?int $userId): array
    {
        $rows = [
            ['شركة المثنى للخرسانة الجاهزة', 'الرياض', 'الشفاء', '0114567890', '311621569600003', '1010456789'],
            ['مصنع الراجحي للحديد', 'الدمام', 'الصناعية الثانية', '0138123456', '300845612300003', '2050123456'],
            ['مؤسسة البناء الحديث للمقاولات', 'جدة', 'الصفا', '0126543210', '310234567800003', '4030234567'],
            ['شركة أسمنت اليمامة', 'الرياض', 'السلي', '0112233445', '300012345600003', '1010012345'],
            ['مجموعة المراعي اللوجستية', 'الخرج', 'المنطقة الصناعية', '0115544332', '300098765400003', '1011098765'],
            ['شركة الجزيرة للأخشاب', 'القصيم', 'بريدة الصناعية', '0163322110', '302211334400003', '1131221133'],
            ['مؤسسة تبوك للتجارة العامة', 'تبوك', 'المروج', '0144455667', null, '3550445566'],
            ['شركة الساحل للمواد الغذائية', 'جازان', 'الروضة', '0173344556', '301122334400003', '5900112233'],
        ];

        $out = [];
        foreach ($rows as [$name, $city, $district, $phone, $vat, $cr]) {
            $c = Customer::firstOrCreate(['name' => $name], [
                'phone' => $phone,
                'company_name' => $name,
                'city' => $city,
                'district' => $district,
                'address' => $district . ' - ' . $city,
                'tax_number' => $vat,
                'commercial_registration_number' => $cr,
                'credit_limit' => 200000,
                'grace_period_days' => 30,
                'balance' => 0,
                'opening_balance' => 0,
                'notes' => self::TAG . ' عميل تجريبي',
            ]);

            if (!$c->accounting_account_id) {
                $this->customerAccount($c, $userId);
            }
            $out[] = $c;
        }

        return $out;
    }

    /** نفس حساب العميل اللي CustomerController::store بيعمله في شجرة الحسابات */
    private function customerAccount(Customer $c, ?int $userId): void
    {
        try {
            $next = (int) FinancialAccount::where('account_type', 1)->where('orginal_type', 1)->max('account_number') + 1;
            $acc = FinancialAccount::create([
                'name' => $c->name,
                'account_type' => 1,
                'parent_account_number' => 2,
                'account_number' => $next,
                'start_balance' => 0,
                'current_balance' => 0,
                'start_balance_status' => 3,
                'added_by' => $userId ?? 1,
                'com_code' => 1,
                'date' => Carbon::now('Asia/Riyadh'),
                'active' => 1,
                'is_parent' => 0,
                'orginal_id' => $c->id,
                'orginal_type' => 1,
            ]);
            $c->update(['accounting_account_id' => $acc->id]);
        } catch (\Throwable $e) {
            $this->command?->warn('ماقدرتش أعمل حساب في الشجرة للعميل ' . $c->name . ': ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------
    // السائقين
    // ------------------------------------------------------------------
    private function drivers(?int $userId): array
    {
        $rows = [
            ['محمد عبدالله القحطاني', 'سعودي', 'company'],
            ['أحمد سالم الشهري', 'سعودي', 'company'],
            ['عبدالرحمن فهد العتيبي', 'سعودي', 'company'],
            ['محمد إقبال خان', 'باكستاني', 'company'],
            ['راجو كومار', 'هندي', 'company'],
            ['محمود حسن عبدالعال', 'مصري', 'company'],
            ['خالد يوسف الزهراني', 'سعودي', 'company'],
            ['عمران علي', 'باكستاني', 'company'],
            ['سعيد محمد الغامدي', 'سعودي', 'external'],
            ['ياسر عبدالعزيز الحربي', 'سعودي', 'external'],
        ];

        $hasType = Schema::hasColumn('drivers', 'driver_type');
        $out = [];
        foreach ($rows as $i => [$name, $nat, $type]) {
            $data = [
                'phone' => '05' . (50000000 + $i * 1234567 % 49999999),
                'id_number' => ($nat === 'سعودي' ? '1' : '2') . str_pad((string) (98765432 + $i * 7919), 9, '0', STR_PAD_LEFT),
                'nationality' => $nat,
                'license_number' => 'DL-' . (40210 + $i * 37),
                // سائقين رخصتهم قربت تنتهي / انتهت عشان التنبيهات
                'license_expiry' => now()->addDays([400, 25, 300, -5, 180, 520, 12, 250, 600, 90][$i])->toDateString(),
                'id_expiry' => now()->addDays(200 + $i * 31)->toDateString(),
                'salary' => $type === 'company' ? [4500, 4200, 4800, 2800, 2600, 3000, 4300, 2700, 0, 0][$i] : 0,
                'status' => 'active',
                'notes' => self::TAG . ' سائق تجريبي',
                'created_by' => $userId,
            ];
            if ($hasType) {
                $data['driver_type'] = $type;
            }
            $out[] = Driver::firstOrCreate(['name' => $name], $data);
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // الشاحنات
    // ------------------------------------------------------------------
    private function trucks(array $drivers, ?int $userId): array
    {
        //   اللوحة, الاسم, النوع, الماركة, السنة, الحمولة, سعر النقلة, الملكية, القيمة, الحالة
        $rows = [
            ['ر س ع 1024', 'T-01', 'تريلا', 'مرسيدس أكتروس', '2021', 40, 2800, 'owned', 420000, 'active'],
            ['ب ن ط 2231', 'T-02', 'تريلا', 'فولفو FH', '2022', 40, 2800, 'owned', 455000, 'active'],
            ['ح ل م 3310', 'T-03', 'سطحة', 'مان TGX', '2020', 35, 2500, 'owned', 360000, 'active'],
            ['ك س د 4478', 'T-04', 'تريلا', 'سكانيا R500', '2023', 42, 3000, 'owned', 510000, 'active'],
            ['ع ر ب 5520', 'T-05', 'قلاب', 'مرسيدس أكتروس', '2019', 30, 1800, 'owned', 290000, 'active'],
            ['ص ق و 6641', 'T-06', 'ثلاجة', 'إيسوزو', '2022', 12, 1500, 'owned', 240000, 'active'],
            ['د ه ي 7719', 'T-07', 'تريلا', 'فولفو FH', '2021', 40, 2800, 'owned', 430000, 'active'],
            ['ط م ن 8802', 'T-08', 'صهريج', 'مان TGS', '2020', 32, 2200, 'owned', 330000, 'active'],
            ['س ل ك 9131', 'T-09', 'دينا', 'تويوتا دينا', '2023', 5, 700, 'owned', 145000, 'active'],
            ['ا ب ح 1450', 'T-10', 'تريلا', 'مرسيدس أكتروس', '2018', 40, 2600, 'owned', 250000, 'maintenance'],
            ['ر ع ط 2567', 'خارجية 1', 'تريلا', 'فولفو', '2019', 40, 2400, 'external', null, 'active'],
            ['ب ق ل 3689', 'خارجية 2', 'سطحة', 'مان', '2018', 35, 2200, 'external', null, 'active'],
        ];

        $out = [];
        foreach ($rows as $i => [$plate, $name, $type, $brand, $year, $cap, $price, $own, $value, $status]) {
            // وثائق: شوية منتهية وشوية قربت عشان تنبيهات الوثائق
            $reg = now()->addDays([300, 20, 180, -10, 400, 250, 45, 15, 365, 120, 200, 90][$i]);
            $ins = now()->addDays([150, 210, -3, 260, 28, 190, 320, 100, 240, 60, 80, 170][$i]);

            $truck = Truck::firstOrCreate(['plate_number' => $plate], array_filter([
                'name' => $name,
                'type' => $type,
                'brand' => $brand,
                'model_year' => $year,
                'capacity' => $cap,
                'default_trip_price' => $price,
                'ownership' => $own,
                'purchase_value' => $value,
                'purchase_date' => $value ? now()->subYears(2026 - (int) $year)->startOfYear()->addMonths(2)->toDateString() : null,
                'owner_name' => $own === 'external' ? ($i === 10 ? 'مؤسسة سعيد الغامدي للنقل' : 'ياسر الحربي') : null,
                'owner_phone' => $own === 'external' ? '05' . (51230000 + $i) : null,
                'color' => ['أبيض', 'أحمر', 'أزرق', 'أبيض', 'أصفر', 'أبيض', 'رمادي', 'أبيض', 'أبيض', 'أحمر', 'أبيض', 'أزرق'][$i],
                'chassis_number' => 'WDB' . strtoupper(substr(md5($plate), 0, 14)),
                'serial_number' => (string) (873400000 + $i * 1117),
                'registration_number' => 'R-' . (550100 + $i * 13),
                'registration_expiry' => $reg->toDateString(),
                'insurance_company' => ['التعاونية', 'ولاء', 'ملاذ', 'بوبا', 'التعاونية', 'الراجحي تكافل'][$i % 6],
                'insurance_policy_number' => 'POL-' . (77001 + $i),
                'insurance_expiry' => $ins->toDateString(),
                'operating_card_number' => 'OC-' . (33100 + $i),
                'operating_card_expiry' => now()->addDays(100 + $i * 25)->toDateString(),
                'inspection_expiry' => now()->addDays([60, 5, 200, 150, -20, 90, 300, 40, 120, 30, 70, 110][$i])->toDateString(),
                'driver_id' => $drivers[$i % count($drivers)]->id ?? null,
                'status' => $status,
                'notes' => self::TAG . ' شاحنة تجريبية',
                'created_by' => $userId,
            ], fn ($v) => $v !== null));

            // قيمة الشاحنة المملوكة كأصل ثابت في الشجرة (لو الشجرة جاهزة)
            if ($truck->wasRecentlyCreated && $own === 'owned') {
                try {
                    app(TruckAssetService::class)->sync($truck);
                } catch (\Throwable $e) {
                    // الشجرة مش جاهزة - مش مشكلة للبيانات التجريبية
                }
            }
            $out[] = $truck;
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // الأحمال
    // ------------------------------------------------------------------
    private function loads(array $trucks, array $drivers, array $customers, ?int $userId): void
    {
        // مسارات شائعة (من, إلى, مدينة التحميل, مدينة التنزيل, ساعات الرحلة, سعر مقترح)
        $routes = [
            ['riyadh', 'eastern', 'الرياض - السلي', 'الدمام - الصناعية الثانية', 5, 2600],
            ['eastern', 'riyadh', 'الجبيل الصناعية', 'الرياض - المصانع', 5, 2600],
            ['riyadh', 'makkah', 'الرياض', 'جدة - الخمرة', 11, 4200],
            ['makkah', 'riyadh', 'جدة - الميناء الإسلامي', 'الرياض - السلي', 11, 4200],
            ['riyadh', 'qassim', 'الرياض', 'بريدة', 4, 1900],
            ['qassim', 'riyadh', 'عنيزة', 'الرياض', 4, 1900],
            ['eastern', 'makkah', 'الدمام', 'جدة', 14, 5200],
            ['makkah', 'madinah', 'جدة', 'المدينة - الصناعية', 5, 2300],
            ['riyadh', 'hail', 'الرياض', 'حائل', 7, 2900],
            ['riyadh', 'asir', 'الرياض', 'خميس مشيط', 10, 3900],
            ['makkah', 'jazan', 'جدة', 'جازان', 9, 3500],
            ['riyadh', 'tabuk', 'الرياض', 'تبوك', 13, 4800],
            ['eastern', 'northern_border', 'الدمام', 'عرعر', 12, 4500],
            ['riyadh', 'riyadh', 'الرياض - السلي', 'الخرج', 2, 900],
        ];
        $types = ['أسمنت', 'حديد', 'مواد بناء', 'خرسانة جاهزة', 'بضائع عامة', 'مواد غذائية', 'معدات', 'أخشاب', 'حاويات'];

        $nowLocal = Carbon::now('Asia/Riyadh');
        $active = array_values(array_filter($trucks, fn ($t) => $t->status === 'active'));

        // ---- 1) أحمال منتهية على مدار آخر 60 يوم ----
        $truckFree = []; // آخر وقت الشاحنة فضيت فيه (عشان الأحمال متتداخلش)
        for ($day = 60; $day >= 1; $day--) {
            $count = mt_rand(1, 3);
            for ($k = 0; $k < $count; $k++) {
                $truck = $active[mt_rand(0, count($active) - 1)];
                $r = $routes[mt_rand(0, count($routes) - 1)];
                $loadedLocal = $nowLocal->copy()->subDays($day)->setTime(mt_rand(5, 16), [0, 15, 30, 45][mt_rand(0, 3)]);

                if (isset($truckFree[$truck->id]) && $loadedLocal->lt($truckFree[$truck->id])) {
                    continue;
                }

                $expected = $loadedLocal->copy()->addHours($r[4] + 2);
                // ~20% اتأخروا عن المعاد
                $late = mt_rand(1, 100) <= 20;
                $unloaded = $loadedLocal->copy()->addHours($r[4])->addMinutes(mt_rand(0, 120) + ($late ? mt_rand(180, 600) : 0));
                $truckFree[$truck->id] = $unloaded->copy()->addHours(2);

                $this->makeLoad($truck, $r, $types, $customers, $drivers, $userId, $loadedLocal, $expected, $unloaded,
                    // ~12% من غير سعر عشان تنبيه "أحمال من غير سعر"
                    mt_rand(1, 100) <= 12 ? null : $r[5] + mt_rand(-2, 4) * 100);
            }
        }

        // ---- 2) النهارده: أحمال اتفرّغت الصبح (بتظهر في الإشعارات) ----
        $pool = $active;
        shuffle($pool);
        foreach (array_slice($pool, 0, 2) as $i => $truck) {
            $r = $routes[[0, 4][$i]];
            $loadedLocal = $nowLocal->copy()->subHours($r[4] + 3);
            $unloaded = $nowLocal->copy()->subMinutes(20 + $i * 45);
            $this->makeLoad($truck, $r, $types, $customers, $drivers, $userId, $loadedLocal, $loadedLocal->copy()->addHours($r[4] + 2), $unloaded, $r[5], true);
        }

        // ---- 3) شاحنات محمّلة دلوقتي: 2 متأخرين + 4 في الطريق (منهم تحميل النهارده) ----
        $onRoad = array_slice($pool, 2, 6);
        foreach ($onRoad as $i => $truck) {
            $r = $routes[[2, 6, 1, 8, 10, 3][$i]];
            if ($i < 2) {
                // متأخرة: المعاد المتوقع عدّى بساعات
                $loadedLocal = $nowLocal->copy()->subHours($r[4] + 14 + $i * 3);
                $expected = $nowLocal->copy()->subHours(10 + $i * 2);
            } else {
                // اتحمّلت النهارده من ساعة-4 ساعات
                $loadedLocal = $nowLocal->copy()->subMinutes(60 + $i * 50);
                $expected = $loadedLocal->copy()->addHours($r[4] + 2);
            }
            $this->makeLoad($truck, $r, $types, $customers, $drivers, $userId, $loadedLocal, $expected, null, $r[5], $i >= 2);
        }

        // ---- مكان الشاحنات الفاضية = منطقة آخر تنزيل ----
        foreach ($trucks as $truck) {
            if (TruckLoad::where('active_truck_id', $truck->id)->exists()) {
                $truck->update(['current_region' => null]);
                continue;
            }
            $last = TruckLoad::where('truck_id', $truck->id)->where('status', 'unloaded')->orderByDesc('unloaded_at')->value('to_region');
            $truck->update(['current_region' => $last ?? ['riyadh', 'eastern', 'makkah', 'qassim'][$truck->id % 4]]);
        }
    }

    private function makeLoad(Truck $truck, array $r, array $types, array $customers, array $drivers, ?int $userId,
        Carbon $loadedLocal, Carbon $expectedLocal, ?Carbon $unloadedLocal, ?int $price, bool $today = false): TruckLoad
    {
        $driver = $truck->driver_id ? Driver::find($truck->driver_id) : $drivers[mt_rand(0, count($drivers) - 1)];
        $customer = $customers[mt_rand(0, count($customers) - 1)];

        // أوقات المستخدم (loaded_at/unloaded_at...) بتتكتب بتوقيت الرياض زي الشاشة،
        // و created_at/unload_recorded_at بتوقيت التطبيق زي Laravel
        $appTime = fn (Carbon $local) => Carbon::parse($local->format('Y-m-d H:i:s'), 'Asia/Riyadh')->setTimezone($this->tz);
        $createdAt = $today && !$unloadedLocal ? now() : $appTime($loadedLocal);

        $data = [
            'truck_id' => $truck->id,
            'active_truck_id' => $unloadedLocal ? null : $truck->id,
            'driver_id' => $driver?->id,
            'customer_id' => $customer->id,
            'from_region' => $r[0],
            'from_city' => $r[2],
            'to_region' => $r[1],
            'to_city' => $r[3],
            'load_type' => $types[mt_rand(0, count($types) - 1)],
            'weight' => min((float) $truck->capacity, mt_rand(8, 42)) ?: null,
            'price' => $price,
            'waybill_number' => 'WB-' . $loadedLocal->format('ymd') . '-' . mt_rand(100, 999),
            'loaded_at' => $loadedLocal->format('Y-m-d H:i:s'),
            'expected_unload_at' => $expectedLocal->format('Y-m-d H:i:s'),
            'unloaded_at' => $unloadedLocal?->format('Y-m-d H:i:s'),
            'status' => $unloadedLocal ? 'unloaded' : 'loaded',
            'notes' => self::TAG . ' حمل تجريبي',
            'created_by' => $userId,
            'unloaded_by' => $unloadedLocal ? $userId : null,
        ];
        if (Schema::hasColumn('truck_loads', 'unload_recorded_at')) {
            $data['unload_recorded_at'] = $unloadedLocal ? ($today ? now()->subMinutes(mt_rand(5, 90)) : $appTime($unloadedLocal)) : null;
        }
        if (!Schema::hasColumn('truck_loads', 'price')) {
            unset($data['price']);
        }

        $load = new TruckLoad();
        $load->forceFill($data);
        $load->created_at = $createdAt;
        $load->updated_at = $unloadedLocal && !$today ? $appTime($unloadedLocal) : now();
        $load->timestamps = false;
        $load->save();

        return $load;
    }
}
