<?php

namespace App\Core;

final class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = trim((string) ($data[$field] ?? ''));
            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                }

                if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Enter a valid email address.';
                }

                if (str_starts_with($rule, 'min:') && strlen($value) < (int) substr($rule, 4)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is too short.';
                }

                if (str_starts_with($rule, 'max:') && strlen($value) > (int) substr($rule, 4)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is too long.';
                }

                if ($rule === 'username' && $value !== '' && !preg_match('/^[A-Za-z0-9_-]{3,30}$/', $value)) {
                    $errors[$field][] = 'Username must be 3-30 characters and use only letters, numbers, underscores, or hyphens.';
                }

                if ($rule === 'url' && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field][] = 'Enter a valid URL including https://.';
                }
            }
        }

        return $errors;
    }
}
