<?php
final class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['__flash'] = ['type' => $type, 'message' => $message];
    }

    public static function get(): ?array
    {
        if (isset($_SESSION['__flash'])) {
            $flash = $_SESSION['__flash'];
            unset($_SESSION['__flash']);
            return $flash;
        }
        return null;
    }

    public static function renderIfPresent(): void
    {
        $flash = self::get();
        if ($flash) {
            $cls = $flash['type'] === 'success' ? 'success' : 'danger';
            echo '<div class="alert alert-'.$cls.' mt-2">'.htmlspecialchars($flash['message']).'</div>';
        }
    }
}
