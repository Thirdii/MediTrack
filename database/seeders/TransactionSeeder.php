<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = Medicine::with(['batches', 'pharmacy'])->get();

        foreach ($medicines as $medicine) {
            $pharmacyStaff = User::where('pharmacy_id', $medicine->pharmacy_id)
                ->where('role', 'staff')
                ->get();

            if ($pharmacyStaff->isEmpty()) continue;

            $batches = $medicine->batches;
            if ($batches->isEmpty()) continue;

            // Generate Stock In transactions (historical)
            foreach ($batches as $batch) {
                $user = $pharmacyStaff->random();

                $txn = InventoryTransaction::create([
                    'pharmacy_id' => $medicine->pharmacy_id,
                    'medicine_id' => $medicine->id,
                    'user_id' => $user->id,
                    'type' => 'stock_in',
                    'quantity' => $batch->quantity + rand(0, 50),
                    'direction' => 'in',
                    'remarks' => 'Initial stock received',
                    'reference_number' => 'PO-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'created_at' => $batch->date_received,
                    'updated_at' => $batch->date_received,
                ]);

                InventoryTransactionLine::create([
                    'inventory_transaction_id' => $txn->id,
                    'batch_id' => $batch->id,
                    'quantity' => $txn->quantity,
                ]);
            }

            // Generate Stock Out transactions over last 30 days (for ADD calculation)
            $stockOutDays = rand(5, 15);
            $nonExpiredBatches = $batches->filter(fn ($b) => $b->expiration_date->isFuture() && $b->quantity > 0);

            for ($d = 0; $d < $stockOutDays; $d++) {
                if ($nonExpiredBatches->isEmpty()) break;

                $batch = $nonExpiredBatches->first();
                $outQty = min(rand(1, 10), $batch->quantity);
                if ($outQty <= 0) continue;

                $user = $pharmacyStaff->random();
                $date = Carbon::now()->subDays(rand(1, 30));

                $txn = InventoryTransaction::create([
                    'pharmacy_id' => $medicine->pharmacy_id,
                    'medicine_id' => $medicine->id,
                    'user_id' => $user->id,
                    'type' => 'stock_out',
                    'quantity' => $outQty,
                    'direction' => 'out',
                    'remarks' => 'Dispensed to patient',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                InventoryTransactionLine::create([
                    'inventory_transaction_id' => $txn->id,
                    'batch_id' => $batch->id,
                    'quantity' => $outQty,
                ]);

                // Reduce batch quantity
                $batch->quantity -= $outQty;
                $batch->save();
            }

            // Occasionally add damaged/adjustment transactions
            if (rand(1, 4) === 1 && $nonExpiredBatches->isNotEmpty()) {
                $batch = $nonExpiredBatches->random();
                $dmgQty = min(rand(1, 3), $batch->quantity);

                if ($dmgQty > 0) {
                    $user = $pharmacyStaff->random();

                    $txn = InventoryTransaction::create([
                        'pharmacy_id' => $medicine->pharmacy_id,
                        'medicine_id' => $medicine->id,
                        'user_id' => $user->id,
                        'type' => 'damaged',
                        'quantity' => $dmgQty,
                        'direction' => 'out',
                        'remarks' => 'Found damaged during inspection',
                        'created_at' => Carbon::now()->subDays(rand(1, 20)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 20)),
                    ]);

                    InventoryTransactionLine::create([
                        'inventory_transaction_id' => $txn->id,
                        'batch_id' => $batch->id,
                        'quantity' => $dmgQty,
                    ]);

                    $batch->quantity -= $dmgQty;
                    $batch->save();
                }
            }
        }
    }
}
