<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>Registration Page</title>
</head>
<body>
    <div class="container">
        <h2>Welcome to Registration Page</h2>

        @if(session('error'))
            <p style="color:red">{{ session('error') }}</p>
        @endif
        @if(session('success'))
            <p style="color: green">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <ul style="color:red;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf

            <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
            <input type="password" name="password" placeholder="Password" required>

            <label>Role:</label>
            <select name="role" required>
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
