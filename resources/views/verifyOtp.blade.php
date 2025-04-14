<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Verify OTP</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- FontAwesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <link rel="stylesheet" href="{{ asset('css/verifyotp.css') }}">
    </head>

    <body>

        <!-- Message box -->
        <div id="message" class="message-container"></div>

        <div id="fullScreenLoader">
            <div class="loader"></div>
        </div>

        <div class="container">
            <div class="left-section"></div>
            <div class="right-section">
                <div class="otp-container">
                    <h2 class="otp-title"><i class="fas fa-key"></i> Enter OTP</h2>
                    <div class="form-control">
                        <i class="fas fa-key"></i>
                        <input type="text" id="otp" placeholder="Enter OTP">
                    </div>
                    <button id="verifyOtpBtn">Verify OTP</button>
                </div>
            </div>
        </div>

        <script>
            document.getElementById("verifyOtpBtn").addEventListener("click", async function() {
                let otp = document.getElementById("otp").value.trim();
                let email = localStorage.getItem("email");
                let loader = document.getElementById("fullScreenLoader");

                let messageDiv = document.getElementById("message");
                messageDiv.style.display = "none";
                messageDiv.textContent = "";

                if (!otp) {
                    showMessage("Please enter the OTP.", "error");
                    return;
                }

                loader.style.display = "flex";

                try {
                    let response = await fetch("{{ url('/api/verify-otp') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                        },
                        body: JSON.stringify({
                            email: email,
                            otp: otp
                        }),
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP Error! Status: ${response.status}`);
                    }

                    let data = await response.json();

                    if (data.status === 200) {
                        localStorage.setItem("token", data.token);
                        localStorage.setItem("user", JSON.stringify(data.data));
                        localStorage.setItem("loginMsg", "User login successfully....");

                        showMessage("OTP verified. Fetching permissions...", "success");

                        await checkUserPermission();

                        setTimeout(() => {
                            window.location.href = "/dashboard";
                        }, 1000);
                    } else {
                        showMessage(data.message || "Invalid OTP. Please try again.", "error");
                    }

                } catch (error) {
                    showMessage("Something went wrong. Please try again.", "error");
                } finally {
                    loader.style.display = "none";
                }
            });

            async function checkUserPermission() {
                let token = localStorage.getItem("token");
                let userdata = localStorage.getItem("user");

                if (!userdata) {
                    showMessage("User data not found.", "error");
                    return;
                }

                try {
                    let user = JSON.parse(userdata);
                    let roleId = user.role_id;

                    if (!roleId) {
                        showMessage("Role ID not found.", "error");
                        return;
                    }

                    let response = await fetch("http://127.0.0.1:8000/api/role/getRole", {
                        method: "POST",
                        headers: {
                            "Authorization": "Bearer " + token,
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            role_id: roleId
                        }),
                    });

                    let data = await response.json();

                    if (data.status === 200 && data.data && Array.isArray(data.data.permissions)) {
                        let permissions = data.data.permissions.map(p => p.slug || "").filter(p => p !== "");

                        localStorage.setItem("permissions", JSON.stringify(permissions));

                        console.log("Permissions stored in localStorage:", localStorage.getItem("permissions"));
                    } else {
                        console.error("Permissions data is invalid or missing:", data);
                    }
                } catch (error) {
                    console.error("Error checking user permission:", error);
                }
            }

            function showMessage(message, type) {
                let messageDiv = document.getElementById("message");
                messageDiv.textContent = message;
                messageDiv.className = `message-container ${type}-message`;
                messageDiv.style.display = "block";
                setTimeout(() => {
                    messageDiv.style.display = "none";
                }, 5000);
            }
        </script>

    </body>

</html>
