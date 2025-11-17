<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;

class SufficientBalance implements ValidationRule
{
    protected $user;
    protected $amount;

    public function __construct(User $user, float $amount = 0)
    {
        $this->user = $user;
        $this->amount = $amount;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $wallet = $this->user->wallet;
        if (!$wallet || $wallet->balance < $this->amount) {
            $fail('Insufficient balance to complete this transaction.');
        }
    }
}