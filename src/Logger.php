<?php

final class AppLogger
{
    private static string $logDir;

    public static function setLogDir(string $dir): void
    {
        self::$logDir = rtrim($dir, '/\\');
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }

    public static function error(string $message, array $context = []): void
    {
        $extra = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) : '';
        self::write('ERROR', $message . $extra);
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    private static function write(string $level, string $message): void
    {
        $logFile = self::$logDir . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
        $entry = date('Y-m-d H:i:s') . " [{$level}] {$message}" . PHP_EOL;
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
