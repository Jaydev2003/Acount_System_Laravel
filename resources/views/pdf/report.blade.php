<!DOCTYPE html>
<html>

<head>
    <title>Balance Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            font-size: 14px;
        }

        h2 {
            margin-bottom: 5px;
        }

        p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        .profit {
            color: green;
            font-weight: bold;
        }

        .loss {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>BALANCE SHEET</h2>
    <p>{{ date('F d, Y') }}</p>

    @php
        $incomeCount = count($incomeTransactions);
        $expenseCount = count($expenseTransactions);
        $extraRow = $profitLoss !== 0 ? 1 : 0; // Add an extra row for profit/loss if it exists
        $maxRows = max($incomeCount, $expenseCount) + $extraRow;
    @endphp

    <table>
        <tr>
            <!-- Income Table -->
            <td style="width: 50%; vertical-align: top; padding: 10px;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="2">Income (Assets)</th>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <th>Amount ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomeTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ number_format($transaction->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @for ($i = $incomeCount; $i < $maxRows - $extraRow; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor

                        @if($profitLoss > 0)
                            <tr class="profit">
                                <td><strong>Net Profit</strong></td>
                                <td>{{ number_format($profitLoss, 2) }}</td>
                            </tr>
                        @elseif($extraRow)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td><strong>{{ number_format($totalIncome, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <!-- Expense Table -->
            <td style="width: 50%; vertical-align: top; padding: 10px;">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="2">Expenses (Liabilities)</th>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <th>Amount ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ number_format($transaction->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        @for ($i = $expenseCount; $i < $maxRows - $extraRow; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor

                        @if($profitLoss < 0)
                            <tr class="loss">
                                <td><strong>Net Loss</strong></td>
                                <td>{{ number_format(abs($profitLoss), 2) }}</td>
                            </tr>
                        @elseif($extraRow)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endif
                    </tbody>
                    {{-- <tfoot>
                        <tr class="total-row">
                            <td><strong>Total Expense</strong></td>
                            <td><strong>{{ number_format($totalExpense, 2) }}</strong></td>
                        </tr>
                    </tfoot> --}}
                    <tr class="total-row">
                        <td><strong>Total</strong></td>
                        <td><strong>{{ number_format($totalIncome, 2) }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>