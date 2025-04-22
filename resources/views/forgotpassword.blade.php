<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/forgotpassword.css') }}">
</head>

<body>
    <!-- Message Box -->
    <div id="message" class="message-container" style="display: none;"></div>

    <!-- Fullscreen Loader -->
    <div id="fullScreenLoader" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Form Section -->
    <div class="container">
        <div class="forgot-password-container">
            <h2><i class="fa fa-lock"></i> Forgot Password?</h2>
            <p style="text-align: center; color: #32495e;">Enter your email to reset your password.</p>

            <form id="forgotPasswordForm">
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="forgotEmail" placeholder="Enter your email" required>
                </div>
                <button type="submit">Send Reset Link</button>
            </form>
        </div>
    </div>

    <!-- Script for AJAX and Message Handling -->
    <script>
      document.getElementById('forgotPasswordForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const email = document.getElementById('forgotEmail').value.trim();
    const loader = document.getElementById("fullScreenLoader");
    const messageDiv = document.getElementById("message");

    messageDiv.style.display = "none";
    messageDiv.textContent = "";

    if (!email) {
        showMessage("Please enter your email address.", "error");
        return;
    }

    loader.style.display = "flex";

    try {
        const response = await fetch('/api/forgot-password', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            body: JSON.stringify({ email })
        });

        const data = await response.json();

        

        if (data.status === 200) {
            showMessage("A password reset link has been sent to your email.", "success");
        } else {
            if (data.status === 404) {
                showMessage(data.message || "Email address not found in our records.", "error");
            } else if (data.status === 422) {
                if (data.errors && data.errors.email) {
                    showMessage(data.errors.email[0], "error");
                } else {
                    showMessage("Something went wrong. Please try again.", "error");
                }
            } else {
                showMessage(data.message || "Something went wrong. Please try again.", "error");
            }
        }
    } catch (error) {
        showMessage("Failed to send password reset email. Please try again later.", "error");
    } finally {
        loader.style.display = "none";
    }
});

function showMessage(message, type) {
    const messageDiv = document.getElementById("message");
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
