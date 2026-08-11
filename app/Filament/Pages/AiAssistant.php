<?php

namespace App\Filament\Pages;

use App\Services\RagService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiAssistant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Assistant IA';

    protected static ?string $navigationGroup = 'CRM';

    protected static string $view = 'filament.pages.ai-assistant';

    /**
     * @var array<int, array{role: string, content: string}>
     */
    public array $messages = [];

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('question')
                    ->label('')
                    ->placeholder('Écris ta question… (ex : quels clients à Paris ont une adresse email renseignée ?)')
                    ->rows(2)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function ask(): void
    {
        $state = $this->form->getState();
        $question = trim((string) ($state['question'] ?? ''));

        if ($question === '') {
            return;
        }

        $history = $this->messages;

        $this->messages[] = ['role' => 'user', 'content' => $question];
        $this->form->fill();

        try {
            $answer = app(RagService::class)->ask($question, $history);
            $this->messages[] = ['role' => 'assistant', 'content' => $answer];
        } catch (\Throwable $e) {
            array_pop($this->messages);

            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetConversation(): void
    {
        $this->messages = [];
    }
}
