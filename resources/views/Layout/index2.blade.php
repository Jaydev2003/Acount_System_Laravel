<!DOCTYPE html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Account System</title>
        <!-- plugins:css -->
        <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
        <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
        <!-- endinject -->
        <!-- plugin css for this page -->
        <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
        <!-- End plugin css for this page -->
        <!-- inject:css -->
        <link rel="stylesheet" href="css/style.css">
        <!-- endinject -->
        <link rel="shortcut icon" href="images/favicon.png" />
    </head>

    <body>

        <div id="message" class="message-container"></div>

        <div class="container-scroller">
            <!-- partial:partials/_navbar.html -->
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
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_sidebar.html -->
                <nav class="sidebar sidebar-offcanvas" id="sidebar">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="index.html">
                                <i class="mdi mdi-home menu-icon"></i>
                                <span class="menu-title">Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="  ">
                                <i class="mdi mdi-grid-large menu-icon"></i>
                                <span class="menu-title">Admin</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="collapse" href="#userMenu" aria-expanded="false"
                                aria-controls="userMenu">
                                <i class="mdi mdi-account menu-icon"></i>
                                <span class="menu-title">User</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="collapse" id="userMenu">
                                <ul class="nav flex-column sub-menu">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-list">User List</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="collapse" href="#rolemenu" aria-expanded="false"
                                aria-controls="rolemenu">
                                <i class="mdi mdi-account menu-icon"></i>
                                <span class="menu-title">Role Management</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="collapse" id="rolemenu">
                                <ul class="nav flex-column sub-menu">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-list">Role List</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="collapse" id="rolemenu">
                                <ul class="nav flex-column sub-menu">
                                    <li class="nav-item">
                                        <a class="nav-link" href="/user-list">Permission List</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    </ul>
                </nav>
                <!-- partial -->
                @yield('content')
                <!-- main-panel ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                let logoutMessage = localStorage.getItem("loginMsg");

                if (logoutMessage) {

                    showMessage(logoutMessage, "success");
                    localStorage.removeItem("loginMsg");
                }

                let token = localStorage.getItem("token");
                let user = localStorage.getItem("user");

                if (token && user) {
                    let userData = JSON.parse(user);
                    document.getElementById("username").innerText = userData.name;
                } else {
                    window.location.href = "/login";
                }

                document.getElementById("logout").addEventListener("click", async function() {
                    try {
                        let token = localStorage.getItem("token");

                        if (!token) {
                            window.location.href = "/login";
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
                            localStorage.setItem("logoutMessage", "User logged out successfully....");
                            window.location.href = "/login";
                        } else {
                            console.error("Logout failed:", result.message);
                        }
                    } catch (error) {
                        console.error("Error during logout:", error);
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
        </script>

        <!-- plugins:js -->
        <script src="vendors/base/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page-->
        <script src="vendors/chart.js/Chart.min.js"></script>
        <script src="vendors/datatables.net/jquery.dataTables.js"></script>
        <script src="vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
        <!-- End plugin js for this page-->
        <!-- inject:js -->
        <script src="js/off-canvas.js"></script>
        <script src="js/hoverable-collapse.js"></script>
        <script src="js/template.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page-->
        <script src="js/dashboard.js"></script>
        <script src="js/data-table.js"></script>
        <script src="js/jquery.dataTables.js"></script>
        <script src="js/dataTables.bootstrap4.js"></script>
        <!-- End custom js for this page-->
    </body>

</html>
