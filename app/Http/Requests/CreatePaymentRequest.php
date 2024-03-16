<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
            'payment-balance' => ['required', 'int', Rule::exists('balances', 'id')],
            'payment-note' => ['nullable', 'string', 'max:65535'],
            'payment-date' => ['required', 'date'],
        ];
    }
}
