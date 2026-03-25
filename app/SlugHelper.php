<?php

declare(strict_types=1);

namespace App;

/**
 * Латиница для URL-фрагментов из русских заголовков (статьи, темы).
 */
final class SlugHelper
{
    /** @var array<string, string> */
    private const CYR_TO_LAT = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    public static function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $buf = '';
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($text, $i, 1, 'UTF-8');
            if (isset(self::CYR_TO_LAT[$ch])) {
                $buf .= self::CYR_TO_LAT[$ch];
                continue;
            }
            if (preg_match('/^[a-z0-9]$/', $ch)) {
                $buf .= $ch;
                continue;
            }
            if ($ch === '-' || $ch === ' ' || $ch === '_' || $ch === '—' || $ch === '–') {
                $buf .= '-';
            }
        }
        $buf = preg_replace('/-+/', '-', $buf) ?? '';
        $buf = trim($buf, '-');

        return $buf !== '' ? $buf : 'statya';
    }
}
