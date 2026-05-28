<?php

namespace App\Http\Controllers\Concerns;

use App\Models\QuotaTransaction;

trait ResolvesExchangeTransactionViews
{
    protected function transactionView(QuotaTransaction $transaction, string $view): string
    {
        if ($transaction->isExchange()) {
            return "transactions.exchange.{$view}";
        }

        return "transactions.{$view}";
    }
}
