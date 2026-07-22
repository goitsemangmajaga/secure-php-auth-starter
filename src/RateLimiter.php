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

        return hash(
            'sha256',
            strtolower(trim($email)) . '|' . $ip
        );
    }

    public function isBlocked(string $key): bool
    {
        $entry = $_SESSION['_login_attempts'][$key] ?? null;

        if (!is_array($entry)) {
            return false;
        }

        $lockedUntil = (int) ($entry['locked_until'] ?? 0);

        if ($lockedUntil === 0) {
            return false;
        }

        if ($lockedUntil <= time()) {
            /*
             * Keep the escalation level. The next failed attempt
             * will immediately trigger the next lockout stage.
             */
            $entry['locked_until'] = 0;
            $entry['count'] = max(0, $this->maxAttempts - 1);
            $_SESSION['_login_attempts'][$key] = $entry;

            return false;
        }

        return true;
    }

    public function recordFailure(string $key): void
    {
        $entry = $_SESSION['_login_attempts'][$key] ?? [
            'count' => 0,
            'level' => 0,
            'locked_until' => 0,
        ];

        $entry['count'] = (int) ($entry['count'] ?? 0) + 1;

        if ($entry['count'] >= $this->maxAttempts) {
            $entry['level'] = min(
                (int) ($entry['level'] ?? 0) + 1,
                3
            );

            $duration = match ($entry['level']) {
                1 => 60,                         // First lockout: 1 minute
                2 => 300,                        // Second lockout: 5 minutes
                default => $this->lockoutSeconds // Third and later: 15 minutes
            };

            $entry['locked_until'] = time() + $duration;
        }

        $_SESSION['_login_attempts'][$key] = $entry;
    }

    public function remainingSeconds(string $key): int
    {
        $entry = $_SESSION['_login_attempts'][$key] ?? null;

        if (!is_array($entry)) {
            return 0;
        }

        $lockedUntil = (int) ($entry['locked_until'] ?? 0);

        return max(0, $lockedUntil - time());
    }

    public function clear(string $key): void
    {
        unset($_SESSION['_login_attempts'][$key]);
    }
}