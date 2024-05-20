<?php

namespace App\Http\Requests;

use App\Models\Balance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CreatePaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment-amount' => ['required', 'numeric', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
            'payment-payee' => ['required', 'int', Rule::exists('users', 'id')],
            'payment-balance' => ['required', 'int', 'valid_balance_id'],
            'payment-note' => ['nullable', 'string', 'max:65535'],
            'payment-date' => ['required', 'date'],
        ];
    }

    /**
     * Customize the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->addExtension('valid_balance_id', function ($attribute, $value, $parameters, $validator) {
            return $value == -1 || Balance::where('id', $value)->exists();
        });
    }
}
