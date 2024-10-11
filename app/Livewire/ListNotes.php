<?php

namespace App\Livewire;

use App\Models\Note;
use App\Services\OpenAIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ListNotes extends Component
{
    use WithPagination;

    /**
     * The search parameter for searching by patient_id
     * History true indicates that livewire should not override history
     *
     * @var ?string
     */
    #[Url(history: true)]
    public ?string $search = null;

    /**
     * Returns list nodes view with user's team notes
     *
     * @return View
     */
    public function render(): View
    {
        $notes = Note::query()->where('current_team_id', request()->user()->currentTeam->id);

        if ($this->search) {
            $notes->where('patient_id', $this->search);
        }

        return view('livewire.list-notes', [
            'notes' => $notes->paginate($this->perPage ?? 10),
        ])->layout('layouts.app');
    }
}
