@extends('voting.layouts.app')
@section('title', 'Login')

@section('content')
<div class="max-w-sm mx-auto mt-12">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
    
    <form method="POST" action="{{ route('voting.login.post') }}" class="bg-white p-6 rounded-lg shadow-sm">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Masuk</button>
    </form>
</div>
@endsection