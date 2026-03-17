<x-layouts.guest>
    <div class="min-h-screen bg-base-100 text-base-content flex items-center justify-center px-4">
        <div class="w-full max-w-xl text-center space-y-6">
            <div class="inline-flex items-center justify-center rounded-2xl bg-primary/10 text-primary px-5 py-2">
                <span class="text-3xl md:text-4xl font-black tracking-wider">{{ $code }}</span>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl md:text-3xl font-bold">{{ $title }}</h1>
                <p class="text-sm md:text-base text-base-content/70">{{ $message }}</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm sm:btn-md">Ke Login</a>
                <a href="{{ url('/') }}" class="btn btn-ghost border border-base-300 btn-sm sm:btn-md">Ke Beranda</a>
            </div>
        </div>
    </div>
</x-layouts.guest>
