@extends('layout.account')
@section('content')
    <link rel="stylesheet" href="{{"css/admin.css"}}">

    <div class="main-panel">
        <div class="content-wrapper">
            <div id="admin-details" class="admin-card">
                <div class="admin-content">
                    <img id="admin-avatar" class="admin-avatar" src="images/faces/face5.jpg" alt="Admin Avatar">
                    <h3 id="admin-name" class="admin-name"></h3>
                    <p id="admin-email" class="admin-email"></p>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let token = localStorage.getItem("token");
            let user = localStorage.getItem("user");

            if (token && user) {
                let userData = JSON.parse(user);
                document.getElementById("username").innerText = userData.name;


                document.getElementById("admin-name").innerText = userData.name;
                document.getElementById("admin-email").innerText = `Email: ${userData.email}`;



                setTimeout(() => {
                    document.getElementById("admin-details").classList.add("show");
                }, 300);
            } else {
                window.location.href = "/";
            }
        });
    </script>

@endsection