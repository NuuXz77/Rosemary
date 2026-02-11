<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Page Title' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    {{-- <div class="absolute inset-0 -z-10 h-full w-full items-center px-5 py-24 [background:radial-gradient(125%_125%_at_50%_10%,#000_40%,#63e_100%)]"></div> --}}
    {{ $slot }}
    @livewireScripts

    <script>
        document.addEventListener('livewire:navigated', () => {
            console.log('Halaman telah pindah atau dimuat!');
            // Inisialisasi JS Anda di sini
        }, {
            once: true
        });
    </script>
</body>

</html>
