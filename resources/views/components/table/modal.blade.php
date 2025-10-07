<div
        x-data="{
        open: false,
        modal: null,
        recordId: null
    }"
        @open-modal.window="
        open = true;
        modal = $event.detail.modal;
        recordId = $event.detail.recordId;
    "
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
>
    <!-- Overlay -->
    <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="open = false"
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
    ></div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                :class="{
                'max-w-sm': modal?.size === 'sm',
                'max-w-md': modal?.size === 'md',
                'max-w-2xl': modal?.size === 'lg',
                'max-w-4xl': modal?.size === 'xl',
                'max-w-full': modal?.size === 'full'
            }"
                class="relative bg-white rounded-lg shadow-xl w-full"
        >
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" x-text="modal?.title"></h3>
                <button
                        @click="open = false"
                        class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-4">
                <template x-if="modal?.type === 'info'">
                    <div class="text-gray-600" x-text="modal?.description"></div>
                </template>

                <template x-if="modal?.type === 'confirmation'">
                    <div>
                        <p class="text-gray-600 mb-4" x-text="modal?.description"></p>
                        <div class="flex justify-end space-x-3">
                            <button
                                    @click="open = false"
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Zrušit
                            </button>
                            <button
                                    @click="$wire.executeAction(modal.action, recordId); open = false"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            >
                                Potvrdit
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="modal?.type === 'form'">
                    <div>
                        <!-- Zde se vykreslí custom form view -->
                        <div x-html="modal?.view"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>