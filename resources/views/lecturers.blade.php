@extends('layout.app')

@section('title', 'Lecturers')

@section('content')
<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<h2>Lecturer List</h2>

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

<table border="1" cellpadding="10">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        @if(session('user')->role === 'admin')
            <th>Actions</th>
        @endif
    </tr>
    @foreach($lecturers as $lecturer)
        <tr>
            <td>{{ $lecturer->name }}</td>
            <td>{{ $lecturer->email }}</td>
            <td>{{ $lecturer->department }}</td>
            @if(session('user')->role === 'admin')
                <td>
                    <a href="{{ route('lecturer.edit', $lecturer->id) }}">Edit</a> |
                    <a href="#"
                       onclick="event.preventDefault(); document.getElementById('delete-form-{{ $lecturer->id }}').submit();">
                       Delete
                    </a>

                    <form id="delete-form-{{ $lecturer->id }}"
                          action="{{ route('lecturer.delete', $lecturer->id) }}"
                          method="POST"
                          style="display: none;">
                        @csrf
                    </form>
                </td>
            @endif
        </tr>
    @endforeach
</table>
@endsection
