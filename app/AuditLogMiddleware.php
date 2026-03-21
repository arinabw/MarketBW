<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Пишет HTTP-события в audit_log при включённом audit.log_enabled в CMS.
 */
final class AuditLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Database $db,
        /** @var array<string, mixed> */
        private array $settings,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->db->isAuditLogEnabled($this->settings)) {
            return $handler->handle($request);
        }

        $verbose = $this->db->isAuditLogVerbose($this->settings);
        $t0 = microtime(true);
        $status = 500;
        try {
            $response = $handler->handle($request);
            $status = $response->getStatusCode();
        } catch (Throwable $e) {
            $this->tryLog($request, $verbose, $status, $t0, $e->getMessage());
            throw $e;
        }

        $this->tryLog($request, $verbose, $status, $t0, null);

        return $response;
    }

    private function tryLog(
        ServerRequestInterface $request,
        bool $verbose,
        int $statusCode,
        float $t0,
        ?string $errorMessage,
    ): void {
        try {
            $durationMs = (int) round((microtime(true) - $t0) * 1000);
            $path = $request->getUri()->getPath();
            if ($this->shouldSkipPath($path)) {
                return;
            }

            $method = $request->getMethod();
            $query = $request->getUri()->getQuery();
            $queryTrunc = $verbose ? self::truncate($query, 800) : null;

            $server = $request->getServerParams();
            $ip = self::clientIp($server);
            $ua = self::truncate((string) ($server['HTTP_USER_AGENT'] ?? ''), 600);
            $referer = $verbose
                ? self::truncate((string) ($server['HTTP_REFERER'] ?? ''), 800)
                : null;

            $channel = str_contains($path, '/admin') ? 'admin' : 'public';
            $adminUser = null;
            if ($channel === 'admin' && !empty($_SESSION['admin']) && !empty($_SESSION['admin_username'])) {
                $adminUser = (string) $_SESSION['admin_username'];
            }

            $ctx = [
                'php_sapi' => PHP_SAPI,
            ];
            if ($verbose) {
                $ctx['query_string'] = $queryTrunc ?? '';
                $ctx['referer'] = $referer ?? '';
                $ctx['content_length'] = $server['CONTENT_LENGTH'] ?? null;
                $ctx['content_type'] = isset($server['CONTENT_TYPE'])
                    ? self::truncate((string) $server['CONTENT_TYPE'], 200)
                    : null;
                if (strtoupper($method) === 'POST' && !empty($_POST)) {
                    $ctx['post_field_names'] = self::sanitizePostFieldNames(array_keys($_POST));
                }
                if (!empty($_FILES)) {
                    $ctx['upload_field_names'] = array_keys($_FILES);
                }
            }
            if ($errorMessage !== null) {
                $ctx['exception_message'] = self::truncate($errorMessage, 2000);
            }

            $this->db->appendAuditLog(
                $channel,
                $method,
                self::truncate($path, 2048),
                $queryTrunc,
                $statusCode,
                $durationMs,
                $ip,
                $ua,
                $adminUser,
                $referer,
                json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            );
        } catch (Throwable) {
            // не ломаем ответ
        }
    }

    private function shouldSkipPath(string $path): bool
    {
        $lower = strtolower($path);
        foreach (['/favicon.ico', '/robots.txt'] as $skip) {
            if (str_ends_with($lower, $skip)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $server */
    private static function clientIp(array $server): string
    {
        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string) $server['HTTP_X_FORWARDED_FOR']);

            return trim($parts[0]);
        }
        if (!empty($server['HTTP_X_REAL_IP'])) {
            return trim((string) $server['HTTP_X_REAL_IP']);
        }

        return (string) ($server['REMOTE_ADDR'] ?? '');
    }

    /**
     * @param list<string> $names
     *
     * @return list<string>
     */
    private static function sanitizePostFieldNames(array $names): array
    {
        $out = [];
        foreach ($names as $n) {
            if (strcasecmp($n, 'csrf') === 0) {
                $out[] = 'csrf';
                continue;
            }
            if (preg_match('/password|passwd|pwd|secret/i', $n)) {
                $out[] = $n . '=[скрыто]';
            } else {
                $out[] = $n;
            }
        }

        return $out;
    }

    private static function truncate(string $s, int $max): string
    {
        if (strlen($s) <= $max) {
            return $s;
        }

        return substr($s, 0, $max - 3) . '...';
    }
}
