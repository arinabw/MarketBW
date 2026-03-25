<?php

declare(strict_types=1);

namespace App;

/**
 * Импорт статей из текстовых файлов (экспорт из Word) в SQLite.
 * Дополняет материал тематическим блоком «Дополнительно» на основе общих сведений о бисере.
 */
final class ArticleImport
{
    /** @var array<int, string> номер раздела 1–8 → id темы в БД */
    private const BLOCK_TO_TOPIC = [
        1 => 'materials',
        2 => 'techniques',
        3 => 'jewelry',
        4 => 'flowers',
        5 => 'figures',
        6 => 'combination',
        7 => 'history',
        8 => 'trends',
    ];

    /**
     * @return array{block: int, sub: int, titleLine: string}|null
     */
    public static function parseFilename(string $basename): ?array
    {
        if (!preg_match('/^(?P<b1>\d+)[_-](?P<b2>\d+)_(?P<rest>.+)\.txt$/u', $basename, $m)) {
            return null;
        }

        return [
            'block' => (int) $m['b1'],
            'sub' => (int) $m['b2'],
            'titleLine' => str_replace('_', ' ', (string) $m['rest']),
        ];
    }

    public static function plainTextToHtml(string $raw): string
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        $lines = explode("\n", $raw);
        $html = '';
        $buf = '';
        $flushBuf = static function () use (&$html, &$buf): void {
            $t = trim(preg_replace('/\s+/u', ' ', $buf));
            if ($t !== '') {
                $html .= '<p>' . htmlspecialchars($t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>\n";
            }
            $buf = '';
        };
        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '') {
                $flushBuf();

                continue;
            }
            $trim = trim($line);
            if (self::chunkLooksLikeHeading($trim)) {
                $flushBuf();
                $tag = preg_match('/^\d+\.\d+\./u', $trim) ? 'h3' : 'h2';
                $html .= '<' . $tag . '>' . htmlspecialchars($trim, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</' . $tag . ">\n";

                continue;
            }
            if (str_starts_with($trim, '•') || str_starts_with($trim, '- ')) {
                $flushBuf();
                $html .= self::lineAsBulletOrParagraph($trim);

                continue;
            }
            if ($buf !== '') {
                $buf .= ' ';
            }
            $buf .= $trim;
        }
        $flushBuf();

