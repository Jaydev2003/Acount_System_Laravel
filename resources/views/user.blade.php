@extends('Layout.account')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">

    <div id="fullScreenLoader">
        <div class="loader"></div>
    </div>
    <div class="main-panel mt-2">

        <div class="container mx-auto mt-6 p-6">
            <div class="table-container relative">

                <div class="table-header">
                    <h2 class="text-2xl font-semibold text-gray-700">User Records</h2>
                    <button class="btn btn-primary" onclick="openUserModal()" style="border-radius: 10px">
                        <i class="ri-user-add-line icon"></i> Add User
                    </button>

                </div>

                <div class="overflow-x-auto">
                    <table id="userTable" class="display stripe" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="width:100%">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="userForm">
                            <input type="hidden" id="userId">

                            <div class="mb-3">
                                <label for="userName" class="form-label">Name</label>
                                <input type="text" id="userName" class="form-control" placeholder="Enter name">
                            </div>

                            <div class="mb-3">
                                <label for="userEmail" class="form-label">Email</label>
                                <input type="email" id="userEmail" class="form-control" placeholder="Enter email">
                            </div>

                            <div class="mb-3" id="passwordField" style="display: none">
                                <label for="userPassword" class="form-label">Password</label>
                                <input type="password" id="userPassword" class="form-control" placeholder="Enter password">
                                <small class="text-muted">Leave blank to keep the existing password</small>
                            </div>
                            <div class="mb-3">
                                <label for="userRole" class="form-label">Role</label>
                                <select id="userRole" class="form-control" style="border:1px solid #ccc;">
                                    <option value="">Loading roles...</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" id="submitBtn" class="btn btn-primary">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function openUserModal() {
            let permissions = JSON.parse(localStorage.getItem("permissions")) || [];

            if (!permissions.includes("Add-user")) {
                showMessage("You do not have permission to add users.", "error");
                return;
            }

            document.getElementById("userForm").reset();
            document.getElementById("userModalLabel").textContent = "Add New User";
            document.getElementById("submitBtn").textContent = "Add User";
            let modal = new bootstrap.Modal(document.getElementById("userModal"));
            modal.show();
        }

        document.addEventListener("DOMContentLoaded", async function() {
            let token = localStorage.getItem("token");
            let loader = document.getElementById("fullScreenLoader");
            if (!token) {
                console.error("No authentication token found.");
                return;
            }

            await fetchUsers();
            await fetchRoles();
           

            document.getElementById("userForm").addEventListener("submit", async function(e) {
                e.preventDefault();

                let userId = document.getElementById("userId").value;
                let name = document.getElementById("userName").value.trim();
                let email = document.getElementById("userEmail").value.trim();
                let password = document.getElementById("userPassword").value.trim();
                let role_id = document.getElementById("userRole").value;

                if (!name || !email || !role_id) {
                    showMessage("Name, Email, and Role are required.", "error");
                    return;
                }

                let token = localStorage.getItem("token");
                let url = userId ? "http://127.0.0.1:8000/api/user/update" :
                    "http://127.0.0.1:8000/api/user/create";
                let method = userId ? "PUT" : "POST";
                let statusCode = userId ? 200 : 201;
                let bodyData = {
                    name,
                    email,
                    role_id
                };
                if (userId) bodyData.id = userId;
                if (password) bodyData.password = password;

                loader.style.display = "flex";
                try {
                    let response = await fetch(url, {
                        method: method,
                        headers: {
                            "Authorization": "Bearer" + token,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(bodyData),
                    });

                    let result = await response.json();
                    console.log("API Response:", result);

                    if (response.status === statusCode) {
                        showMessage(userId ? "User updated successfully!" :
                            "User added successfully!", "success");
                        document.getElementById("userForm").reset();
                        let modal = bootstrap.Modal.getInstance(document.getElementById(
                            "userModal"));
                        modal.hide();
                        fetchUsers();
                    } else if (response.status === 400 && result.errors) {
                        let errorMessages = [];
                        Object.keys(result.errors).forEach(field => {
                            if (Array.isArray(result.errors[field]) && result.errors[field]
                                .length > 0) {
                                errorMessages.push(
                                    `${field}: ${result.errors[field].join(", ")}`);
                            }
                        });

                        console.log("Extracted Error Messages:", errorMessages);

                        if (errorMessages.length > 0) {
                            showMessage(errorMessages.join("<br>"), "error");
                        } else {
                            showMessage(
                                "Validation errors occurred, but no specific message provided.",
                                "error");
                        }

                    } else {
                        showMessage(result.message || "An error occurred.", "error");
                    }
                } catch (error) {
                    console.error("Error:", error);
                    showMessage("An error occurred. Please try again.", "error");
                } finally {
                    loader.style.display = "none";
                }
            });
        });
        let permissions = JSON.parse(localStorage.getItem("permissions")) || [];

        async function fetchUsers() {
            let permissions = JSON.parse(localStorage.getItem("permissions")) || [];
            if (!permissions.includes("View-user")) {
                showMessage("You do not have permission to View users.", "error");
                return;
            }
            let token = localStorage.getItem("token");
            try {
                let response = await fetch("http://127.0.0.1:8000/api/user/display", {
                    method: "GET",
                    headers: {
                        "Authorization": "Bearer " + token,
                        "Content-Type": "application/json",
                    },
                });

                let data = await response.json();
                if (response.ok) {
                    let users = data.data;
              
                    let table = $('#userTable').DataTable();
                    table.clear();

                    users.forEach((user, index) => {
                        table.  .add([
                            index + 1,
                            user.name,
                            user.email,
                            user.role.name,
                            `<button onclick="editUser('${user.id}')" class="action-btn edit-btn">
                                    <i class="ri-edit-2-line"></i>
                                </button>
                                <button onclick="deleteUser('${user.id}')" class="action-btn delete-btn">
                                    <i class="ri-delete-bin-line"></i>
                                </button>`
                        ]);
                    });

                    table.draw();
                } else {
                    console.error("Error fetching data:", data.message);
                }
            } catch (error) {
                console.error("Request failed:", error);
            }
        }

        $(document).ready(function() {
            var table = $('#userTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10
            });
            fetchUsers();
        });


        async function fetchRoles() {
            let token = localStorage.getItem("token");
            try {
                let response = await fetch("http://127.0.0.1:8000/api/role/roles-with-permissions", {
                    method: "GET",
                    headers: {
                        "Authorization": "Bearer " + token,
                        "Content-Type": "application/json",
                    },
                });

                let data = await response.json();
                if (response.ok) {

                    let roles = data.data;

                    let roleDropdown = document.getElementById("userRole");
                    roleDropdown.innerHTML = '<option value="">Select Role</option>';

                    data.data.forEach(role => {
                        roleDropdown.innerHTML += `<option value="${role.id}">${role.role_name}</option>`;
                    });
                } else {
                    console.error("Error fetching roles:", data.message);
                }
            } catch (error) {
                console.error("Error fetching roles:", error);
            }
        }
          
        async function deleteUser(userId) {
    let permissions = JSON.parse(localStorage.getItem("permissions")) || [];

    if (!permissions.includes("Delete-user")) {
        showMessage("You do not have permission to delete users.", "error");
        return;
    }

    let token = localStorage.getItem("token");
    if (!token) {
        Swal.fire("Error", "Authentication token not found.", "error");
        return;
    }

    const result = await Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await fetch("http://127.0.0.1:8000/api/user/delete", {
            method: "DELETE",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: userId })
        });

      const data = await response.json();

        if (data.status === 200) {
            Swal.fire("Deleted!", "User has been deleted.", "success");

            let table = $('#userTable').DataTable();
            table.row($(`button[onclick="deleteUser('${userId}')"]`).parents('tr')).remove().draw();
        } else {
            Swal.fire("Error", data.message || "Failed to delete user.", "error");
        }
    } catch (error) {
        console.error("Error deleting user:", error);
        Swal.fire("Error", "An error occurred. Please try again.", "error");
    }
}




