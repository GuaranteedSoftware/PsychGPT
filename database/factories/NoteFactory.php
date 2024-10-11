<?php

namespace Database\Factories;

use App\Livewire\PsychGpt;
use App\Models\Note;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $psychGpt = new PsychGpt();

        return [
            'current_team_id' => Team::factory()->create()->id,
            'patient_id' => $this->faker->randomNumber(),
            'start_time' => $this->faker->date(),
            'end_time' => $this->faker->date(),
            'note_type' => $this->faker->randomElement($psychGpt->noteTypes),
            'actor_type' => $this->faker->randomElement($psychGpt->actorTypes),
            'age' => $this->faker->numberBetween(18, 80),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'client_diagnosed' => $this->faker->randomElement([true, false]),
            'diagnose' => $this->faker->text(),
            'goals' => $this->faker->text(),
            'comments' => $this->faker->text(),
            'recommendations' => $this->faker->text(),
            'behavior' => $this->faker->text(),
            'intervention' => $this->faker->text(),
            'client_response' => $this->faker->text(),
            'homework_given' => $this->faker->text(),
            'genie' => $this->faker->text(),
            'generated_note' => $this->faker->text(),
        ];
    }
}
