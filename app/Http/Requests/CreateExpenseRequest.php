<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateExpenseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expense-name' => ['required', 'string', 'max:255'],
            'expense-amount' => ['required', 'numeric', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
            'expense-paid' => ['required', 'int', Rule::exists('users', 'id')],
            'expense-group' => ['required', 'int', Rule::exists('groups', 'id')],
            'expense-split' => ['required', 'int', Rule::exists('expense_types', 'id')],
            /*'expense-category' => ['required', 'int', Rule::exists('categories', 'id')],*/
            'expense-note' => ['nullable', 'string', 'max:65535'],
            'expense-date' => ['required', 'date'],
        ];
    }
}
