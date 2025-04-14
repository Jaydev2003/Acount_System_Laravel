<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Password</title>
        <link rel="stylesheet" href="{{ asset('css/resetpassword.css') }}">
    </head>

    <body>

        <div class="reset-container">
            <h2>Reset Your Password</h2>
            <form id="resetPasswordForm" method="POST">
                @csrf
                <input type="hidden" name="token" id="token" value="{{ request()->query('token') }}">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn" id="submitBtn">
                        Reset Password
                        <div class="loader" id="loader"></div>
                    </button>
                </div>

                <div class="message" id="messageBox"></div>
            </form>
        </div>

        <script>
            document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                let email = document.getElementById('email').value.trim();
                let password = document.getElementById('password').value.trim();
                let token = document.getElementById('token').value;
                let submitBtn = document.getElementById('submitBtn');
                let loader = document.getElementById('loader');
                let messageBox = document.getElementById('messageBox');

                if (!email || !password || !token) {
                    showMessage('Please fill all fields.', 'error');
                    return;
                }

                submitBtn.disabled = true;
                loader.style.display = 'inline-block';

                try {
                    let response = await fetch("http://127.0.0.1:8000/api/reset-password", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                        },
                        body: JSON.stringify({
                            email,
                            password,
                            token
                        }),
                    });

                    let data = await response.json();

                    if (response.status === 200) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = "/";
                        }, 2000);
                    } else {
                        showMessage(data.message || "Something went wrong. Please try again.", 'error');
                    }
                } catch (error) {
                    console.error("Reset Password Error:", error);
                    showMessage("Failed to reset password. Please try again later.", 'error');
                }

                submitBtn.disabled = false;
                loader.style.display = 'none';
            });

            function showMessage(message, type) {
                let messageBox = document.getElementById('messageBox');
                messageBox.textContent = message;
                messageBox.className = `message ${type}`;
                messageBox.style.display = 'block';
            }
        </script>

    </body>

</html>
