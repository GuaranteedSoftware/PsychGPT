<?php

namespace App\Livewire;

use App\Models\Note;
use App\Services\OpenAIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PsychGpt extends Component
{
    public const TIME_FORMAT = 'Y-m-d H:i';

    /**
     * Form validation rules grouped by input wizard steps
     * step -> array of rules
     *
     * @var array<int, string[]>
     */
    public const STEP_RULES = [
        1 => [ # first step validation
            'patientId' => 'required',
            'startTime' => 'required',
            'endTime' => 'required',
            'noteType' => 'required',
            'actorType' => 'required',
            'age' => 'required',
            'gender' => 'required',
        ],
        2 => [ # second step validation
            'goals' => 'required',
        ]
    ];

    public string $patientId = '';

    public string $startTime = '';

    public string $endTime = '';

    public array $noteTypes = [
        'CPST',
        'TBSI',
        'Day Tx',
        'Individual',
    ];

    public string $noteType = '';

    public array $actorTypes = [
        'Therapist',
        'Clinitian',
    ];

    public string $actorType = '';

    public array $genders = [
        'Male',
        'Female',
        'Other',
    ];

    public string $gender = '';

    public string $age = '';

    public bool $clientDiagnosed = false;

    public string $diagnose = '';

    public string $goals = '';

    public string $comments = '';

    public string $recommendations = '';

    public string $behavior = '';

    public string $intervention = '';

    public string $clientResponse = '';

    public string $homeworkGiven = '';

    public string $genie = '';

    public string $generatedNote = '';

    public ?int $id;

    /**
     * The current step in the form wizard, preset to the first step
     *
     * @var int
     */
    public int $step = 1;

    /**
     * Increment - move to the next step, or go to the specific step if specified
     *
     * @param int|null $step optional, force positioning at the exact step
     * @return void
     */
    public function incrementStep(int $step = null)
    {
        $step = $step ?? $this->step + 1;
        $this->validate(self::STEP_RULES[$step-1]);
        $this->step = $step;
    }

    /**
     * Go to the previous step in the form wizard sequence
     *
     * @return void
     */
    public function decrementStep()
    {
        $this->step--;
    }

    public function mount($id = null) {
        $this->id = $id;
        $this->initialize();
    }

    public function initialize() {
        if ($this->id ?? false) {
            if ($note = Note::where(['id' => $this->id, 'current_team_id' => Auth::user()->currentTeam->id])?->first()){
                $this->id = $note->id;
                $this->patientId = $note->patient_id;
                $this->startTime = $note->start_time;
                $this->endTime = $note->end_time;
                $this->noteType = $note->note_type;
                $this->actorType = $note->actor_type;
                $this->age = $note->age;
                $this->gender = $note->gender;
                $this->clientDiagnosed = $note->client_diagnosed;
                $this->diagnose = $note->diagnose;
                $this->goals = $note->goals;
                $this->comments = $note->comments;
                $this->recommendations = $note->recommendations;
                $this->behavior = $note->behavior;
                $this->intervention = $note->intervention;
                $this->clientResponse = $note->client_response;
                $this->homeworkGiven = $note->homework_given;
                $this->genie = $note->genie;
                $this->generatedNote = $note->generated_note;
            }
        } else {
            $this->reset();
            $this->startTime = Carbon::now()->format(self::TIME_FORMAT);
            $this->endTime = Carbon::now()->format(self::TIME_FORMAT);
        }
    }

    /**
     * Generate the prompt by filling in the input data in the prompt template.
     * Generate the note by calling OpenAI API.
     * Stream the note as generated in the `generatedNote` property (mapped to the UI form field).
     *
     * @return void
     */
    public function generateNote() {
        /**
         * Got to the last (#3) wizard step, implicitly validating the previous (#2) step form data
         * The step number is specified because this method is triggered by `Generate Note`
         * and also by `Update Note` (Genie) which is already on the third step
         */
        $this->incrementStep(3);

        $prompt = view(
            'prompts.note',
            $this->only(
                [
                    'actorType',
                    'goals',
                    'age',
                    'gender',
                    'diagnose',
                    'noteType',
                    'startTime',
                    'endTime',
                    'behavior',
                    'intervention',
                    'comments',
                    'clientResponse',
                    'homeworkGiven',
                    'recommendations',
                    'genie',
                ]
            )
        )->render();

        $stream = \OpenAI\Laravel\Facades\OpenAI::completions()
            ->createStreamed(
                [
                    "model"      => "gpt-3.5-turbo-instruct-0914",
                    'prompt'     => $prompt,
                    'max_tokens' => 3000,
                ]
            );

        $note = '';
        # ensure the note field is cleared before populating it with the newly generated note
        $this->stream('generatedNote', $note, true);

        foreach ($stream as $response) {
            $reply = $response->choices[0]->text;
            $note .= $reply;
            $this->stream('generatedNote', $reply);
        }

        $this->generatedNote = $note;
    }

    /**
     * Returns all defined validation rules for the form fields / class properties
     * as array of "property" => "rule" mappings
     *
     * @return array<string, string>
     */
    public function rules() {
        # flattens 2-dimensional array of per step rules defined as a class constant
        return array_merge([], ...self::STEP_RULES);
    }

    public function saveNote() {
        $this->validate();

        $note = Note::updateOrCreate(
            ['id' => $this->id ?? null],
            [
                'current_team_id' => Auth::user()->currentTeam->id,
                'patient_id' => $this->patientId,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'note_type' => $this->noteType,
                'actor_type' => $this->actorType,
                'age' => $this->age,
                'client_diagnosed' => $this->clientDiagnosed ?? false,
                'diagnose' => $this->diagnose ?? '',
                'goals' => $this->goals,
                'comments' => $this->comments ?? '',
                'recommendations' => $this->recommendations ?? '',
                'behavior' => $this->behavior ?? '',
                'intervention' => $this->intervention ?? '',
                'client_response' => $this->clientResponse ?? '',
                'homework_given' => $this->homeworkGiven ?? '',
                'genie' => $this->genie ?? '',
                'generated_note' => $this->generatedNote,
            ]
        );

        $this->redirectRoute('update-note', ['id' => $note->id]);
    }

    public function render() {
        return view('livewire.psych-gpt')->layout('layouts.app');
    }
}
