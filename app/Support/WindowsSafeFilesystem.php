<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Filesystem\Filesystem;

final class WindowsSafeFilesystem extends Filesystem
{
    /**
     * Write the contents of a file, replacing it atomically if it already exists.
     *
     * On Windows, rename() fails with "Access is denied" when another process
     * holds the destination open — which happens whenever concurrent requests
     * compile the same Blade view. Losing that race is harmless (the winner
     * wrote equivalent content), so tolerate it instead of throwing.
     *
     * @param  string  $path
     * @param  string  $content
     * @param  int|null  $mode
     */
    public function replace($path, $content, $mode = null)
    {
        // If the path already exists and is a symlink, get the real path...
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        // Fix permissions of tempPath because `tempnam()` creates it with permissions set to 0600...
        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        foreach ([0, 10_000, 50_000, 150_000] as $delay) {
            usleep($delay);

            if (@rename($tempPath, $path)) {
                return;
            }
        }

        if (file_exists($path)) {
            @unlink($tempPath);

            return;
        }

        rename($tempPath, $path);
    }
}
