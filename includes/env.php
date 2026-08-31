<?php

/**
 * Minimal .env loader - no Composer/vendor dependency. Reads KEY=VALUE
 * pairs (blank lines and #-comments ignored, quotes around the value
 * optional) into getenv()/$_ENV so config/connexion.php can read real
 * credentials without hardcoding them in a tracked file.
 */
function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (strlen($value) >= 2 && (
            ($value[0] === '"' && str_ends_with($value, '"')) ||
            ($value[0] === "'" && str_ends_with($value, "'"))
        )) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Reads an environment variable with a fallback, since a plain getenv()
 * miss and an intentionally empty value both return the same falsy string.
 */
function env(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}