        return $html;
    }

    private static function lineAsBulletOrParagraph(string $trim): string
    {
        if (str_starts_with($trim, '•')) {
            $rest = trim(substr($trim, strlen('•')));
            $parts = preg_split('/\s*•\s*/u', $rest) ?: [];
            $parts = array_values(array_filter(array_map('trim', $parts)));
            if ($parts === []) {
                return '<p>' . htmlspecialchars($trim, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>\n";
            }
            $html = "<ul>\n";
            foreach ($parts as $li) {
                $html .= '<li>' . htmlspecialchars($li, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>\n";
            }

            return $html . "</ul>\n";
        }

        return '<p>' . htmlspecialchars($trim, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>\n";
    }

    private static function chunkLooksLikeHeading(string $chunk): bool
    {
        if (mb_strlen($chunk) > 160) {
            return false;
        }

        return (bool) preg_match(
            '/^(Часть\\s+\\d+[:\\.]?|Введение|Заключение|Оглавление|\\d+\\.\\d+\\.\\s|[А-ЯЁ][^\\n]{0,90}:)$/u',
            $chunk
        );
    }

    /**
     * Текстовый блок «Дополнительно» по разделу (обобщённые сведения, без внешних ссылок).
     */
    public static function enrichmentHtml(int $block, string $articleTitle): string
    {
        $intro = 'Материал ниже дополняет основной текст: это обобщённые сведения, которые часто обсуждают в профессиональной среде при работе над темами вроде «'
            . htmlspecialchars($articleTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '».';

        $body = match ($block) {
            1 => 'На практике различают «японскую» и «чешскую» философии калибровки: у Miyuki и TOHO обычно выше однородность размера и формы отверстия, что упрощает сложное плетение и многократный проход нити; у чешского бисера (в том числе Preciosa) чаще встречается естественный разброс формы и чуть меньший диаметр отверстия, зато палитра и соотношение цена/объём часто выигрывают для масштабных проектов. Для ювелирных покрытий устойчивость цвета к УФ и трению у разных серий линейки может отличаться — имеет смысл хранить бисер с разделением по партиям (номер краски на упаковке), чтобы колье и браслет не «плавали» по оттенку.',
            2 => 'Пейот и кирпичный стежок дают схожий «сетчатый» рисунок, но отличаются направлением ряда и тем, как нить крепит новую бисерину: пейот обычно быстрее набирается ряд за рядом, кирпичный шов чаще даёт более жёсткое полотно и устойчивость формы — это важно для серёг и элементов с нагрузкой. Равномерный цилиндрический бисер (Delica и аналоги) снижает «змейку» полотна; при работе с рубкой стоит учитывать износ нити на острых кромках и подбирать более толстую или полиуретановую нить там, где бисерина режет волокно.',
            3 => 'В украшениях бисер сочетают с кожей, металлом и камнями: важно согласовать жёсткость конструкции (жёсткая основа vs гибкий жгут) и тип фурнитуры — замок и швензы лучше подбирать с запасом по разрывной нагрузке, чем «впритык». Для контакта с кожей носителя никелевые сплавы без покрытия у части людей вызывают раздражение; гипоаллергенные варианты — хирургическая сталь, серебро 925 пробы, титан, часть латунных покрытий с изоляционным слоем. Храните готовые работы сухо: влага и косметика ускоряют потемнение металла и ослабление нити.',
            4 => 'Цветочные объекты из бисера часто собирают на проволоке: чем тоньше жёсткая проволока, тем изящнее лепесток, но выше риск перегиба и поломки; мягкая проволока держит крупные лепестки, но хуже держит «стойку». Для букетов полезно продумывать центр тяжести — тяжёлый низ вазы или декоративный камень в горшке предотвращает опрокидывание. Смешение размеров бисера в одном лепестке даёт живой градиент, но усложняет посадку на каркас — тренируйтесь на одном мотиве, прежде чем плести весь букет.',
            5 => 'Мелкие фигурки и брелоки — зона повышенного износа: кольца и карабины лучше брать литыми, а не из тонкой проволоки. Для подарков детям избегайте острых элементов (длинная рубка, иглы) и мелких деталей без фиксации — размер бисера меньше 3 мм может быть небезопасен для малышей. Контрастные полоски и «глазки» из бисера читаются с расстояния лучше, чем сложные мелкие узоры.',
            6 => 'Сутаж — плоский шнур с центральной канавкой: при стёжке игла идёт по углублению, бисер и кабошон «запекаются» в объёме за счёт слоёв жгута. Сочетание бисера с кожей и тканью требует аккуратного крепления — клей по периметру текстиля может дать пятно; часто используют прошивку к краю и потайные петли. Натуральные камни с острыми углами стоит отшлифовать или обернуть, чтобы не резали нить.',
            7 => 'Стеклянные бусины известны тысячелетиями: в древности стекло было редким материалом, и мелкие бисерины участвовали в обмене на дальних маршрутах — отсюда привычка называть «торговыми» целые группы стеклянных бусин европейского производства, распространявшихся в разных колониях. Для музейной реконструкции традиционного костюма важны не только цвет, но и способ посадки — ровный ряд на ткани vs «рогожка» на коже даёт разный силуэт. Современные этнографические коллекции часто сочетают аутентичный узор с современной фурнитурой — это нормальная рабочая практика для носки.',
            8 => 'Тренды в рукоделии связаны с медиа и образом жизни: короткие видео сделали технику доступнее, а запрос на «медленное потребление» и уникальные вещи подталкивает к handmade. На подиуме бисер то появляется как роскошная вышивка, то как минималистичная геометрия — мастеру полезно отслеживать не столько бренды, сколько силуэт и палитру сезона. Неон и прозрачные покрытия хорошо смотрятся при контрастном фоне одежды; в работе учитывайте подложку — лайнированный и прозрачный бисер по-разному «собирают» фон.',
            default => '',
        };

        if ($body === '') {
            return '';
        }

        return '<section class="article-extra"><h2>Дополнительно</h2><p>' . $intro . '</p><p>'
            . htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p></section>\n";
    }

    /**
     * Импорт всех .txt из каталога. Существующие статьи с теми же topic_id+slug заменяются.
     *
     * @return int число импортированных статей
     */
    public static function importFromTxtDirectory(Database $db, string $directory): int
    {
        if (!is_dir($directory)) {
            return 0;
        }

        $files = glob($directory . '/*.txt') ?: [];
        sort($files, SORT_STRING);
        $count = 0;

        foreach ($files as $path) {
            $base = basename($path);
            if ($base === '_manifest.tsv' || !str_ends_with($base, '.txt')) {
                continue;
            }
            $meta = self::parseFilename($base);
            if ($meta === null || $meta['block'] < 1 || $meta['block'] > 8) {
                continue;
            }
            $topicId = self::BLOCK_TO_TOPIC[$meta['block']] ?? null;
            if ($topicId === null) {
                continue;
            }

            $raw = (string) file_get_contents($path);
            $raw = trim($raw);
            if ($raw === '') {
                continue;
            }

            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $raw));
            $title = trim($lines[0] ?? '');
            if ($title === '') {
                $title = $meta['titleLine'];
            }
            $bodyRest = implode("\n", array_slice($lines, 1));
            $html = self::plainTextToHtml($bodyRest);
            $extra = self::enrichmentHtml($meta['block'], $title);
            if ($extra !== '') {
                $html .= "\n" . $extra;
            }

            $tail = SlugHelper::slugify($title);
            if ($tail === 'statya') {
                $tail = SlugHelper::slugify($meta['titleLine']);
            }
            // Уникальный адрес: номер раздела-подраздела + тема (без коллизий заголовков).
            $slug = $meta['block'] . '-' . $meta['sub'] . '-' . $tail;
            if (mb_strlen($slug, 'UTF-8') > 90) {
                $slug = mb_substr($slug, 0, 90, 'UTF-8');
                $slug = rtrim($slug, '-');
            }

            $existingId = self::findArticleIdByTopicSlug($db, $topicId, $slug);

            $excerpt = mb_substr(preg_replace('/\s+/u', ' ', strip_tags($html)) ?: '', 0, 280, 'UTF-8');
            $sortOrder = $meta['block'] * 100 + $meta['sub'];
            if ($existingId !== null) {
                $db->updateArticle(
                    $existingId,
                    $topicId,
                    $title,
                    $slug,
                    $excerpt,
                    $html,
                    true,
                    $sortOrder
                );
            } else {
                $db->createArticle($topicId, $title, $slug, $excerpt, $html, true, $sortOrder);
            }
            ++$count;
        }

        return $count;
    }

    private static function findArticleIdByTopicSlug(Database $db, string $topicId, string $slug): ?string
    {
        $st = $db->pdo()->prepare('SELECT id FROM articles WHERE topic_id = ? AND slug = ?');
        $st->execute([$topicId, $slug]);
        $id = $st->fetchColumn();

        return $id !== false ? (string) $id : null;
    }
}
