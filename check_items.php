<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransactionItem;
use App\Models\Transaction;

$items = TransactionItem::with('transaction')->latest()->limit(5)->get();
foreach ($items as $item) {
    echo "ID: {$item->id}, Service: {$item->service_id}, Price: {$item->unit_price}, Total: {$item->line_total}, Free: " . ($item->transaction->is_free_haircut ? 'YES' : 'NO') . "\n";
}
