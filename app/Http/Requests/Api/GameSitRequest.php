<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SufficientBalance;
use App\Rules\ValidBuyIn;

class GameSitRequest extends FormRequest
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
            // Removed the seat requirement as the backend will automatically assign seats
            'buy_in' => [
                'required',
                'numeric',
                'min:0.01',
                new SufficientBalance($this->user(), $this->input('buy_in')),
                new ValidBuyIn($this->route('room'))
            ],
        ];
    }
}