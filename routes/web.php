<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lecturer;
use App\Http\Controllers\DocumentationController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Session::has('user')) return redirect()->route('home');
    return redirect()->route('login');
});

// Show login form
Route::get('/login', function () {
    if (Session::has('user')) return redirect()->route('home');
    return view('login');
})->name('login');

// Handle login submission
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withInput($request->only('email'))->with('error', 'Invalid credentials.');
    }

    Session::flush();
    Session::put('user', $user);
    Session::put('user_id', $user->id);

    return redirect()->route('home')->with('success', 'Login successful!');
})->name('login.submit');

// Logout
Route::get('/logout', function () {
    Session::flush();
    return redirect()->route('login')->with('success', 'You have logged out.');
})->name('logout');

// Show register form
Route::get('/register', function () {
    if (Session::has('user')) return redirect()->route('home');
    return view('register');
})->name('register');

// Handle register submission
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'role' => 'required|in:admin,user',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
    ]);

    Lecturer::firstOrCreate(
        ['email' => $request->email],
        [
            'name' => $request->name,
            'department' => 'Unassigned',
        ]
    );

    return redirect()->route('login')->with('success', 'Account created. Please log in.');
})->name('register.submit');



/*
|--------------------------------------------------------------------------
| System Routes (Requires Login)
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/home', function () {
    if (!Session::has('user')) return redirect()->route('login');

    $user = Session::get('user');
    return view('home', compact('user'));
})->name('home');

// Show all lecturers
Route::get('/lecturers', function () {
    if (!Session::has('user')) return redirect()->route('login');

    $lecturers = Lecturer::all();
    $user = Session::get('user');

    return view('lecturers', compact('lecturers', 'user'));
})->name('lecturers');

// Show add lecturer form (admin only)
Route::get('/lecturer/add', function () {
    $user = Session::get('user');
    if (!$user || $user->role !== 'admin') return redirect()->route('lecturers');

    return view('lectureradd');
})->name('lecturer.add');

// Handle add lecturer (admin only)
Route::post('/lecturer/add', function (Request $request) {
    $user = Session::get('user');
    if (!$user || $user->role !== 'admin') return redirect()->route('lecturers');

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:lecturers,email',
        'department' => 'required|string|max:255',
    ]);

    Lecturer::create([
        'name' => $request->name,
        'email' => $request->email,
        'department' => $request->department,
    ]);

    return redirect()->route('lecturers')->with('success', 'Lecturer added successfully.');
})->name('lecturer.store');

// Show edit lecturer form (admin only)
Route::get('/lecturer/edit/{id}', function ($id) {
    $user = Session::get('user');
    if (!$user || $user->role !== 'admin') return redirect()->route('lecturers');

    $lecturer = Lecturer::findOrFail($id);
    return view('lectureredit', compact('lecturer'));
})->name('lecturer.edit');

// Handle update lecturer (admin only)
Route::post('/lecturer/edit/{id}', function (Request $request, $id) {
    $user = Session::get('user');
    if (!$user || $user->role !== 'admin') return redirect()->route('lecturers');

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:lecturers,email,' . $id,
        'department' => 'required|string|max:255',
    ]);

    $lecturer = Lecturer::findOrFail($id);
    $lecturer->update([
        'name' => $request->name,
        'email' => $request->email,
        'department' => $request->department,
    ]);

    return redirect()->route('lecturers')->with('success', 'Lecturer updated successfully.');
})->name('lecturer.update');

// Handle delete lecturer (admin only)
Route::post('/lecturer/delete/{id}', function ($id) {
    $user = Session::get('user');
    if (!$user || $user->role !== 'admin') return redirect()->route('lecturers');

    Lecturer::destroy($id);
    return redirect()->route('lecturers')->with('success', 'Lecturer deleted successfully.');
})->name('lecturer.delete');

Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');