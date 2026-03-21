<?php

declare(strict_types=1);

namespace App;

/**
 * Определение HTTPS за reverse-proxy (Traefik, Caddy, Cloudflare и т.д.).
 */
final class HttpsDetector
{
    /**
     * @param array<string, mixed> $server
     */
    public static function fromServer(array $server): bool
    {
        if (!empty($server['HTTPS'])) {
            $v = strtolower((string) $server['HTTPS']);
            if ($v !== 'off' && $v !== '0' && $v !== '') {
                return true;
            }
        }

        foreach (['HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_PROTOCOL'] as $key) {
            if (empty($server[$key])) {
                continue;
            }
            $raw = (string) $server[$key];
            $first = strtolower(trim(explode(',', $raw, 2)[0]));
            if ($first === 'https') {
                return true;
            }
        }

        if (!empty($server['HTTP_FRONT_END_HTTPS'])
            && strtolower((string) $server['HTTP_FRONT_END_HTTPS']) === 'on') {
            return true;
        }

        if (!empty($server['HTTP_X_FORWARDED_SSL'])
            && strtolower((string) $server['HTTP_X_FORWARDED_SSL']) === 'on') {
            return true;
        }

        if (!empty($server['HTTP_FORWARDED'])
            && preg_match('/(?:^|[;,]\s*)proto=https(?:\s|;|,|$)/i', (string) $server['HTTP_FORWARDED'])) {
            return true;
        }

        if (!empty($server['HTTP_CF_VISITOR'])) {
            $decoded = json_decode((string) $server['HTTP_CF_VISITOR'], true);
            if (is_array($decoded) && isset($decoded['scheme'])
                && strtolower((string) $decoded['scheme']) === 'https') {
                return true;
            }
        }

        return false;
    }
}
