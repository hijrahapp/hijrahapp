<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing methodology_question records to have proper sequence values
        $methodologies = DB::table('methodology_question')
            ->select('methodology_id')
            ->distinct()
            ->get();

        foreach ($methodologies as $methodology) {
            $questions = DB::table('methodology_question')
                ->where('methodology_id', $methodology->methodology_id)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($questions as $index => $question) {
                DB::table('methodology_question')
                    ->where('id', $question->id)
                    ->update(['sequence' => $index + 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset sequence values to 0
        DB::table('methodology_question')->update(['sequence' => 0]);
    }
};
