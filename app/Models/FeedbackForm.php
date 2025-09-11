<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeedbackForm extends Model
{
    protected $fillable = [
        'version',
        'name',
        'form_structure',
        'language',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'form_structure' => 'array',
        ];
    }

    /**
     * Get the highest version form for a specific language.
     */
    public static function getActiveForm(string $language = 'ar'): ?self
    {
        return static::where('language', $language)
            ->orderByRaw('CAST(SUBSTRING_INDEX(version, ".", 1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(version, ".", -1) AS UNSIGNED) DESC')
            ->first();
    }

    /**
     * Get form by version and language.
     */
    public static function getByVersion(string $version, string $language = 'ar'): ?self
    {
        return static::where('version', $version)
            ->where('language', $language)
            ->first();
    }

    /**
     * Get form questions by type.
     */
    public function getQuestionsByType(string $type): array
    {
        $questions = [];
        foreach ($this->form_structure['questions'] ?? [] as $question) {
            if ($question['type'] === $type) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    /**
     * Get form validation rules.
     */
    public function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->form_structure['questions'] ?? [] as $index => $question) {
            $key = "responses.{$question['key']}";

            $questionRules = [];
            if ($question['required'] ?? false) {
                $questionRules[] = 'required';
            } else {
                $questionRules[] = 'nullable';
            }

            switch ($question['type']) {
                case 'rating':
                    $questionRules[] = 'integer';
                    $questionRules[] = "min:{$question['min_value']}";
                    $questionRules[] = "max:{$question['max_value']}";
                    break;

                case 'single_choice':
                    $questionRules[] = 'string';
                    $options = array_column($question['options'], 'value');
                    $questionRules[] = 'in:'.implode(',', $options);
                    break;

                case 'multiple_choice':
                    $questionRules[] = 'array';
                    if ($question['required'] ?? false) {
                        $questionRules[] = 'min:1';
                    }
                    $options = array_column($question['options'], 'value');
                    $rules["{$key}.*"] = ['string', 'in:'.implode(',', $options)];
                    break;

                case 'text':
                    $questionRules[] = 'string';
                    if (isset($question['max_length'])) {
                        $questionRules[] = "max:{$question['max_length']}";
                    }
                    break;
            }

            $rules[$key] = $questionRules;
        }

        return $rules;
    }

    /**
     * Scope to get forms by language.
     */
    public function scopeLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }
}
