<?php

namespace Core;

/**
 * Rule-string validator: ['email' => 'required|email', 'price' => 'required|numeric|min:0'].
 */
class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules)
    {
        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            // min/max compare numbers only for numeric fields; otherwise they check text length
            // (so a phone number like "09171234567" is measured in characters).
            $numeric = (bool) array_intersect($rules, ['numeric', 'integer']);
            foreach ($rules as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $this->check($field, $name, $arg, $numeric);
            }
        }
    }

    private function check(string $field, string $rule, ?string $arg, bool $numeric): void
    {
        $value = $this->data[$field] ?? null;
        $label = ucfirst(str_replace('_', ' ', $field));
        $empty = $value === null || $value === '';

        if ($rule !== 'required' && $empty) {
            return;
        }

        $error = match ($rule) {
            'required' => $empty ? "$label is required." : null,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "$label must be a valid email.",
            'numeric' => is_numeric($value) ? null : "$label must be a number.",
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false ? null : "$label must be a whole number.",
            'min' => $numeric && is_numeric($value)
                ? ($value >= $arg ? null : "$label must be at least $arg.")
                : (mb_strlen((string) $value) >= $arg ? null : "$label must be at least $arg characters."),
            'max' => $numeric && is_numeric($value)
                ? ($value <= $arg ? null : "$label must be at most $arg.")
                : (mb_strlen((string) $value) <= $arg ? null : "$label must be at most $arg characters."),
            'same' => $value === ($this->data[$arg] ?? null) ? null : "$label does not match.",
            default => throw new \InvalidArgumentException("Unknown rule $rule"),
        };

        if ($error && !isset($this->errors[$field])) {
            $this->errors[$field] = $error;
        }
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
