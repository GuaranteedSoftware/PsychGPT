<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('current_team_id')->index();
            $table->string('patient_id')->index();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('note_type', [
                'CPST',
                'TBSI',
                'Day Tx',
                'Individual',
            ]);
            $table->enum('actor_type', [
                'Therapist',
                'Clinitian',
            ]);
            $table->tinyInteger('age')->unsigned();
            $table->enum('gender', [
                'Male',
                'Female',
                'Other',
            ]);
            $table->boolean('client_diagnosed');
            $table->string('diagnose')->nullable();
            $table->string('goals');
            $table->string('comments')->nullable();
            $table->string('recommendations')->nullable();
            $table->string('behavior')->nullable();
            $table->string('intervention')->nullable();
            $table->string('client_response')->nullable();
            $table->string('homework_given')->nullable();
            $table->string('genie')->nullable();
            $table->text('generated_note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
