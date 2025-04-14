<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Account Credentials</title>
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

        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 12px;
            background-color: #ffaa33;
            color: white;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #405d7d;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Welcome, {{ $name }}!</h1>
        <p>Your account has been successfully created.</p>

        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Password:</strong> {{ $password }}</p>

        <a class="btn" href="{{ url('/') }}">Login Now</a>

        <p>Please change your password after logging in for security reasons.</p>
        <p>If you did not sign up, please ignore this email.</p>
    </div>

</body>
</html>
