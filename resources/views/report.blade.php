@extends('Layout.account')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/report.css') }}">
    
    <div class="main-panel">
        <div id="message" class="message-container"></div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" class="btn btn-primary" onclick="openTransactionModal()">
                Add Transaction
            </button>

            <h2 class="m-0 text-center flex-grow-1">User Transactions Report</h2>

            <button type="button" class="btn btn-success" onclick="downloadReport()">
                Download Report
            </button>
        </div>

        <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <form id="editTransactionForm">
                            <input type="hidden" id="editTransactionId">
                            
                            <div class="form-group">
                                <label for="editTransactionDate">Date:</label>
                                <input type="date" class="form-control" id="editTransactionDate">
                            </div>
                            
                            <div class="form-group">
                                <label for="editTransactionAmount">Amount:</label>
                                <input type="number" step="0.01" class="form-control" id="editTransactionAmount">
                            </div>
                            
                            <div class="form-group">
                                <label for="editTransactionType">Type:</label>
                                <select class="form-control" id="editTransactionType">
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="editTransactionDescription">Description:</label>
                                <textarea class="form-control" id="editTransactionDescription"></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" onclick="closeEditTransactionModal()">Close</button>

                                <button type="button" class="btn btn-success" onclick="updateTransaction()">Update Transaction</button>
                            </div>
                        </form>
                    </div>
                
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTransactionModalLabel">Add Transaction</h5>
                <button type="button" class="close" onclick="closeModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addTransactionForm">
                    <div class="form-group">
                        <label for="transactionDate">Date:</label>
                        <input type="date" class="form-control" id="transactionDate">
                    </div>
                    <div class="form-group">
                        <label for="transactionAmount">Amount:</label>
                        <input type="number" step="0.01" class="form-control" id="transactionAmount">
                    </div>
                    <div class="form-group">
                        <label for="transactionType">Type:</label>
                        <select class="form-control" id="transactionType">
                            <option value="" selected>Select type of amount</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="transactionDescription">Description:</label>
                        <textarea class="form-control" id="transactionDescription"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="storeTransaction()">Save Transaction</button>
            </div>
        </div>
    </div>
