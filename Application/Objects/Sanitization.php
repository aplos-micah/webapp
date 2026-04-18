<?php

class Sanitization
{
    public static function sanitizeAll(): void
    {
        $_GET  = self::sanitizeArray($_GET);
        $_POST = self::sanitizeArray($_POST);
    }

    private static function sanitizeArray(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $cleanKey = self::sanitizeValue((string) $key);
            
            $clean[$cleanKey] = is_array($value)
                ? self::sanitizeArray($value)
                : self::sanitizeValue((string) $value);
        }
        return $clean;
    }

    private static function sanitizeValue(string $value): string
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return $value;
    }
}
