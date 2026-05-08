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
        <div class="subtitle">Official Receipt</div>
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
                <td>Customer</td>
                <td>{{ $transaction->customer?->phone ?? 'Walk-in' }}</td>
            </tr>
            @if($transaction->customer?->name)
            <tr>
                <td>Name</td>
                <td>{{ $transaction->customer->name }}</td>
            </tr>
            @endif
            <tr>
                <td>Cashier</td>
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
                <th style="width:35%">Staff</th>
                <th class="right" style="width:25%">KES</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->items as $item)
            <tr>
                <td>{{ $item->service?->name ?? '—' }}</td>
                <td>{{ $item->staff?->name ?? '—' }}</td>
                <td class="right">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td style="text-align:right;">KES {{ number_format($transaction->subtotal, 2) }}</td>
        </tr>
        @if ($transaction->discount > 0)
        <tr>
            <td>Haircut Loyalty Reward</td>
            <td style="text-align:right; color:#16a34a;">- KES {{ number_format($transaction->discount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-row">
            <td><strong>TOTAL</strong></td>
            <td style="text-align:right;"><strong>KES {{ number_format($transaction->total, 2) }}</strong></td>
        </tr>
    </table>

    {{-- Payment Method --}}
    <div class="payment-box">
        <span class="method">Payment: {{ strtoupper($transaction->payment_method) }}</span>
        @if ($transaction->mpesa_reference)
            &nbsp;| Ref: {{ $transaction->mpesa_reference }}
        @endif
    </div>

    {{-- Free Haircut Badge --}}
    @if ($transaction->is_free_haircut)
    <div class="loyalty-badge">Free Haircut Applied — Loyalty Reward</div>
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
