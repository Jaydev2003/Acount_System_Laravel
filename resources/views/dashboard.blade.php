@extends('Layout.account')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <div class="main-panel ">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="filter-box">
                    <h6 class="filter-label me-2">Filter</h6>
                    <div id="reportrange">
                        <i class="fa fa-calendar"></i>&nbsp;
                        <span></span> <i class="fa fa-caret-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <div id="message" class="message-container"></div>

        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card shadow-lg rounded-lg bg-primary text-white text-center p-3">
                    <h5>Total Income</h5>
                    <h3 id="total_income">$0</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-lg rounded-lg bg-danger text-white text-center p-3">
                    <h5>Total Expense</h5>
                    <h3 id="total_expense">$0</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-lg rounded-lg bg-warning text-white text-center p-3">
                    <h5>Balance</h5>
                    <h3 id="balance">$0</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-lg rounded-lg bg-success text-white text-center p-3">
                    <h5>Status</h5>
                    <h3><span id="status" class="badge badge-light">-</span></h3>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="chart-container">
                    <canvas id="reportChart" width="300" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        jQuery(document).ready(function($) {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function fetchData(startDate, endDate) {
                let token = localStorage.getItem("token");
                let user = JSON.parse(localStorage.getItem("user"));
                let userId = user ? user.id : null;

                if (!userId) {
                    showMessage("user not found", 'error');
                    window.location.href = "/";
                    return;
                }

                fetch("http://127.0.0.1:8000/api/user/account-detail/report", {
                        method: "POST",
                        headers: {
                            "Authorization": "Bearer " + token,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            start_date: startDate,
                            end_date: endDate
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 200 || !data.data.length) {
                            resetValues();
                            showMessage("No data found for the selected period.", 'error');
                            return;
                        }

                        let report = data.data[0].monthly_reports;
                        if (!report.length) {
                            resetValues();
                            showMessage("No data available for this period.", 'error');
                            return;
                        }

                        let totalIncome = report.reduce((sum, r) => sum + r.total_income, 0);
                        let totalExpense = report.reduce((sum, r) => sum + r.total_expense, 0);
                        let balance = Math.max(totalIncome - totalExpense, 0);
                        let statusText = (totalIncome - totalExpense) >= 0 ? "Profit" : "Loss";
                        let statusClass = (totalIncome - totalExpense) >= 0 ? "badge-success" : "badge-danger";

                        $("#total_income").text(`$${totalIncome}`);
                        $("#total_expense").text(`$${totalExpense}`);
                        $("#balance").text(`$${balance}`);
                        $("#status").text(`${statusText} ($${Math.abs(totalIncome - totalExpense)})`)
                            .removeClass("badge-success badge-danger")
                            .addClass(statusClass);

                        renderChart(totalIncome, totalExpense);
                    })
                    .catch(error => console.error("Error fetching data:", error));
            }

            function renderChart(income, expense) {
                const ctx = document.getElementById("reportChart").getContext("2d");

                if (window.myChart) {
                    window.myChart.destroy();
                }

                window.myChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: ["Total Income", "Total Expense"],
                        datasets: [{
                            data: [income, expense],
                            backgroundColor: ["#007BFF", "#DC3545"],
                            // borderColor: ["#0056b3", "#B22222"], 
                            borderWidth: 2,
                            hoverBackgroundColor: ["#3399FF", "#FF4D4D"],
                            hoverBorderColor: ["#004085", "#8B0000"],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: "top",
                                labels: {
                                    font: {
                                        size: 14,
                                        weight: "bold"
                                    },
                                    color: "#333"
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        let value = tooltipItem.raw.toLocaleString();
                                        return `${tooltipItem.label}: $${value}`;
                                    }
                                }
                            }
                        },
                        cutout: "60%",
                        animation: {
                            animateRotate: true,
                            animateScale: true
                        }
                    }
                });
            }

            function resetValues() {
                $("#total_income").text("$0");
                $("#total_expense").text("$0");
                $("#balance").text("$0");
                $("#status").text("-").removeClass("badge-success badge-danger").addClass("badge-light");
                renderChart(0, 0);
            }


            function showMessage(message, type) {
                let messageDiv = document.getElementById("message");
                messageDiv.textContent = message;
                messageDiv.className = `message-container ${type}-message`;
                messageDiv.style.display = "block";
                setTimeout(() => {
                    messageDiv.style.display = "none";
                }, 2000);
            }

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                fetchData(start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));
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
    </script>
@endsection
