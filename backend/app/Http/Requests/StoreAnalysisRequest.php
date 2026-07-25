<?php

namespace App\Http\Requests;

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
        ]);
    }

    public function rules(): array
    {
        return [
            'input_code' => ['required', 'string'],
            'analyzer' => ['required', Rule::enum(AnalyzerType::class)],
        ];
    }
}
