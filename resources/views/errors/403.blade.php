@extends('layouts.public')

@section('title', '403 - Akses Ditolak')

@section('content')
<div class="flex min-h-[60vh] items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary-100">
            <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900">403</h1>
        <p class="mt-2 text-lg font-semibold text-primary-600">Akses Ditolak</p>
        <p class="mt-2 text-base text-gray-600">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
        </p>
        <div class="mt-8">
            <a href="{{ url('/') }}"
               class="inline-flex items-center rounded-md bg-primary-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-600 transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
