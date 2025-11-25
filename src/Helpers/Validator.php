<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Validator helper class for input validation
 */
class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * Create a new validator instance
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Validate the data with given rules
     */
    public function validate(array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $rulesArray = is_string($fieldRules)
                ? explode("|", $fieldRules)
                : $fieldRules;

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a validation rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $params = [];

        // Check if rule has parameters (e.g., min:12)
        if (strpos($rule, ":") !== false) {
            [$rule, $paramString] = explode(":", $rule, 2);
            $params = explode(",", $paramString);
        }

        $methodName = "validate" . ucfirst($rule);

        if (!method_exists($this, $methodName)) {
            throw new \InvalidArgumentException(
                "Validation rule '$rule' does not exist",
            );
        }

        $this->$methodName($field, $value, ...$params);
    }

    /**
     * Validate required field
     */
    private function validateRequired(string $field, $value): void
    {
        if (
            $value === null ||
            $value === "" ||
            (is_string($value) && trim($value) === "")
        ) {
            $this->addError($field, ucfirst($field) . " is required");
        }
    }

    /**
     * Validate email format
     */
    private function validateEmail(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError(
                $field,
                ucfirst($field) . " must be a valid email address",
            );
        }
    }

    /**
     * Validate minimum length
     */
    private function validateMin(string $field, $value, string $min): void
    {
        if ($value !== null) {
            $length = is_string($value)
                ? strlen($value)
                : (is_array($value)
                    ? count($value)
                    : $value);

            if ($length < (int) $min) {
                $this->addError(
                    $field,
                    ucfirst($field) . " must be at least $min characters",
                );
            }
        }
    }

    /**
     * Validate maximum length
     */
    private function validateMax(string $field, $value, string $max): void
    {
        if ($value !== null) {
            $length = is_string($value)
                ? strlen($value)
                : (is_array($value)
                    ? count($value)
                    : $value);

            if ($length > (int) $max) {
                $this->addError(
                    $field,
                    ucfirst($field) . " must not exceed $max characters",
                );
            }
        }
    }

    /**
     * Validate exact length
     */
    private function validateLength(string $field, $value, string $length): void
    {
        if ($value !== null && strlen((string) $value) !== (int) $length) {
            $this->addError(
                $field,
                ucfirst($field) . " must be exactly $length characters",
            );
        }
    }

    /**
     * Validate alphanumeric characters only
     */
    private function validateAlphanumeric(string $field, $value): void
    {
        if (
            $value !== null &&
            !preg_match('/^[a-zA-Z0-9]+$/', (string) $value)
        ) {
            $this->addError(
                $field,
                ucfirst($field) . " must contain only letters and numbers",
            );
        }
    }

    /**
     * Validate alpha characters only
     */
    private function validateAlpha(string $field, $value): void
    {
        if ($value !== null && !preg_match('/^[a-zA-Z]+$/', (string) $value)) {
            $this->addError(
                $field,
                ucfirst($field) . " must contain only letters",
            );
        }
    }

    /**
     * Validate numeric value
     */
    private function validateNumeric(string $field, $value): void
    {
        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, ucfirst($field) . " must be a number");
        }
    }

    /**
     * Validate integer value
     */
    private function validateInteger(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, ucfirst($field) . " must be an integer");
        }
    }

    /**
     * Validate pattern match
     */
    private function validatePattern(
        string $field,
        $value,
        string $pattern,
    ): void {
        if ($value !== null && !preg_match($pattern, (string) $value)) {
            $this->addError($field, ucfirst($field) . " format is invalid");
        }
    }

    /**
     * Validate confirmation field matches
     */
    private function validateConfirmed(string $field, $value): void
    {
        $confirmField = $field . "_confirmation";
        $confirmValue = $this->data[$confirmField] ?? null;

        if ($value !== $confirmValue) {
            $this->addError(
                $field,
                ucfirst($field) . " confirmation does not match",
            );
        }
    }

    /**
     * Validate value is in array
     */
    private function validateIn(string $field, $value, string ...$values): void
    {
        if ($value !== null && !in_array($value, $values, true)) {
            $this->addError(
                $field,
                ucfirst($field) . " must be one of: " . implode(", ", $values),
            );
        }
    }

    /**
     * Validate value is not in array
     */
    private function validateNotIn(
        string $field,
        $value,
        string ...$values,
    ): void {
        if ($value !== null && in_array($value, $values, true)) {
            $this->addError($field, ucfirst($field) . " is not allowed");
        }
    }

    /**
     * Validate URL format
     */
    private function validateUrl(string $field, $value): void
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, ucfirst($field) . " must be a valid URL");
        }
    }

    /**
     * Validate boolean value
     */
    private function validateBoolean(string $field, $value): void
    {
        $acceptable = [
            true,
            false,
            0,
            1,
            "0",
            "1",
            "true",
            "false",
            "yes",
            "no",
        ];

        if ($value !== null && !in_array($value, $acceptable, true)) {
            $this->addError($field, ucfirst($field) . " must be true or false");
        }
    }

    /**
     * Validate date format
     */
    private function validateDate(
        string $field,
        $value,
        string $format = "Y-m-d",
    ): void {
        if ($value !== null) {
            $date = \DateTime::createFromFormat($format, $value);

            if (!$date || $date->format($format) !== $value) {
                $this->addError(
                    $field,
                    ucfirst($field) . " must be a valid date",
                );
            }
        }
    }

    /**
     * Validate value is different from another field
     */
    private function validateDifferent(
        string $field,
        $value,
        string $otherField,
    ): void {
        $otherValue = $this->data[$otherField] ?? null;

        if ($value === $otherValue) {
            $this->addError(
                $field,
                ucfirst($field) . " must be different from $otherField",
            );
        }
    }

    /**
     * Validate value is same as another field
     */
    private function validateSame(
        string $field,
        $value,
        string $otherField,
    ): void {
        $otherValue = $this->data[$otherField] ?? null;

        if ($value !== $otherValue) {
            $this->addError(
                $field,
                ucfirst($field) . " must be the same as $otherField",
            );
        }
    }

    /**
     * Add an error message
     */
    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get the first error for a field
     */
    public function getFirstError(string $field): ?string
    {
        $errors = $this->getFieldErrors($field);
        return !empty($errors) ? $errors[0] : null;
    }

    /**
     * Check if there are any errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if a specific field has errors
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Clear all errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Static method to quickly validate data
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        $validator->validate($rules);
        return $validator;
    }

    /**
     * Custom validation rule for username
     */
    private function validateUsername(string $field, $value): void
    {
        if ($value !== null) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', (string) $value)) {
                $this->addError(
                    $field,
                    "Username must contain only letters and numbers",
                );
            } elseif (strlen((string) $value) < 3) {
                $this->addError(
                    $field,
                    "Username must be at least 3 characters long",
                );
            } elseif (strlen((string) $value) > 50) {
                $this->addError(
                    $field,
                    "Username must not exceed 50 characters",
                );
            }
        }
    }

    /**
     * Custom validation rule for password strength
     */
    private function validatePassword(string $field, $value): void
    {
        if ($value !== null) {
            if (strlen((string) $value) < 12) {
                $this->addError(
                    $field,
                    "Password must be at least 12 characters long",
                );
            }
        }
    }
}
