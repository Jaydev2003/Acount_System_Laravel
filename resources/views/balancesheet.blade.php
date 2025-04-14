@extends('Layout.account')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/balancesheet.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <div class="main-panel mt-2">
        <div id="message" class="message-container"></div>

        <div class="container mt-6 p-6">
            <div class="table-container relative">
                <div class="table-responsive">
                    <table id="userTable" class="table table-striped table-bordered">
                        <thead class="text-center">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Name</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", async function () {
            let token = localStorage.getItem("token");
    
            if (!token) {
                showMessage("No authentication token found.", "error");
                return;
            }
    
            let apiUrl = "http://127.0.0.1:8000/api/user/account-detail/view";
            fetchData(apiUrl);
    
            async function fetchData(apiUrl) {
                try {
                    let response = await fetch(apiUrl, {
                        method: "GET",
                        headers: {
                            "Authorization": "Bearer " + token,
                            "Content-Type": "application/json"
                        }
                    });
    
                    let data = await response.json();
    
                    if (response.ok) {
                        populateTable(data.data || []);
                    } else {
                        showMessage(data.message || "Error fetching data.", "error");
                    }
                } catch (error) {
                    showMessage("Request failed: " + error.message, "error");
                }
            }
    
            function populateTable(users) {
                let tableBody = document.querySelector("#userTable tbody");
                tableBody.innerHTML = "";
    
                users.forEach((user, index) => {
                    tableBody.insertAdjacentHTML("beforeend", `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm view-btn" data-user-id="${user.id}">
                                    <i class="fa fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    `);
                });
    
                if ($.fn.DataTable.isDataTable("#userTable")) {
                    $("#userTable").DataTable().destroy();
                }
    
                let enablePaginationAndSearch = users.length > 1;
    
                $("#userTable").DataTable({
                    paging: enablePaginationAndSearch,
                    searching: enablePaginationAndSearch,
                    info: enablePaginationAndSearch,
                    lengthChange: true
                });
    
                document.querySelectorAll(".view-btn").forEach(button => {
                    button.addEventListener("click", function () {
                        let userId = this.getAttribute("data-user-id");
                        localStorage.setItem("selectedUserId", userId);
                        window.location.href = "/report";
                    });
                });
            }
        });
    
        function showMessage(message, type) {
            let messageDiv = document.getElementById("message");
            messageDiv.textContent = message;
            messageDiv.className = `message-container ${type}-message`;
            messageDiv.style.display = "block";
            setTimeout(() => {
                messageDiv.style.display = "none";
            }, 2000);
        }
    </script>
    
@endsection