</div>


        <table id="transactionTable" class="display table table-striped table-bordered">
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>

        $(document).ready(function($) {
            var table = $('#transactionTable').DataTable({
                "dom": '<"d-flex justify-content-between align-items-center mb-3"<"dataTables_length"l><"custom-filter-box"><"dataTables_filter"f>>t<"d-flex justify-content-between align-items-center"ip>',
                "paging": true,
                "searching": true,
                "ordering": true,
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10
            });

            $(".custom-filter-box").html(`
            <div class="filter-box d-flex align-items-center">
                <h6 class="filter-label me-2 mb-0">Filter</h6>
                <div id="reportrange">
                    <i class="fa fa-calendar"></i>&nbsp;
                    <span></span> <i class="fa fa-caret-down"></i>
                </div>
            </div>
        `);

            let start = moment("2024-03-01");
            let end = moment("2025-04-30");

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                fetchTransactions(start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));
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
        });

        function closeModal() {
        $('#addTransactionModal').modal('hide');
    }
    function closeEditTransactionModal() {
    $('#editTransactionModal').modal('hide');
}

        async function fetchTransactions(startDate, endDate) {


            let token = localStorage.getItem("token");
            let userId = localStorage.getItem("selectedUserId");

            if (!userId) {
                showMessage("User not Found", "error");
                return;
            }

            function formatDate(dateStr) {
                let dateObj = moment(dateStr, "YYYY-MM-DD");
                return dateObj.format("DD/MM/YYYY");
            }
            let formattedStartDate = formatDate(startDate);
            let formattedEndDate = formatDate(endDate);
            try {
                let response = await fetch("http://127.0.0.1:8000/api/user/account-detail/get-transaction", {
                    method: "POST",
                    headers: {
                        "Authorization": "Bearer " + token,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: userId,
                        start_date: formattedStartDate,
                        end_date: formattedEndDate
                    }),
                });
                let data = await response.json();
                if (response.status !== 200 || !data.data.length) {
                    showMessage("No transactions found for the selected date range.", "error");
                    $("#totalIncome").text("0.00");
                    $("#totalExpense").text("0.00");
                    $("#profitLossStatus").text("Profit/Loss: 0.00");

                    $("#transactionTable tbody").html(`
                    <tr>
                        <td colspan="6" class="text-center">No data available</td>
                    </tr>
                `);
                    return;
                }

                populateTable(data.data);
            } catch (error) {
                console.error("Error fetching transactions:", error);
            }
        }

        function downloadReport() {
            let userId = localStorage.getItem("selectedUserId");

            if (!userId) {
                showMessage("User not found", "error");
                return;
            }

            let dateRange = $('#reportrange span').text();
            let dates = dateRange.split(" - ");

            let startDate = moment(dates[0], "MMMM D, YYYY").format("YYYY-MM-DD");
            let endDate = moment(dates[1], "MMMM D, YYYY").format("YYYY-MM-DD");

            $.ajax({
                url: '/download-report',
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    user_id: userId
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    if (xhr.status === 401) {
                        showMessage("Unauthorized access", "error");
                        return;
                    }

                    let blob = new Blob([response], {
                        type: 'application/pdf'
                    });
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'balance_sheet.pdf';
                    link.click();
                },
                error: function(xhr) {
                    console.error("Error generating report", xhr);
                    showMessage('failed to generate report', 'error');
                }
            });
        }

        
        function populateTable(transactions) {
            let tableBody = $("#transactionTable tbody");
            tableBody.empty();

            let totalIncome = 0,
                totalExpense = 0;
            transactions.forEach((transaction, index) => {
                let amount = parseFloat(transaction.amount);
                transaction.type === "income" ? (totalIncome += amount) : (totalExpense += amount);

                let row = `<tr id='transaction-${transaction.id}'>
                <td>${index + 1}</td>
                <td class=transaction-date >${transaction.date}</td>
                <td class='transaction-amount'>${amount.toFixed(2)}</td>
                <td class='transaction-type'>${transaction.type}</td>
                <td class=transaction-description>${transaction.description}</td>
                <td>
                    <button class='btn btn-warning btn-sm' onclick='openEditModal(${JSON.stringify(transaction)})'>Edit</button>
                    <button class='btn btn-danger btn-sm delete-btn' data-id='${transaction.id}'>Delete</button>
                </td>
            </tr>`;
                tableBody.append(row);
            });

            $("#totalIncome").text(totalIncome.toFixed(2));
            $("#totalExpense").text(totalExpense.toFixed(2));

            let profitLoss = totalIncome - totalExpense;
            let profitLossText = profitLoss >= 0 ? `Profit: ${profitLoss.toFixed(2)}` :
                `Loss: ${Math.abs(profitLoss).toFixed(2)}`;
            $("#profitLossStatus").text(profitLossText);
        }
        $(document).on("click", ".delete-btn", function() {
            let transactionId = $(this).data("id");
            deleteTransaction(transactionId);
        });
        $(document).on("click", ".edit-btn", function() {
            let transactionId = $(this).data("id");

        });

        function openTransactionModal() {
            if (!hasPermission("Add-account")) {
                showMessage("You do not have permission to add a transaction.", "error");
                return;
            }
            $("#addTransactionModal").modal("show");
        }

        
        var accountPermission = JSON.parse(localStorage.getItem("permissions") || "[]");

        var hasPermission = (permission) => accountPermission.includes(permission);



        function openEditModal(transaction) {
            if (!hasPermission("Edit-account")) {
                showMessage("You do not have permission to edit transactions.", "error");
                return;
            }


            let transactionId = transaction.id;
            if (!transactionId) {
                console.error("Invalid transaction ID:", transaction);
                return;
            }

            let row = $(`#transaction-${transactionId}`);
            if (row.length === 0) {
                console.error("Transaction row not found for ID:", transactionId);
                return;
            }

            let dateText = row.find("td.transaction-date").text().trim();
            let amountText = row.find("td.transaction-amount").text().trim();
            let typeText = row.find("td.transaction-type").text().trim();
            let descriptionText = row.find("td.transaction-description").text().trim();

            let dateParts = dateText.split("/");
            let formattedDate = `${dateParts[2]}-${dateParts[1].padStart(2, '0')}-${dateParts[0].padStart(2, '0')}`;

            $("#editTransactionId").val(transactionId);
            $("#editTransactionDate").val(formattedDate);
            $("#editTransactionAmount").val(amountText);
            $("#editTransactionType").val(typeText);
            $("#editTransactionDescription").val(descriptionText);

            $("#editTransactionModal").modal("show");
        }


        async function updateTransaction() {
            let token = localStorage.getItem("token");

            let transactionId = $("#editTransactionId").val();
            let dateValue = $("#editTransactionDate").val();
            let formattedDate = "";

            if (dateValue) {
                let dateParts = dateValue.split("-");
                formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
            }

            let updatedData = {
                id: transactionId,
                date: formattedDate,
                amount: $("#editTransactionAmount").val(),
                type: $("#editTransactionType").val(),
                description: $("#editTransactionDescription").val(),
            };

            try {
                let response = await fetch("http://127.0.0.1:8000/api/user/account-detail/update", {
                    method: "PUT",
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(updatedData)
                });

                let result = await response.json();

                if (response.status === 200) {
                    showMessage("Transaction updated successfully", "success");

                    let row = $(`#transaction-${transactionId}`);
                    row.find("td.transaction-date").text(formattedDate);
                    row.find("td.transaction-amount").text(parseFloat(updatedData.amount).toFixed(2));
                    row.find("td.transaction-type").text(updatedData.type);
                    row.find("td.transaction-description ").text(updatedData.description);

                    $("#editTransactionModal").modal("hide");
                } else {
                    showMessage(result.error, "error");
                    console.error("Update failed:", result);
                }
            } catch (error) {
                console.error("Error updating transaction:", error);
                showMessage("Failed to update transaction", "error");
            }
        }

        async function deleteTransaction(id) {
            if (!hasPermission("Delete-account")) {
                showMessage("You do not have permission to delete transaction.", "error");
                return;
            }
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then(async (result) => {
                if (result.isConfirmed) {
                    let token = localStorage.getItem("token");
                    try {
                        let response = await fetch("http://127.0.0.1:8000/api/user/account-detail/delete", {
                            method: "DELETE",
                            headers: {
                                "Authorization": `Bearer ${token}`,
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                id
                            })
                        });

                        if (response.status === 200) {
                            $(`#transaction-${id}`).remove();
                            Swal.fire("Deleted!", "Transaction has been deleted.", "success");
                        } else {
                            Swal.fire("Error!", "Failed to delete transaction.", "error");
                        }
                    } catch (error) {
                        console.error("Delete request failed:", error);
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                }
            });
        }
        async function storeTransaction() {
    if (!hasPermission("Add-account")) {
        showMessage("You do not have permission to add a transaction.", "error");
        return;
    }

    let userId = localStorage.getItem("selectedUserId");
    let token = localStorage.getItem("token");

    if (!userId || !token) {
        showMessage("User ID or token not found.", "error");
        return;
    }

    let rawDate = $("#transactionDate").val();
    let amount = $("#transactionAmount").val();
    let type = $("#transactionType").val();
    let description = $("#transactionDescription").val();

    // ✅ Validation
    if (!rawDate) {
        showMessage("Please select a date.", "error");
        return;
    }

    if (!amount || parseFloat(amount) <= 0) {
        showMessage("Please enter a valid amount greater than 0.", "error");
        return;
    }

    if (!type) {
        showMessage("Please select a transaction type.", "error");
        return;
    }

    if (!description.trim()) {
        showMessage("Please enter a description.", "error");
        return;
    }

    let formattedDate = moment(rawDate).format("DD/MM/YYYY");

    let formData = {
        user_id: userId,
        date: formattedDate,
        amount: amount,
        type: type,
        description: description,
    };

    try {
        let response = await fetch("http://127.0.0.1:8000/api/user/account-detail/create", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
        });

        let result = await response.json();

        if (response.status === 201) {
            showMessage("Transaction added successfully", "success");

            $("#addTransactionModal").modal("hide");

            let dateRange = $('#reportrange span').text();
            let dates = dateRange.split(" - ");
            let startDate = moment(dates[0], "MMMM D, YYYY").format("YYYY-MM-DD");
            let endDate = moment(dates[1], "MMMM D, YYYY").format("YYYY-MM-DD");
            fetchTransactions(startDate, endDate);
        } else {
            showMessage(result.error || "Failed to add transaction", "error");
        }
    } catch (error) {
        showMessage("Error adding transaction", "error");
    }
}



        function showMessage(message, type) {
            let messageDiv = document.getElementById("message");
            messageDiv.textContent = message;
            messageDiv.className = `message-container ${type}-message`;
            messageDiv.style.display = "block";
            setTimeout(() => {
                messageDiv.style.display = "none";
            }, 3000);
        }
    </script>
@endsection
