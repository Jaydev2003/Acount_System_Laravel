<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Majestic Admin</title>
       
        <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
        <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <link rel="stylesheet" type="text/css"
            href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">

        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="shortcut icon" href="images/favicon.png" />

    </head>

    <body>

        <div id="message" class="message-container"></div>
        <div class="container-scroller">
            <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
                <div class="navbar-brand-wrapper d-flex justify-content-center">
                    <div class="navbar-brand-inner-wrapper d-flex justify-content-between align-items-center w-100">
                        <a class="navbar-brand brand-logo" href="index.html"><img src="images/logo.svg"
                                alt="logo" /></a>
                        <a class="navbar-brand brand-logo-mini" href="index.html"><img src="images/logo-mini.svg"
                                alt="logo" /></a>
                        <button class="navbar-toggler navbar-toggler align-self-center" type="button"
                            data-toggle="minimize">
                            <span class="mdi mdi-sort-variant"></span>
                        </button>
                    </div>
                </div>
                <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                    <ul class="navbar-nav mr-lg-4 w-100">
                        <li class="nav-item nav-search d-none d-lg-block w-100">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="search">
                                        <i class="mdi mdi-magnify"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" placeholder="Search now" aria-label="search"
                                    aria-describedby="search">
                            </div>
                        </li>
                    </ul>
                    <ul class="navbar-nav navbar-nav-right">

                        <li class="nav-item nav-profile dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown"
                                id="profileDropdown">
                                <img src="images/faces/face5.jpg" alt="profile" />

                                <span id="username"></span>

                            </a>
                            <div class="dropdown-menu dropdown-menu-right navbar-dropdown"
                                aria-labelledby="profileDropdown">

                                <a class="dropdown-item" id="logout">
                                    <i class="mdi mdi-logout text-primary"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                        data-toggle="offcanvas">
                        <span class="mdi mdi-menu"></span>
                    </button>
                </div>
            </nav>
            <div class="container-fluid page-body-wrapper">

                <nav class="sidebar sidebar-offcanvas" id="sidebar">
                    <ul class="nav">

                        <li class="nav-item" id="dashboardMenu">
                            <a class="nav-link" href="{{ route('dashboardpage') }}">
                                <i class="ri-dashboard-line menu-icon"></i>
                                <span class="menu-title">Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item" id="userMenu">
                            <a class="nav-link" href="{{ route('user-list') }}">
                                <i class="ri-shield-user-line menu-icon"></i>
                                <span class="menu-title">User Management</span>
                            </a>
                        </li>

                        <li class="nav-item" id="roleMenu">
                            <a class="nav-link" href="{{ route('role') }}">
                                <i class="ri-shield-keyhole-line menu-icon"></i>
                                <span class="menu-title">Role Management</span>
                            </a>
                        </li>

                        <li class="nav-item" id="balanceSheetMenu">
                            <a class="nav-link" href="{{ route('balancesheet') }}">
                                <i class="ri-wallet-2-line menu-icon"></i>
                                <span class="menu-title">Balance Sheet</span>
                            </a>
                        </li>

                    </ul>
                </nav>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

                <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

                <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

                @yield('content')

            </div>

        </div>

        <script>
            document.addEventListener("DOMContentLoaded", async function() {
                let user = localStorage.getItem("user");

                if (!user) {
                    window.location.href = "/";
                    return;
                }

                let userData = JSON.parse(user);
                let roleId = userData.role_id;

                try {
                    let response = await fetch("http://127.0.0.1:8000/api/role/getRole", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            role_id: roleId
                        })
                    });

                    let result = await response.json();

                    if (response.ok) {
                        let rolePermissions = result.data;

                        if (rolePermissions) {
                            let permissions = rolePermissions.permissions.flat().map(p => p.name);

                            const menuItems = {
                                "Dashboard": "dashboardMenu",
                                "User": "userMenu",
                                "Role": "roleMenu",
                                "Account": "balanceSheetMenu"
                            };

                            Object.entries(menuItems).forEach(([permission, menuId]) => {
                                let menuItem = document.getElementById(menuId);
                                if (menuItem) {
                                    menuItem.addEventListener("click", (event) => {
                                        if (!permissions.includes(permission)) {
                                            event.preventDefault();
                                            event.stopPropagation();
                                            showMessage(
                                                'You do not have permission to access this page',
                                                'error');
                                        }
                                    });
                                }
                            });

                        }
                    } else {
                        console.error("Failed to load permissions:", result);
                    }
                } catch (error) {
                    console.error("Error fetching roles and permissions:", error);
                }

                document.getElementById("username").innerText = userData.name;
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


            document.getElementById("logout").addEventListener("click", async function() {
                try {
                    let token = localStorage.getItem("token");

                    if (!token) {
                        window.location.href = "/";
                        return;
                    }

                    let response = await fetch("/api/logout", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Authorization": "Bearer " + token
                        },
                        body: JSON.stringify({
                            token: token
                        })
                    });

                    let result = await response.json();

                    if (response.status === 200) {
                        localStorage.removeItem("token");
                        localStorage.removeItem("user");
                        localStorage.removeItem("permissions");
                        localStorage.setItem("logoutMessage", "User logged out successfully....");
                        window.location.href = "/";
                    } else {
                        console.error("Logout failed:", result.message);
                    }
                } catch (error) {
                    console.error("Error during logout:", error);
                }
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="vendors/base/vendor.bundle.base.js"></script>

        <script src="vendors/chart.js/Chart.min.js"></script>
        <script src="vendors/datatables.net/jquery.dataTables.js"></script>
        <script src="vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>

        <script src="js/off-canvas.js"></script>
        <script src="js/hoverable-collapse.js"></script>
        <script src="js/template.js"></script>

        <script src="js/dashboard.js"></script>
        <script src="js/data-table.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
            integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
            integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
        </script>

    </body>

</html>
