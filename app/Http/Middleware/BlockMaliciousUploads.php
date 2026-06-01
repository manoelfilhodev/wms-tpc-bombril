<?php

namespace App\Http\Middleware;

use App\Services\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BlockMaliciousUploads
{
    private const BLOCKED_EXTENSIONS = [
        'php',
        'phtml',
        'phar',
        'exe',
        'bat',
        'cmd',
        'sh',
        'ps1',
        'com',
        'scr',
        'js',
        'jar',
    ];

    public function __construct(private readonly SecurityAuditService $audit)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        foreach ($request->allFiles() as $key => $file) {
            if ($this->containsBlockedUpload($file)) {
                $this->audit->record('blocked_malicious_upload', 'uploads', ['field' => $key], $request);

                abort(422, 'Arquivo enviado nao permitido.');
            }
        }

        return $next($request);
    }

    private function containsBlockedUpload(mixed $file): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if ($this->containsBlockedUpload($item)) {
                    return true;
                }
            }

            return false;
        }

        if (! $file instanceof UploadedFile) {
            return false;
        }

        $extension = mb_strtolower($file->getClientOriginalExtension());
        $original = mb_strtolower($file->getClientOriginalName());

        return in_array($extension, self::BLOCKED_EXTENSIONS, true)
            || preg_match('/\.(php|phtml|phar|exe|bat|cmd|sh|ps1)(\.|$)/i', $original);
    }
}
