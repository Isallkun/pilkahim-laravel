@extends('layouts.arutala')

@section('title', 'Belum Ada Pemilihan')

@section('body')
<div class="min-h-screen flex items-center justify-center px-4 bg-[#FFFBEA]">
    <div class="text-center max-w-md">
        <div class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-gray-400 text-[40px]">event_busy</span>
        </div>
        <h1 class="text-2xl font-bold text-[#2D2A24] mb-3">Belum Ada Pemilihan Aktif</h1>
        <p class="text-[#5C5648] mb-6">Saat ini belum ada pemilihan yang sedang berlangsung. Silakan cek kembali nanti.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-primary px-6 py-3">Logout</button>
        </form>
    </div>
</div>
@endsection
