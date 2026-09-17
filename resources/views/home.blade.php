@extends('layout.app')

@section('title', 'Home')

@section('content')
    <h2>Welcome to the Lecturer Management System</h2>

    @if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
        <div class="alert error">
             {{ session('error') }}
        </div>
    @endif

    @if ($user)
        <p>You are logged in as <strong>{{ $user->role }}</strong>.</p>
    @endif
@endsection
