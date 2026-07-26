<?php

namespace App\Http\Requests\Analysis;

use App\Enums\AnalyzerType\AnalyzerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'analyzer' => $this->input('analyzer', AnalyzerType::Quality->value),
            'language' => $this->input('language', config('ai.language')),
        ]);
    }

    public function rules(): array
    {
        return [
            'input_code' => ['required', 'string'],
            'analyzer' => ['required', Rule::enum(AnalyzerType::class)],
            'language' => ['required', 'string', Rule::in(['en', 'pt-BR'])],
        ];
    }
}
