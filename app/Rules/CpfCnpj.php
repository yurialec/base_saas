<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfCnpj implements Rule
{
    public function passes($attribute, $value)
    {
        if (! is_string($value)
            || ! preg_match('/^(?:[0-9]{11}|[A-Z0-9]{12}[0-9]{2})$/D', $value)
            || preg_match('/^([0-9])\1+$/D', $value)) {
            return false;
        }

        $weights = strlen($value) === 11
            ? [range(10, 2), range(11, 2)]
            : [
                [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
                [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            ];

        $document = substr($value, 0, -2);

        foreach ($weights as $digitWeights) {
            $sum = 0;

            foreach ($digitWeights as $index => $weight) {
                // ASCII - 48 atende ao CNPJ numérico e ao alfanumérico.
                $sum += (ord($document[$index]) - 48) * $weight;
            }

            $remainder = $sum % 11;
            $document .= $remainder < 2 ? '0' : (string) (11 - $remainder);
        }

        return $document === $value;
    }

    public function message()
    {
        return 'Informe um CPF ou CNPJ válido.';
    }
}
