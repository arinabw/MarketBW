<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Канонический origin сайта и JSON-LD для SEO (Яндекс, Google).
 */
final class SeoHelper
{
    /**
     * Абсолютный корень сайта без завершающего слэша (с учётом BASE_PATH / Slim).
     * При заданном PUBLIC_SITE_URL в .env — он приоритетнее (должен совпадать с реальным URL в браузере).
     *
     * @param array<string, mixed> $settings
     */
    public static function resolvePublicBase(ServerRequestInterface $request, array $settings, string $slimBasePath): string
    {
        $fixed = trim(rtrim((string) ($settings['public_site_url'] ?? ''), '/'));
        if ($fixed !== '') {
            return $fixed;
        }

        return self::inferFromRequest($request, $settings, $slimBasePath);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function inferFromRequest(ServerRequestInterface $request, array $settings, string $slimBasePath): string
    {
        $server = $request->getServerParams();
        $https = HttpsDetector::fromServer($server)
            || (bool) ($settings['session_force_secure'] ?? false);
        $scheme = $https ? 'https' : 'http';

        $hostHeader = (string) ($server['HTTP_X_FORWARDED_HOST'] ?? $server['HTTP_HOST'] ?? '');
        $hostHeader = trim(explode(',', $hostHeader, 2)[0]);
        if ($hostHeader === '') {
            return '';
        }

        $bp = trim($slimBasePath, '/');
        $pathPrefix = $bp !== '' ? '/' . $bp : '';

        return $scheme . '://' . $hostHeader . $pathPrefix;
    }

    /**
     * Тематика сайта для schema.org knowsAbout (рукоделие, бисер, подарки).
     *
     * @return list<string>
     */
    public static function thematicKnowsAbout(): array
    {
        return [
            'Бисероплетение',
            'Украшения ручной работы',
            'Рукоделие',
            'Подарки ручной работы',
            'Изделия из бисера',
        ];
    }

    /**
     * Полный URL страницы товара для schema.org.
     *
     * @param array<string, mixed> $product
     */
    public static function buildProductJsonLd(
        array $product,
        string $pageUrl,
        string $absoluteBase,
        ?string $categoryName = null,
        string $brandName = '',
    ): string {
        $imgs = [];
        foreach ($product['images'] ?? [] as $img) {
            if (!is_string($img) || $img === '') {
                continue;
            }
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                $imgs[] = $img;
            } else {
                $imgs[] = rtrim($absoluteBase, '/') . '/' . ltrim($img, '/');
            }
        }

        $desc = trim(strip_tags((string) ($product['description'] ?? '')));
        if (strlen($desc) > 5000) {
            $desc = substr($desc, 0, 4997) . '...';
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => (string) ($product['name'] ?? ''),
            'description' => $desc,
            'url' => $pageUrl,
            'offers' => [
                '@type' => 'Offer',
                'url' => $pageUrl,
                'priceCurrency' => 'RUB',
                'price' => (string) ((float) ($product['price'] ?? 0)),
                'availability' => !empty($product['in_stock'])
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];
        if ($imgs !== []) {
            $data['image'] = count($imgs) === 1 ? $imgs[0] : $imgs;
        }
        if ($categoryName !== null && $categoryName !== '') {
            $data['category'] = $categoryName;
        }
        if ($brandName !== '') {
            $data['brand'] = [
                '@type' => 'Brand',
                'name' => $brandName,
            ];
        }
        $materials = $product['materials'] ?? [];
        if (is_array($materials) && $materials !== []) {
            $data['material'] = implode(', ', array_map(static fn ($m): string => (string) $m, $materials));
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param list<string> $sameAs
     * @param list<string> $knowsAbout
     */
    public static function buildOrganizationJsonLd(
        string $name,
        string $description,
        string $siteUrl,
        string $phone,
        string $email,
        array $sameAs,
        array $knowsAbout = [],
        ?string $keywords = null,
    ): string {
        $same = array_values(array_filter($sameAs, static fn (string $u): bool => $u !== '' && $u !== '#'));
        $data = [
            '@context' => 'https://schema.org',
            '@type' => ['LocalBusiness', 'JewelryStore'],
            'name' => $name,
            'description' => $description,
            'url' => $siteUrl,
        ];
        if ($phone !== '') {
            $data['telephone'] = $phone;
        }
        if ($email !== '') {
            $data['email'] = $email;
        }
        if ($same !== []) {
            $data['sameAs'] = $same;
        }
        if ($knowsAbout !== []) {
            $data['knowsAbout'] = $knowsAbout;
        }
        if ($keywords !== null && $keywords !== '') {
            $data['keywords'] = $keywords;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function buildWebSiteJsonLd(string $name, string $url, string $description, ?string $searchUrlTemplate = null): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $url,
            'inLanguage' => 'ru-RU',
        ];
        if ($description !== '') {
            $data['description'] = $description;
        }
        if ($searchUrlTemplate !== null && $searchUrlTemplate !== '') {
            $data['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $searchUrlTemplate,
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param list<array{name: string, url: string}> $items
     */
    public static function buildBreadcrumbJsonLd(array $items): string
    {
        $elements = [];
        $pos = 1;
        foreach ($items as $it) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => $it['name'],
                'item' => $it['url'],
            ];
            $pos++;
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
