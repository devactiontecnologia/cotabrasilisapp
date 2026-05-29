<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Quota;
use App\Models\QuotaTransaction;

echo "=== Cotas em negociacao (compra) ===\n";
$negotiating = Quota::where('status', 'negotiating')->with(['saleOffers'])->get();
foreach ($negotiating as $q) {
    $tx = $q->current_transaction_id ? QuotaTransaction::find($q->current_transaction_id) : null;
    echo "#{$q->id} hotel={$q->hotel_name} status={$q->status} tx=" . ($tx?->transaction_type ?? '-') . " tx_status=" . ($tx?->status ?? '-') . "\n";
    echo "  sale offers: " . $q->saleOffers->pluck('status')->join(', ') . "\n";
    $inSearch = Quota::where('id', $q->id)
        ->where('status', Quota::STATUS_AVAILABLE)
        ->whereHasActiveSaleListing()
        ->exists();
    echo "  apareceria na busca compra (available)? " . ($inSearch ? 'SIM (BUG)' : 'nao') . "\n";
}

echo "\n=== Cotas AVAILABLE com oferta venda (aparecem na busca) ===\n";
$available = Quota::where('status', 'available')
    ->whereHasActiveSaleListing()
    ->where(function ($q) {
        $q->whereFractionPeriodAction('sell')->orWhereJsonContains('allowed_uses', 'sell');
    })
    ->get(['id', 'hotel_name', 'status', 'current_transaction_id']);
foreach ($available as $q) {
    echo "#{$q->id} hotel={$q->hotel_name} current_tx={$q->current_transaction_id}\n";
}
