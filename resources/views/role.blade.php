@extends('Layout.account')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/role.css') }}">

    <div class="main-panel">
        <div id="message" class="message-container"></div>

        <div class="table-container relative">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Roles & Permissions</h2>

            <div class="role-container">
                <select id="roleSelect">
                    <option value="">Select a Role</option>
                </select>

                <button class="add-btn" id="openModalBtn">
                    <i class="fa-solid fa-plus"></i>
                </button>

                <button class="delete-btn" id="deleteRoleBtn" style="display: none;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th>Assign</th>
                        </tr>
                    </thead>
                    <tbody id="permissionsTable">
                        <!-- Permissions will be dynamically populated -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="submit-container">
            <button id="submitPermissionsBtn" class="submit-btn">Submit</button>
        </div>
    </div>

    <div class="modal" id="roleModal">
        <div class="modal-content">
            <h3 class="text-lg font-semibold mb-2">Add New Role</h3>
            <input type="text" id="newRoleName" placeholder="Enter role name">

            <div class="modal-buttons">
                <button id="addRoleBtn" class="modal-btn">Add Role</button>
                <button id="closeModalBtn" class="modal-btn close-btn">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            let token = localStorage.getItem("token");
            if (!token) {
                console.error("No authentication token found.");
                return;
            }

            let roleSelect = document.getElementById("roleSelect");
            let permissionsTable = document.getElementById("permissionsTable");
            let modal = document.getElementById("roleModal");
            let openModalBtn = document.getElementById("openModalBtn");
            let closeModalBtn = document.getElementById("closeModalBtn");
            let addRoleBtn = document.getElementById("addRoleBtn");
            let newRoleName = document.getElementById("newRoleName");
            let submitPermissionsBtn = document.getElementById("submitPermissionsBtn");
            let allPermissions = [];
            let roles = [];

            submitPermissionsBtn.style.display = "none";

            function hasPermission(permission) {
                let permissions = JSON.parse(localStorage.getItem("permissions")) || [];
                console.log(permissions);
                return permissions.includes(permission);
            }

            async function fetchRoles() {
                try {
                    let response = await fetch("http://127.0.0.1:8000/api/role/roles-with-permissions", {
                        method: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Content-Type': 'application/json'
                        }
                    });

                    let data = await response.json();
                    if (response.status === 200) {
                        roles = data.data;
                        allPermissions = data.permission;
                        roleSelect.innerHTML = '<option value="">Select a Role</option>';
                        roles
                            .filter(role => role.role_name.toLowerCase() !== "admin")
                            .forEach(role => {
                                let option = document.createElement("option");
                                option.value = role.id;
                                option.textContent = role.role_name;
                                roleSelect.appendChild(option);
                            });
                    } else {
                        console.error("Error fetching roles:", data.message);
                    }
                } catch (error) {
                    console.error("Request failed:", error);
                }
            }

            function renderPermissions(selectedRole = null) {
                permissionsTable.innerHTML = "";

                allPermissions.forEach(permission => {
                    let checkboxes = permission.group.map(group => {
                        let isChecked = false;
                        if (selectedRole) {
                            let selectedRoleData = roles.find(r => r.id == selectedRole);
                            if (selectedRoleData && selectedRoleData.permissions) {
                                let selectedPermissions = Object.values(selectedRoleData
                                    .permissions).flat();
                                isChecked = selectedPermissions.includes(group.name);
                            }
                        }

                        let actionName = group.name.split('-')[0];
                        actionName = actionName.charAt(0).toUpperCase() + actionName.slice(1);
                        return `<label>
                            <input type="checkbox" name="permission_${group.id}" value="${group.id}" ${isChecked ? 'checked' : ''}>
                            ${actionName}
                        </label>`;
                    }).join('');

                    permissionsTable.innerHTML +=
                        `<tr><td>${permission.name}</td><td class="permission-group">${checkboxes}</td></tr>`;
                });


            }

            openModalBtn.addEventListener("click", () => {
                if (!hasPermission("Add-role")) {
                    showMessage("You do not have permission to add a role.", "error");
                    return;
                }
                modal.style.display = "flex";
            });

            closeModalBtn.addEventListener("click", () => modal.style.display = "none");

            addRoleBtn.addEventListener("click", async function() {
                if (!hasPermission("Add-role")) {
                    showMessage("You do not have permission to add a role.", "error");
                    return;
                }
                let roleName = newRoleName.value.trim();
                if (!roleName) return showMessage("Role name is required.", 'error');

                try {
                    let response = await fetch("http://127.0.0.1:8000/api/role/create", {
                        method: "POST",
                        headers: {
                            "Authorization": "Bearer " + token,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            role_name: roleName
                        })
                    });

                    let result = await response.json();
                    if (response.status === 200) {
                        showMessage("Role added successfully!", "success");
                        modal.style.display = "none";
                        newRoleName.value = "";
                        fetchRoles();
                    } else {
                        showMessage(result.message || "Failed to add role.", "error");
                    }
                } catch (error) {
                    console.error("Error adding role:", error);
                    showMessage("An error occurred. Please try again.", "error");
                }
            });

            submitPermissionsBtn.addEventListener("click", async function() {
                if (!hasPermission("Edit-role")) {
                    showMessage("You do not have permission to update permissions.", "error");
                    return;
                }
                let roleId = roleSelect.value;
                if (!roleId) {
                    showMessage("Please select a role first.", "error");
                    return;
                }
                let selectedRole = roles.find(r => r.id == roleId);
                if (!selectedRole) {
                    showMessage("Invalid role selection.", "error");
                    return;
                }
                let permissions = Array.from(document.querySelectorAll(
                        "#permissionsTable input[type='checkbox']:checked"))
                    .map(checkbox => parseInt(checkbox.value));

                if (permissions.length === 0) {
                    showMessage("Please select at least one permission.", "error");
                    return;
                }

                let requestData = {
                    role_id: roleId,
                    role_name: selectedRole.role_name,
                    permissions: permissions
                };

                try {
                    let response = await fetch("http://127.0.0.1:8000/api/role/update", {
                        method: "POST",
                        headers: {
                            "Authorization": "Bearer " + token,
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(requestData)
                    });

                    let result = await response.json();
                    if (response.status === 200) {
                        showMessage("Permissions updated successfully!", "success");
                        console.log(result);
                    } else {
                        showMessage(result.message || "Failed to assign permissions.", "error");
                    }
                } catch (error) {
                    console.error("Error submitting permissions:", error);
                    showMessage("An error occurred. Please try again.", "error");
                }
            });

            let deleteRoleBtn = document.getElementById("deleteRoleBtn");

            roleSelect.addEventListener("change", function() {
                if (this.value) {
                    submitPermissionsBtn.style.display = "block";
                    deleteRoleBtn.style.display = "inline-block";
                } else {
                    submitPermissionsBtn.style.display = "none";
                    deleteRoleBtn.style.display = "none";
                }
                renderPermissions(this.value);
            });

            deleteRoleBtn.addEventListener("click", async function() {
                if (!hasPermission("Delete-role")) {
                    showMessage("You do not have permission to delete a role.", "error");
                    return;
                }

                let roleId = roleSelect.value;
                if (!roleId) {
                    showMessage("Please select a role to delete.", "error");
                    return;
                }


                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            let response = await fetch(
                                `http://127.0.0.1:8000/api/role/delete`, {
                                    method: "DELETE",
                                    headers: {
                                        "Authorization": "Bearer " + token,
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({
                                        role_id: roleId
                                    })
                                });

                            let result = await response.json();
                            if (response.status === 200) {
                                Swal.fire("Deleted!", "Role has been deleted.",
                                    "success");
                                fetchRoles();
                            } else {
                                Swal.fire("Error!", result.message ||
                                    "Failed to delete role.", "error");
                            }
                        } catch (error) {
                            console.error("Error deleting role:", error);
                            Swal.fire("Error!", "An error occurred. Please try again.",
                                "error");
                        }
                    }
                });
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

            fetchRoles();
        });
    </script>
@endsection
