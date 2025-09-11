<?php

namespace App\Http\Requests;

use App\Models\FeedbackForm;
use Illuminate\Foundation\Http\FormRequest;

class SubmitProgramFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is authenticated via JWT middleware
        return $this->has('authUserId') || $this->has('authUser');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $baseRules = [
            'responses' => ['required', 'array'],
            'form_version' => ['sometimes', 'string', 'max:10'],
        ];

        // Get form version and load dynamic validation rules
        $formVersion = $this->input('form_version', '1.0');
        $language = $this->input('language', 'ar');
        $form = FeedbackForm::getByVersion($formVersion, $language);

        if ($form) {
            // Merge dynamic rules from form configuration
            $dynamicRules = $form->getValidationRules();

            return array_merge($baseRules, $dynamicRules);
        }

        // Fallback to static rules if form not found
        return array_merge($baseRules, [
            'responses.overall_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'responses.life_improvement' => ['required', 'string', 'in:yes,somewhat,no'],
            'responses.content_clarity' => ['required', 'string', 'in:excellent,good,needs_improvement'],
            'responses.most_beneficial_content' => ['required', 'array', 'min:1'],
            'responses.most_beneficial_content.*' => [
                'string',
                'in:articles,videos,ebooks,challenges,daily_practices,quick_tasks,audio_files',
            ],
            'responses.improvement_suggestions' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'responses.overall_rating.required' => 'Overall rating is required',
            'responses.overall_rating.min' => 'Rating must be at least 1 star',
            'responses.overall_rating.max' => 'Rating cannot exceed 5 stars',
            'responses.life_improvement.required' => 'Life improvement rating is required',
            'responses.life_improvement.in' => 'Invalid life improvement rating',
            'responses.content_clarity.required' => 'Content clarity rating is required',
            'responses.content_clarity.in' => 'Invalid content clarity rating',
            'responses.most_beneficial_content.required' => 'Please select at least one beneficial content type',
            'responses.most_beneficial_content.min' => 'Please select at least one beneficial content type',
            'responses.most_beneficial_content.*.in' => 'Invalid content type selected',
            'responses.improvement_suggestions.max' => 'Suggestions cannot exceed 1000 characters',
        ];
    }
}
