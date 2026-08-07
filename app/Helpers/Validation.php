<?php

declare(strict_types=1);

namespace App\Helpers;

class Validation
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, array $data): void
    {
        [$ruleName, $param] = explode(':', $rule . ':', 2);

        match ($ruleName) {
            'required' => $this->required($field, $value),
            'email' => $this->email($field, $value),
            'min' => $this->min($field, $value, (int)$param),
            'max' => $this->max($field, $value, (int)$param),
            'numeric' => $this->numeric($field, $value),
            'unique' => $this->unique($field, $value, $param),
            'in' => $this->in($field, $value, $param),
            'date' => $this->date($field, $value),
            'url' => $this->url($field, $value),
            default => null
        };
    }

    private function required(string $field, mixed $value): void
    {
        if (empty($value) && $value !== '0') {
            $this->errors[$field][] = ucfirst($field) . ' is required';
        }
    }

    private function email(string $field, mixed $value): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = ucfirst($field) . ' must be a valid email address';
        }
    }

    private function min(string $field, mixed $value, int $min): void
    {
        if ($value && (is_string($value) && strlen($value) < $min)) {
            $this->errors[$field][] = ucfirst($field) . " must be at least {$min} characters";
        }
    }

    private function max(string $field, mixed $value, int $max): void
    {
        if ($value && (is_string($value) && strlen($value) > $max)) {
            $this->errors[$field][] = ucfirst($field) . " must not exceed {$max} characters";
        }
    }

    private function numeric(string $field, mixed $value): void
    {
        if ($value && !is_numeric($value)) {
            $this->errors[$field][] = ucfirst($field) . ' must be numeric';
        }
    }

    private function unique(string $field, mixed $value, string $param): void
    {
        if (!$value || !$param) return;

        [$table, $column] = explode(',', $param . ',id', 2) + [1 => 'id'];
        $column = $column ?: 'id';
        $fieldColumn = $column === 'id' ? $field : $column;

        $db = \App\Core\Database::getInstance();
        $result = $db->fetch("SELECT id FROM {$table} WHERE {$fieldColumn} = ?", [$value]);

        if ($result) {
            $this->errors[$field][] = ucfirst($field) . ' already exists';
        }
    }

    private function in(string $field, mixed $value, string $param): void
    {
        if (!$value || !$param) return;
        $allowed = explode(',', $param);
        
        if (!in_array($value, $allowed)) {
            $this->errors[$field][] = ucfirst($field) . ' must be one of: ' . implode(', ', $allowed);
        }
    }

    private function date(string $field, mixed $value): void
    {
        if ($value && strtotime($value) === false) {
            $this->errors[$field][] = ucfirst($field) . ' must be a valid date';
        }
    }

    private function url(string $field, mixed $value): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = ucfirst($field) . ' must be a valid URL';
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}
