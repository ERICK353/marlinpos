<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;

$t = Transaction::latest()->first();
if ($t) {
    echo "ID: {$t->id}, Payment Method: '{$t->payment_method}'\n";
} else {
    echo "No transactions found.\n";
}
