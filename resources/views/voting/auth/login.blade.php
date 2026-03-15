@extends('voting.layouts.app')
@section('title', 'Login')

@section('content')
    <div class="max-w-sm mx-auto mt-12 relative">
        <div class="absolute -top-8 -left-8 w-16 h-16 rounded-full border-4 border-ink bg-primary-yellow -z-10"></div>
        <h1 class="text-3xl font-display font-black uppercase tracking-tight text-center mb-6">Login</h1>

        <x-card shadow="xl" border="thick" accent="square">
            <form method="POST" action="{{ route('voting.login.post') }}">
                @csrf
                <div class="mb-4">
                    <x-label value="Email" />
                    <x-input type="email" name="email" required />
                </div>
                <div class="mb-6">
                    <x-label value="Password" />
                    <x-input type="password" name="password" required />
                </div>
                <x-button type="submit" variant="primary" class="w-full">
                    Masuk
                </x-button>
            </form>
        </x-card>
    </div>
@endsection
