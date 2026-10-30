@extends('layouts.app')

{{--
    Kerangka halaman panel admin.

    Memakai kerangka yang sama dengan Beranda, Dashboard, dan SOP: satu
    kolom selebar maksimal 7xl di tengah halaman. Navigasi panel ada di
    navbar utama (lihat layouts/partials/nav-utama), bukan sidebar, jadi
    tidak ada kolom samping di sini.
--}}
@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4">
    @yield('admin-content')
</main>
@stop

@section('modals')
    @yield('admin-modals')
@stop

@push('scripts')
    @stack('admin-scripts')
@endpush
