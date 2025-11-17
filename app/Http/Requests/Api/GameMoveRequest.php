<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GameMoveRequest extends FormRequest
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
            'action' => 'required|string|in:fold,check,call,raise,all-in',
            'amount' => 'nullable|numeric|min:0'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Action is required',
            'action.in' => 'Action must be one of: fold, check, call, raise, all-in',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be at least 0'
        ];
    }
}