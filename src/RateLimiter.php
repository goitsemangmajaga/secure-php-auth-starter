<?php
declare(strict_types=1);

final class RateLimiter
{
    public function __construct(
        private readonly int $maxAttempts,
        private readonly int $lockoutSeconds
    ) {
    }

    public function key(string $email): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return hash('sha256', strtolower(trim($email)) . '|' . $ip);
    }

    public function isBlocked(string $key): bool
    {
        $entry = $_SESSION['_login_attempts'][$key] ?? null;
        if (!is_array($entry)) {
            return false;
        }

        if (($entry['locked_until'] ?? 0) <= time()) {
            unset($_SESSION['_login_attempts'][$key]);
            return false;
        }

        return true;
    }

    public function recordFailure(string $key): void
    {
        $entry = $_SESSION['_login_attempts'][$key] ?? ['count' => 0, 'locked_until' => 0];
        $entry['count']++;

        if ($entry['count'] >= $this->maxAttempts) {
            $entry['locked_until'] = time() + $this->lockoutSeconds;
        }

        $_SESSION['_login_attempts'][$key] = $entry;
    }

    public function clear(string $key): void
    {
        unset($_SESSION['_login_attempts'][$key]);
    }
}

