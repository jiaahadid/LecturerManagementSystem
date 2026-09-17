@extends('layout.app')

@section('title', 'Edit Lecturer')

@section('content')
<link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <h2>Edit Lecturer</h2>

    @if ($errors->any())
        <ul style="color:red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('lecturer.update', $lecturer->id) }}">
        @csrf
        <label>Name:</label><br>
        <input type="text" name="name" value="{{ old('name', $lecturer->name) }}" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email', $lecturer->email) }}" required><br><br>

        <label>Department:</label><br>
        <input type="text" name="department" value="{{ old('department', $lecturer->department) }}" required><br><br>

        <button type="submit">Update Lecturer</button>
    </form>
@endsection
