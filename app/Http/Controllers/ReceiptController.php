<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        // Authorization: only the reception who created it, or any admin
        if (
            ! $user->isAdmin() &&
            ! ($user->isReception() && $transaction->reception_user_id === $user->id)
        ) {
            abort(403);
        }

        $transaction->load(['customer', 'reception', 'items.service', 'items.staff']);

        $pdf = Pdf::loadView('receipt', compact('transaction'))
            ->setPaper('A5', 'portrait');

        return $pdf->stream("receipt-TX{$transaction->id}.pdf");
    }
}
