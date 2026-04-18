<?php

/**
 * Validator
 *
 * Stateless in-memory validation helpers. Each method returns null on pass
 * or a human-readable error string on failure. Coercion helpers return the
 * normalised value directly.
 *
 * DB-dependent rules (duplicate checks, token validity) stay in the Object
 * classes until a DI container makes them independently testable (#4).
 */
class Validator
{
    /**
     * Fail if the trimmed value is empty.
     */
    public static function required(string $value, string $label): ?string
    {
        return trim($value) === '' ? "{$label} is required." : null;
    }

    /**
     * Fail if the value is not a syntactically valid email address.
     */
    public static function email(string $value, string $label): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) === false
            ? "Please provide a valid email address for {$label}."
            : null;
    }

    /**
     * Fail if the trimmed value is shorter than $min characters.
     */
    public static function minLength(string $value, int $min, string $label): ?string
    {
        return strlen(trim($value)) < $min
            ? "{$label} must be at least {$min} characters."
            : null;
    }

    /**
     * Return $value if it is in $allowed, otherwise return $default.
     */
    public static function enum(mixed $value, array $allowed, mixed $default): mixed
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * Coerce to 1 or 0.
     */
    public static function boolean(mixed $value): int
    {
        return !empty($value) ? 1 : 0;
    }

    /**
     * Return (int) $value if non-empty string/non-null, otherwise null.
     */
    public static function nullableInt(mixed $value): ?int
    {
        return ($value !== '' && $value !== null) ? (int) $value : null;
    }

    /**
     * Return (float) $value if non-empty string/non-null, otherwise null.
     */
    public static function nullableFloat(mixed $value): ?float
    {
        return ($value !== '' && $value !== null) ? (float) $value : null;
    }
}
