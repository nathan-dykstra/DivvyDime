<?php

use App\Models\EmailPreferenceType;
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
        Schema::create('email_preference_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 255);
        });

        EmailPreferenceType::insert([
            ['type' => 'Weekly'],
            ['type' => 'Biweekly'],
            ['type' => 'Monthly'],
            ['type' => 'Never'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_preference_types');
    }
};
