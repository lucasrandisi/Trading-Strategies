<?php

namespace App\Http\Requests\Strategies;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RelativeStrengthIndexRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'crypto_id' => 'required|exists:cryptos,id',
            'periods' => 'required|int',
            'initial_usd' => 'required|numeric',
            'initial_crypto' => 'required|numeric'
        ];
    }
}
