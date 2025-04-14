@extends('Layout.account')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/report.css') }}">
    <div id="message" class="message-container"></div>
    <div class="main-panel mt-2">
        <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTransactionModalLabel">Add Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addTransactionForm">
                            <div class="form-group">
                                <label for="transactionDate">Date:</label>
                                <input type="date" class="form-control" id="transactionDate" required>
                            </div>
                            <div class="form-group">
                                <label for="transactionAmount">Amount:</label>
                                <input type="number" step="0.01" class="form-control" id="transactionAmount" required>
                            </div>
                            <div class="form-group">
                                <label for="transactionType">Type:</label>
                                <select class="form-control" id="transactionType" required>
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="transactionDescription">Description:</label>
                                <textarea class="form-control" id="transactionDescription" required></textarea>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="storeTransaction()">Save
                                Transaction</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-5">

            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTransactionModal">
                Add Transaction
            </button>
            <h2 class="mb-4">User Transactions Report</h2>
            <div id="reportrange"
                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="fa fa-caret-down"></i>
            </div>
            <table id="transactionTable" class="display table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>Total Income:</strong></td>
                        <td><strong id="totalIncome">0.00</strong></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Total Expense:</strong></td>
                        <td><strong id="totalExpense">0.00</strong></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Profit/Loss Status:</strong></td>
                        <td><strong id="profitLossStatus">Calculating...</strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                loadTransactions(start, end);
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }, cb);

            cb(start, end);

            function loadTransactions(start, end) {
                $.ajax({
                    url: "http://127.0.0.1:8000/api/user/account-detail/get-transaction",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        start_date: start.format("YYYY-MM-DD"),
                        end_date: end.format("YYYY-MM-DD")
                    }),
                    success: function(response) {
                        updateTransactionTable(response.data);
                    },
                    error: function() {
                        alert("Error fetching transactions");
                    }
                });
            }

            function updateTransactionTable(transactions) {
                let table = $("#transactionTable").DataTable();
                table.clear();

                let totalIncome = 0,
                    totalExpense = 0;

                transactions.forEach((transaction, index) => {
                    let amount = parseFloat(transaction.amount);
                    if (transaction.type === "income") {
                        totalIncome += amount;
                    } else {
                        totalExpense += amount;
                    }

                    table.row.add([
                        index + 1,
                        transaction.date,
                        amount.toFixed(2),
                        transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1),
                        transaction.description,
                        `<button class="btn btn-danger btn-sm" onclick="deleteTransaction(${transaction.id})">Delete</button>`
                    ]);
                });

                $("#totalIncome").text(totalIncome.toFixed(2));
                $("#totalExpense").text(totalExpense.toFixed(2));
                $("#profitLossStatus").text((totalIncome - totalExpense).toFixed(2));

                table.draw();
            }

            $("#transactionTable").DataTable();
        });
    </script>
@endsection
