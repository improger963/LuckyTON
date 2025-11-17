<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SufficientBalance;

class WalletWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.00000001',
                'max:1000000',
                'regex:/^\d+(\.\d{1,8})?$/',
                new SufficientBalance($this->user(), $this->input('amount'))
            ],
            'address' => [
                'required',
                'string',
                'min:26',
                'max:62',
                'regex:/^[13][a-km-zA-HJ-NP-Z1-9]{25,61}$/', // BTC format
            ],
            'currency' => 'required|in:BTC,ETH,USDT',
            'pin' => [
                'nullable',
                'string',
                'digits:4',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.regex' => 'The amount format is invalid.',
            'address.regex' => 'The address format is invalid.',
        ];
    }
}