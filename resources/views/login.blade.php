<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>Login</title>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        @if(session('error'))
            <p style="color:red">{{ session('error') }}</p>
        @endif
        @if(session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @endif
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            Email: <input type="email" name="email" value="{{ old('email') }}" required><br><br>
            Password: <input type="password" name="password" required><br><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
    </div>
</body>
</html>
