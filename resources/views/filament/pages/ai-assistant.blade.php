<x-filament-panels::page>
    <div class="flex flex-col gap-4">
        @if (count($messages) > 0)
            <div class="flex justify-end">
                <x-filament::button color="gray" size="sm" wire:click="resetConversation">
                    Nouvelle conversation
                </x-filament::button>
            </div>
        @endif

        <x-filament::section>
            <div class="flex flex-col gap-4">
                @forelse ($messages as $message)
                    <div @class([
                        'flex',
                        'justify-end' => $message['role'] === 'user',
                        'justify-start' => $message['role'] === 'assistant',
                    ])>
                        <div @class([
                            'max-w-2xl rounded-lg px-4 py-2 text-sm prose dark:prose-invert prose-sm',
                            'bg-primary-600 text-white prose-invert' => $message['role'] === 'user',
                            'bg-gray-100 dark:bg-gray-800' => $message['role'] === 'assistant',
                        ])>
                            {!! nl2br(e($message['content'])) !!}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pose une question sur tes clients pour démarrer la conversation.
                    </p>
                @endforelse

                <div wire:loading wire:target="ask" class="flex justify-start">
                    <div class="max-w-2xl rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        L'assistant réfléchit…
                    </div>
                </div>
            </div>
        </x-filament::section>

        <form wire:submit="ask" class="flex items-start gap-2">
            <div class="flex-1">
                {{ $this->form }}
            </div>

            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="ask">
                Envoyer
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
