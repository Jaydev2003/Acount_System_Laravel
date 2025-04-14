<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Your Password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(10, 75, 90, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        h1 {
            color: #ffb144;
            margin-bottom: 15px;
        }

        p {
            color: #32495e;
            font-size: 16px;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 12px;
            background-color: #ffaa33;
            color: white;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        a:hover {
            background-color: #405d7d;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Password Reset Request</h1>
        <p>Click the button below to reset your password:</p>
        <a href="{{ url('/reset-password-form?token=' . $token) }}">Reset Password</a>
        <p>If you did not request this, please ignore this email.</p>
    </div>

</body>
</html>
