<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidGameAction implements ValidationRule
{
    protected $validActions = ['fold', 'check', 'call', 'raise', 'all-in'];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array(strtolower($value), $this->validActions)) {
            $fail('The selected game action is invalid.');
        }
    }
}