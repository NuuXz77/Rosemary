<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-base-300 via-base-200 to-base-300 px-4"
    x-data>

    {{-- Toast (inline so guest layout gets it too) --}}
    <x-partials.toast />

    {{-- Subtle grid overlay --}}
    <div class="pointer-events-none fixed inset-0 opacity-[0.03]"
        style="background-image:linear-gradient(#000 1px,transparent 1px),linear-gradient(90deg,#000 1px,transparent 1px);background-size:40px 40px">
    </div>

    <div class="relative z-10 w-full max-w-sm">

        {{-- Card --}}
        <div class="card bg-base-100/90 backdrop-blur-xl shadow-2xl border border-base-200 rounded-3xl overflow-hidden">

            {{-- Header Band --}}
            <div class="bg-gradient-to-r from-primary to-primary-focus px-6 py-5 flex flex-col items-center gap-1">
                <img src="{{ asset('img/logo.png') }}" class="w-14 h-14 object-contain drop-shadow" alt="Logo">
                <h1 class="text-primary-content font-black text-xl tracking-tight mt-1">Kasir POS</h1>
                <p class="text-primary-content/70 text-xs">Masukkan PIN 4 digit kamu</p>
            </div>

            <div class="p-6 space-y-6">

                {{-- PIN Dots --}}
                <div class="flex justify-center gap-4">
                    @foreach (range(0, 3) as $i)
                        <div
                            @class([
                                'w-4 h-4 rounded-full border-2 transition-all duration-200',
                                'bg-primary border-primary scale-110 shadow-[0_0_12px_2px] shadow-primary/40' => $i < count($digits),
                                'bg-transparent border-base-content/30' => $i >= count($digits),
                            ])>
                        </div>
                    @endforeach
                </div>

                {{-- Student greeting --}}
                <div class="min-h-[1.5rem] text-center">
                    @if ($studentName)
                        <p class="text-success font-bold text-sm animate-pulse">
                            <x-heroicon-o-check-circle class="w-4 h-4 inline mr-1" />
                            {{ $studentName }}
                        </p>
                    @endif
                </div>

                {{-- Keypad --}}
                <div class="grid grid-cols-3 gap-3">
                    @foreach (['1','2','3','4','5','6','7','8','9'] as $digit)
                        <button wire:click="addDigit('{{ $digit }}')"
                            class="btn btn-ghost border border-base-300 text-xl font-bold h-14 rounded-2xl hover:bg-primary hover:text-primary-content hover:border-primary active:scale-90 transition-all duration-150 shadow-sm"
                            {{ count($digits) >= 4 ? 'disabled' : '' }}>
                            {{ $digit }}
                        </button>
                    @endforeach

                    {{-- Bottom row: Clear | 0 | Backspace --}}
                    <button wire:click="clearPin"
                        class="btn btn-ghost border border-base-300 text-xs font-semibold h-14 rounded-2xl hover:bg-error hover:text-error-content hover:border-error active:scale-90 transition-all duration-150 shadow-sm">
                        CLR
                    </button>

                    <button wire:click="addDigit('0')"
                        class="btn btn-ghost border border-base-300 text-xl font-bold h-14 rounded-2xl hover:bg-primary hover:text-primary-content hover:border-primary active:scale-90 transition-all duration-150 shadow-sm"
                        {{ count($digits) >= 4 ? 'disabled' : '' }}>
                        0
                    </button>

                    <button wire:click="removeDigit"
                        class="btn btn-ghost border border-base-300 h-14 rounded-2xl hover:bg-warning hover:text-warning-content hover:border-warning active:scale-90 transition-all duration-150 shadow-sm">
                        <x-heroicon-o-backspace class="w-6 h-6" />
                    </button>
                </div>

                {{-- Divider --}}
                <div class="divider text-xs opacity-40 my-0">atau</div>

                {{-- Back to normal login --}}
                <a href="{{ route('login') }}" wire:navigate
                    class="btn btn-ghost btn-sm w-full rounded-xl text-xs opacity-60 hover:opacity-100 gap-2">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Login sebagai Admin
                </a>
            </div>
        </div>

        {{-- Bottom watermark --}}
        <p class="text-center text-[10px] text-base-content/30 mt-4">
            {{ config('app.name') }} &copy; {{ date('Y') }}
        </p>
    </div>
</div>
