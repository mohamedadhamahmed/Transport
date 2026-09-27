<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\FinancialAccount;
use App\Models\Truck;
use App\Models\TruckLoad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * مسح البيانات التجريبية اللي عملها TransportDemoSeeder (المعلّمة بـ [DEMO]).
 * الحاجات اللي اتربطت بفواتير أو سندات حقيقية بتتساب زي ما هي.
 *
 *   php artisan db:seed --class=TransportDemoCleanupSeeder
 */
class TransportDemoCleanupSeeder extends Seeder
{
    public function run(): void
    {
        $tag = TransportDemoSeeder::TAG . '%';

        DB::transaction(function () use ($tag) {
            // الأحمال (غير المفوترة بس)
            $loads = TruckLoad::where('notes', 'like', $tag)->whereNull('transport_invoice_id')->pluck('id');
            DB::table('waybills')->whereIn('truck_load_id', $loads)->update(['truck_load_id' => null]);
            $deletedLoads = TruckLoad::whereIn('id', $loads)->delete();

            // الشاحنات اللي ملهاش أحمال/فواتير/سندات باقية
            $deletedTrucks = 0;
            foreach (Truck::where('notes', 'like', $tag)->get() as $t) {
                if ($t->loads()->exists() || $t->trips()->exists() || $t->expenseVouchers()->exists()) {
                    continue;
                }
                if ($t->financial_account_id) {
                    FinancialAccount::whereKey($t->financial_account_id)->whereDoesntHave('creditTransactions')->delete();
                }
                $t->delete();
                $deletedTrucks++;
            }

            $deletedDrivers = 0;
            foreach (Driver::where('notes', 'like', $tag)->get() as $d) {
                if (TruckLoad::where('driver_id', $d->id)->exists() || Truck::where('driver_id', $d->id)->exists()) {
                    continue;
                }
                $d->delete();
                $deletedDrivers++;
            }

            $deletedCustomers = 0;
            foreach (Customer::where('notes', 'like', $tag)->get() as $c) {
                if (TruckLoad::where('customer_id', $c->id)->exists() || DB::table('transport_invoices')->where('customer_id', $c->id)->exists()) {
                    continue;
                }
                if ($c->accounting_account_id) {
                    FinancialAccount::whereKey($c->accounting_account_id)->whereDoesntHave('creditTransactions')->delete();
                }
                $c->delete();
                $deletedCustomers++;
            }

            $this->command?->info("اتمسح: $deletedLoads حمل، $deletedTrucks شاحنة، $deletedDrivers سائق، $deletedCustomers عميل.");
        });
    }
}
