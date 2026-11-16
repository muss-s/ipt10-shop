<?php

namespace Core;

/**
 * Appends lines to storage/logs/app.log.
 */
class Logger
{
    public static string $file = '';

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    private static function write(string $level, string $message): void
    {
        if (self::$file === '') {
            return;
        }
        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $level, $message);
        file_put_contents(self::$file, $line, FILE_APPEND | LOCK_EX);
    }
}
