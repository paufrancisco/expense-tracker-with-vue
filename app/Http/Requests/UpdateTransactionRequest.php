<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => 'required|in:income,expense',
            'title'            => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'category'         => 'required|string',
            'description'      => 'nullable|string',
            'transaction_date' => 'required|date',
        ];
    }
}