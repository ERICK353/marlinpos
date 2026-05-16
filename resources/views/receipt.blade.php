<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
        }

        .receipt {
            width: 90%;
            margin: 0 auto;
            padding: 20px 10px;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #aaa;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .meta table { width: 100%; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .meta td:last-child { text-align: right; color: #444; }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #777;
            margin: 12px 0 5px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 3px;
        }

        .items { width: 100%; border-collapse: collapse; }
        .items th {
            font-size: 10px;
            text-align: left;
            color: #888;
            padding-bottom: 4px;
        }
        .items th.right, .items td.right { text-align: right; }
        .items td { padding: 4px 0; border-bottom: 1px dotted #eee; font-size: 11px; vertical-align: top; }

        .totals { width: 100%; margin-top: 8px; border-collapse: collapse; }
        .totals td { padding: 2px 0; }
        .totals td:last-child { text-align: right; }
        .grand-row td {
            font-size: 13px;
            font-weight: bold;
            border-top: 2px dashed #aaa;
            padding-top: 6px;
        }

        .payment-box {
            margin-top: 10px;
            padding: 5px 8px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }
        .payment-box .method { font-weight: bold; text-transform: uppercase; font-size: 11px; }

        .loyalty-badge {
            background: #fef9c3;
            border: 1px solid #fde68a;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            color: #92400e;
            margin-top: 8px;
        }

        .notes { font-size: 10px; color: #666; margin-top: 8px; }

        .footer {
            text-align: center;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 2px dashed #aaa;
            font-size: 10px;
            color: #888;
            line-height: 1.8;
        }
        .footer strong { color: #333; font-size: 11px; }
    </style>
</head>
<body>
<div class="receipt">

    {{-- Header --}}
    <div class="header">
        <h1>Malyn Executive Barber</h1>
        <div class="subtitle">Receipt</div>
    </div>

    {{-- Meta --}}
    <div class="meta">
        <table>
            <tr>
                <td>Receipt #</td>
                <td><strong>TX-{{ str_pad($transaction->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
            </tr>
            <tr>
                <td>Date</td>
                <td>{{ $transaction->served_at->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{{ $transaction->customer?->phone ?? 'Walk-in' }}</td>
            </tr>
            @if($transaction->customer?->name)
            <tr>
                <td>Customer</td>
                <td>{{ $transaction->customer->name }}</td>
            </tr>
            @endif
            <tr>
                <td>Served By</td>
                <td>{{ $transaction->reception?->name ?? '—' }}</td>
            </tr>
        </table>
    </div>

    {{-- Services --}}
    <div class="section-title">Services</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width:40%">Service</th>
                <th style="width:30%">Staff</th>
                <th class="right" style="width:30%">Price</th>
                <th class="right" style="width:30%">Disc.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->items as $item)
            <tr>
                <td>{{ $item->service?->name ?? '—' }}</td>
                <td>{{ $item->staff?->name ?? '—' }}</td>
                <td class="right">{{ number_format($item->line_total, 2) }}</td>
                <td class="right">{{ $item->discount_amount > 0 ? '-'.number_format($item->discount_amount, 2) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals">
        <tr>
            <td>Price</td>
            <td style="text-align:right;">KES {{ number_format($transaction->subtotal, 2) }}</td>
        </tr>
        @if ($transaction->discount > 0)
        <tr>
            <td>Loyalty Discount</td>
            <td style="text-align:right; color:#16a34a;">- KES {{ number_format($transaction->discount, 2) }}</td>
        </tr>
        @endif
        @php $totalServiceDiscounts = $transaction->items->sum('discount_amount'); @endphp
        @if ($totalServiceDiscounts > 0)
        <tr>
            <td>Service Discounts</td>
            <td style="text-align:right; color:#16a34a;">- KES {{ number_format($totalServiceDiscounts, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-row">
            <td><strong>TOTAL</strong></td>
            <td style="text-align:right;"><strong>KES {{ number_format($transaction->total, 2) }}</strong></td>
        </tr>
    </table>

    {{-- Payment Method --}}
    <div class="payment-box">
        @php
            $isWalletSplit = $transaction->payment_method === 'wallet' && (float)$transaction->amount_tendered > 0;
            $isCashMpesaSplit = $transaction->payment_method === 'split';
        @endphp

        {{-- Payment method label --}}
        @if ($isWalletSplit)
            <span class="method">💳 Split Payment: Wallet + {{ $transaction->mpesa_reference ? 'M-Pesa' : 'Cash' }}</span>
        @elseif ($isCashMpesaSplit)
            <span class="method">🌓 Split Payment: Cash + M-Pesa</span>
        @else
            <span class="method">
                @if($transaction->payment_method === 'cash') 💵 Cash
                @elseif($transaction->payment_method === 'mpesa') 📱 M-Pesa
                @elseif($transaction->payment_method === 'wallet') 💰 Wallet
                @else {{ strtoupper($transaction->payment_method) }}
                @endif
            </span>
            @if ($transaction->mpesa_reference)
                &nbsp;| Ref: {{ $transaction->mpesa_reference }}
            @endif
        @endif

        {{-- Wallet Split breakdown --}}
        @if ($isWalletSplit)
        <div style="margin-top: 5px; font-size: 10px; border-top: 1px dotted #ccc; padding-top: 5px;">
            <table style="width:100%;">
                <tr>
                    <td>💰 From Wallet</td>
                    <td style="text-align:right;">KES {{ number_format((float)$transaction->credit_used, 2) }}</td>
                </tr>
                <tr>
                    <td>{{ $transaction->mpesa_reference ? '📱 M-Pesa (Ref: '.$transaction->mpesa_reference.')' : '💵 Cash Given' }}</td>
                    <td style="text-align:right;">KES {{ number_format((float)$transaction->amount_tendered, 2) }}</td>
                </tr>
                @if ((float)$transaction->change_due > 0)
                <tr>
                    <td>{{ (float)$transaction->credit_stored > 0 ? '💰 Change Stored in Wallet' : '💵 Change Given' }}</td>
                    <td style="text-align:right;">KES {{ number_format((float)$transaction->credit_stored > 0 ? $transaction->credit_stored : $transaction->change_due, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Cash + M-Pesa Split breakdown --}}
        @elseif($isCashMpesaSplit)
        <div style="margin-top: 5px; font-size: 10px; border-top: 1px dotted #ccc; padding-top: 5px;">
            <table style="width:100%;">
                <tr>
                    <td>📱 Paid via M-Pesa</td>
                    <td style="text-align:right;">KES {{ number_format((float)$transaction->mpesa_paid, 2) }}</td>
                </tr>
                <tr>
                    <td>💵 Paid via Cash</td>
                    <td style="text-align:right;">KES {{ number_format((float)$transaction->cash_paid, 2) }}</td>
                </tr>
                @if ((float)$transaction->change_due > 0)
                <tr>
                    <td>{{ (float)$transaction->credit_stored > 0 ? '💰 Change Stored in Wallet' : '💵 Change Given' }}</td>
                    <td style="text-align:right;">KES {{ number_format((float)$transaction->credit_stored > 0 ? $transaction->credit_stored : $transaction->change_due, 2) }}</td>
                </tr>
                @endif
                @if ($transaction->mpesa_reference)
                <tr>
                    <td colspan="2" style="font-size: 8px; color: #666; padding-top: 3px;">Ref: {{ $transaction->mpesa_reference }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Cash-only breakdown --}}
        @elseif($transaction->payment_method === 'cash')
        <div style="margin-top: 5px; font-size: 10px; border-top: 1px dotted #ccc; padding-top: 5px;">
            Money Given: KES {{ number_format((float)$transaction->amount_tendered, 2) }}<br>
            @if((float)$transaction->credit_stored > 0)
                Change Stored in Wallet: KES {{ number_format($transaction->credit_stored, 2) }}
            @elseif((float)$transaction->change_due > 0)
                Change Given: KES {{ number_format($transaction->change_due, 2) }}
            @endif
        </div>

        {{-- Wallet-only credit note --}}
        @elseif($transaction->payment_method === 'wallet' && (float)$transaction->credit_used > 0)
        <div style="margin-top: 5px; font-size: 10px; color: #92400e;">
            Wallet Credit Used: KES {{ number_format($transaction->credit_used, 2) }}
        </div>
        @endif
    </div>

    {{-- Free Haircut Badge --}}
    @if ($transaction->is_free_haircut)
    <div class="loyalty-badge">Free Shave Used — Loyalty Reward</div>
    @endif

    {{-- Notes --}}
    @if ($transaction->notes)
    <div class="notes"><strong>Notes:</strong> {{ $transaction->notes }}</div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <strong>Thank you for visiting Malyn!</strong><br>
        We look forward to seeing you again.
    </div>

</div>
</body>
</html>
