<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login Page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

</head>

<body>

    <div id="message" class="message-container"></div>

    <div id="fullScreenLoader">
        <div class="loader"></div>
    </div>

    <div class="container">
        <div class="left-section"></div>
        <div class="right-section">
            <div class="login-container">
                <form class="login-form" method="post">
                    @csrf
                    <h2 class="login-title"><i class="fas fa-user-circle"></i> Login</h2>
                    <div class="form-control">
                        <i class="fas fa-user icon"></i>
                        <input type="text" placeholder="Email" id="username" required>
                    </div>
                    <div class="form-control">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" placeholder="Password" id="password">
                    </div>
                    <div class="forgot-password" onclick="forgotPassword()">
                        <a href="#">Forgot Password?</a>
                    </div>
                    <button type="submit" class="submit">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector(".login-form").addEventListener("submit", async function (event) {
                event.preventDefault();

                let email = document.getElementById("username").value.trim();
                let password = document.getElementById("password").value.trim();
                let messageDiv = document.getElementById("message");
                let loader = document.getElementById("fullScreenLoader");

                messageDiv.style.display = "none";
                messageDiv.textContent = "";

                if (!email || !password) {
                    showMessage("Please enter both email and password.", "error");
                    return;
                }

                loader.style.display = "flex";

                try {
                    let response = await fetch("{{ url('/api/login') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            email,
                            password
                        }),
                    });

                    let data = await response.json();

                    if (data.status === 200) {
                        showMessage("Login successful. Redirecting...", "success");
                        localStorage.setItem("email", email);
                        setTimeout(() => {
                            window.location.href = "/verify-otp";
                        }, 1000);
                    } else {
                        showMessage(data.message || "Invalid credentials. Please try again.", "error");
                    }
                } catch (error) {
                    showMessage("Something went wrong. Please try again later.", "error");
                } finally {
                    loader.style.display = "none";
                }
            });
        });


        let logoutMessage = localStorage.getItem("logoutMessage");

        if (logoutMessage) {

            showMessage(logoutMessage, "success");
            localStorage.removeItem("logoutMessage");
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

        function forgotPassword() {
            window.location.href = "/forgot-password";
        }
    </script>
</body>

</html>