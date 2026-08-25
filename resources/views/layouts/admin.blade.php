<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Du lịch') }}</title>
    <link rel="icon" href="{{ $adminFavicon }}">
    <link rel="shortcut icon" href="{{ $adminFavicon }}">
    <link rel="stylesheet" href="{{ asset('vendor/overlayscrollbars/overlayscrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte4/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tom-select/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('css')
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        @include('admin.partials.header')
        @include('admin.partials.sidebar')
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('page-title', 'Tổng quan')</h3>
                        </div>
                        <div class="col-sm-6">@yield('breadcrumbs')</div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">@yield('content')</div>
            </div>
        </main>
        @include('admin.partials.footer')
    </div>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte4/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/dropzone/dropzone.min.js') }}"></script>
    <script>
        // Every admin uploader is initialized explicitly with its own endpoint and options.
        // Disable Dropzone's DOM auto-discovery before DOMContentLoaded to avoid a second,
        // URL-less instance being attached to elements that use the `.dropzone` class.
        Dropzone.autoDiscover = false;
    </script>
    <script src="{{ asset('vendor/sortable/sortable.min.js') }}"></script>
    <script src="{{ asset('vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('js')
    @if (session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: @json(session('success')),
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    @endif
</body>

</html>
