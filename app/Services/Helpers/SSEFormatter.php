<?php

namespace App\Services\Helpers;

class SSEFormatter
{
    /**
     * Format a data chunk as SSE message event
     */
    public static function message(string $content): string
    {
        return self::event('message', ['content' => $content]);
    }

    /**
     * Format a done event
     */
    public static function done(int $chunks, int $length): string
    {
        return self::event('done', [
            'status' => 'complete',
            'chunks' => $chunks,
            'length' => $length
        ]);
    }

    /**
     * Format an error event
     */
    public static function error(string $message, ?string $details = null): string
    {
        $data = ['error' => $message];
        if ($details) {
            $data['details'] = $details;
        }
        return self::event('error', $data);
    }

    /**
     * Format a progress event
     */
    public static function progress(int $chunk, int $totalChunks, int $length): string
    {
        return self::event('progress', [
            'chunk' => $chunk,
            'total_chunks' => $totalChunks,
            'length' => $length
        ]);
    }

    /**
     * Format a custom SSE event
     */
    public static function event(string $eventName, array $data): string
    {
        $sse = "event: {$eventName}\n";
        $sse .= "data: " . json_encode($data) . "\n\n";
        return $sse;
    }

    /**
     * Send and flush an SSE event
     */
    public static function sendAndFlush(string $sseFormatted): void
    {
        echo $sseFormatted;
        
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    /**
     * Send a keep-alive comment (prevents timeout)
     */
    public static function keepAlive(): string
    {
        return ": keepalive\n\n";
    }
}