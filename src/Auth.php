<?php
declare(strict_types=1);

final class Auth
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly int $idleTimeout
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => strtolower(trim($email))]);
        $user = $statement->fetch();

        if (!$user || !(bool) $user['is_active'] || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $rehash = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $rehash->execute([
                'hash' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $user['id'],
            ]);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        $_SESSION['last_activity'] = time();
        return true;
    }

    public function check(): bool
    {
        if (empty($_SESSION['user'])) {
            return false;
        }

        $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
        if ($lastActivity === 0 || (time() - $lastActivity) > $this->idleTimeout) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function requireLogin(): void
    {
        if (!$this->check()) {
            flash('error', 'Please sign in to continue.');
            redirect('/index.php');
        }
    }

    public function requireRole(string ...$roles): void
    {
        $this->requireLogin();
        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public function user(): ?array
    {
        return $this->check() ? $_SESSION['user'] : null;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