async function editUser(userId) {
    let permissions = JSON.parse(localStorage.getItem("permissions")) || [];

    if (!permissions.includes("Edit-user")) {
        showMessage("You do not have permission to edit users.", "error");
        return;
    }

    document.getElementById("passwordField").style.display = "block";

    let token = localStorage.getItem("token");
    if (!token) {
        Swal.fire("Error", "Authentication token not found.", "error");
        return;
    }

    try {
        const response = await fetch("http://127.0.0.1:8000/api/user/user-data", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: userId }),
        });

        const result = await response.json();

        if (result.status === 200) {
            const user = result.data;
            document.getElementById("userId").value = user.id;
            document.getElementById("userName").value = user.name;
            document.getElementById("userEmail").value = user.email;
            document.getElementById("userRole").value = user.role_id;
            document.getElementById("userPassword").value = "";

            document.getElementById("userModalLabel").textContent = "Edit User";
            document.getElementById("submitBtn").textContent = "Update User";

            const modal = new bootstrap.Modal(document.getElementById("userModal"));
            modal.show();
        } else {
            Swal.fire("Error", result.message || "Failed to load user data.", "error");
        }
    } catch (error) {
        console.error("Error fetching user data:", error);
        Swal.fire("Error", "An error occurred. Please try again.", "error");
    }
}


        function showMessage(message, type) {
            let messageDiv = document.getElementById("message");
            if (!messageDiv) {
                messageDiv = document.createElement("div");
                messageDiv.id = "message";
                document.body.appendChild(messageDiv);
            }
            messageDiv.textContent = message;
            messageDiv.className = `message-container ${type}-message`;
            messageDiv.style.display = "block";
            setTimeout(() => {
                messageDiv.style.display = "none";
            }, 2000);
        }
    </script>
@endsection
