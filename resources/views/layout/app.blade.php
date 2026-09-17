<!DOCTYPE html>
<html>
<head>
    <title>@yield('title') - Lecturer Management System</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <div class="container">
        <h1>Lecturer Management System</h1>

        @if(Session::has('user'))
    <p>
        Welcome, {{ Session::get('user')->name }} ({{ Session::get('user')->role }})
    </p>

    <div class="nav-buttons">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('lecturers') }}">Lecturers</a>
        @if(Session::get('user')->role === 'admin')
            <a href="{{ route('lecturer.add') }}">➕ Add Lecturer</a>
        @endif
        <a href="{{ route('logout') }}">Logout</a>
    </div>
@endif

        <hr>
        

        @yield('content')
    </div>
</body>
</html>
