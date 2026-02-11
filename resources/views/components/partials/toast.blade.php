@props(['success' => null, 'error' => null])

<div class="toast toast-start z-[9999]" 
     x-data="{
        toasts: [],
        init() {
            // Listen to Livewire events
            Livewire.on('show-toast', (event) => {
                let type, message;
                
                // Handle both object and array format
                if (Array.isArray(event)) {
                    type = event[0]?.type || 'info';
                    message = event[0]?.message || '';
                } else {
                    type = event.type || 'info';
                    message = event.message || '';
                }
                
                this.addToast(type, message);
            });
        },
        addToast(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message, show: true });
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts[index].show = false;
                    setTimeout(() => {
                        this.toasts.splice(index, 1);
                    }, 300);
                }
            }, 5000);
        }
     }">
    
    {{-- Session Flash Messages --}}
    @if ($success)
        <div wire:key="success-{{ now()->timestamp }}" class="alert alert-success flex flex-row items-center"
            x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
            <x-heroicon-o-check class="w-5" />
            <span>{{ $success }}</span>
        </div>
    @endif

    @if ($error)
        <div wire:key="error-{{ now()->timestamp }}" class="alert alert-error flex flex-row items-center"
            x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
            <x-heroicon-o-x-circle class="w-5" />
            <span>{{ $error }}</span>
        </div>
    @endif

    {{-- Dynamic Toast dari Livewire Events --}}
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show" 
             x-transition
             class="alert flex flex-row items-center"
             :class="{
                'alert-success': toast.type === 'success',
                'alert-error': toast.type === 'error',
                'alert-warning': toast.type === 'warning',
                'alert-info': toast.type === 'info'
             }">
            
            {{-- Icons --}}
            <template x-if="toast.type === 'success'">
                <x-heroicon-o-check class="w-5" />
            </template>
            <template x-if="toast.type === 'error'">
                <x-heroicon-o-x-circle class="w-5" />
            </template>
            <template x-if="toast.type === 'warning'">
                <x-heroicon-o-exclamation-triangle class="w-5" />
            </template>
            <template x-if="toast.type === 'info'">
                <x-heroicon-o-information-circle class="w-5" />
            </template>
            
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
