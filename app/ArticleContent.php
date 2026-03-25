<?php

declare(strict_types=1);

namespace App;

/**
 * Чтение статических статей из content/articles/.
 * Без БД — вся информация в PHP-конфиге и HTML-файлах на диске.
 */
final class ArticleContent
{
    /** @var array<string, array<string, mixed>>|null */
    private static ?array $topics = null;

    private string $contentDir;

    public function __construct(?string $contentDir = null)
    {
        $this->contentDir = $contentDir ?? dirname(__DIR__) . '/content/articles';
    }

    /**
     * @return array<string, array{name: string, description: string, sort: int, articles: list<array{slug: string, title: string, excerpt: string, sort: int}>}>
     */
    public function topics(): array
    {
        if (self::$topics === null) {
            $path = $this->contentDir . '/_topics.php';
            self::$topics = is_file($path) ? (array) require $path : [];
        }

        return self::$topics;
    }

    /**
     * @return list<array{slug: string, name: string, description: string}>
     */
    public function topicList(): array
    {
        $out = [];
        foreach ($this->topics() as $slug => $t) {
            $out[] = [
                'slug' => $slug,
                'name' => (string) $t['name'],
                'description' => (string) ($t['description'] ?? ''),
                'sort' => (int) ($t['sort'] ?? 0),
            ];
        }
        usort($out, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $out;
    }

    /**
     * @return array{slug: string, name: string, description: string}|null
     */
    public function topicBySlug(string $slug): ?array
    {
        $t = $this->topics()[$slug] ?? null;
        if ($t === null) {
            return null;
        }

        return [
            'slug' => $slug,
            'name' => (string) $t['name'],
            'description' => (string) ($t['description'] ?? ''),
        ];
    }

    /**
     * @return list<array{slug: string, title: string, excerpt: string, topic_slug: string, topic_name: string}>
     */
    public function articlesInTopic(string $topicSlug): array
    {
        $t = $this->topics()[$topicSlug] ?? null;
        if ($t === null) {
            return [];
        }
        $out = [];
        foreach ($t['articles'] as $a) {
            $htmlPath = $this->contentDir . '/' . $topicSlug . '/' . $a['slug'] . '.html';
            if (!is_file($htmlPath)) {
                continue;
            }
            $out[] = [
                'slug' => $a['slug'],
                'title' => $a['title'],
                'excerpt' => $a['excerpt'],
                'topic_slug' => $topicSlug,
                'topic_name' => (string) $t['name'],
            ];
        }

        return $out;
    }

    /**
     * @return array{slug: string, title: string, excerpt: string, topic_slug: string, topic_name: string, body_html: string}|null
     */
    public function article(string $topicSlug, string $articleSlug): ?array
    {
        $t = $this->topics()[$topicSlug] ?? null;
        if ($t === null) {
            return null;
        }
        $found = null;
        foreach ($t['articles'] as $a) {
            if ($a['slug'] === $articleSlug) {
                $found = $a;
                break;
            }
        }
        if ($found === null) {
            return null;
        }
        $htmlPath = $this->contentDir . '/' . $topicSlug . '/' . $found['slug'] . '.html';
        if (!is_file($htmlPath)) {
            return null;
        }

        return [
            'slug' => $found['slug'],
            'title' => $found['title'],
            'excerpt' => $found['excerpt'],
            'topic_slug' => $topicSlug,
            'topic_name' => (string) $t['name'],
            'body_html' => (string) file_get_contents($htmlPath),
        ];
    }

    /**
     * @return list<array{topic_slug: string, slug: string}>
     */
    public function allPublishedSlugs(): array
    {
        $out = [];
        foreach ($this->topics() as $tSlug => $t) {
            foreach ($t['articles'] as $a) {
                $htmlPath = $this->contentDir . '/' . $tSlug . '/' . $a['slug'] . '.html';
                if (is_file($htmlPath)) {
                    $out[] = ['topic_slug' => $tSlug, 'slug' => $a['slug']];
                }
            }
        }

        return $out;
    }
}
