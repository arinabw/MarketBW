<?php

// FILE: app/Seed.php
// VERSION: 3.12.4
// START_MODULE_CONTRACT
//   PURPOSE: Вставка демо-данных (категории, товары, отзывы, FAQ) при пустых таблицах
//   SCOPE: ifEmpty() — проверка COUNT(*) и INSERT демо-записей
//   DEPENDS: M-DATABASE (вызывается из Database::init())
//   LINKS: M-SEED
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   ifEmpty       — проверяет таблицы и вставляет демо-данные
//   DEMO_CATEGORIES — 3 категории
//   DEMO_REVIEWS    — 3 отзыва
//   DEMO_FAQS       — 8 FAQ
// END_MODULE_MAP

declare(strict_types=1);

namespace App;

use PDO;

final class Seed
{
    /** @param list<array{0:string,1:string,2:string,3:string}> */
    private const DEMO_CATEGORIES = [
        ['bracelet', 'Браслеты', 'Элегантные браслеты из бисера ручной работы', '/images/categories/bracelets.svg'],
        ['necklace', 'Колье', 'Уникальные колье и ожерелья из бисера', '/images/categories/necklaces.svg'],
        ['earrings', 'Серьги', 'Нежные серьги из бисера для любого образа', '/images/categories/earrings.svg'],
    ];

    public static function ifEmpty(PDO $pdo): void
    {
        $n = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
        if ($n === 0) {
            $st = $pdo->prepare('INSERT INTO categories (id, name, description, image) VALUES (?, ?, ?, ?)');
            foreach (self::DEMO_CATEGORIES as $c) {
                $st->execute($c);
            }
        }

        $n = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        if ($n === 0) {
            $sql = <<<'SQL'
INSERT INTO products
(id, name, description, price, category, images, materials, size, technique, in_stock, featured, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL;
            $st = $pdo->prepare($sql);
            foreach (self::demoProducts() as $p) {
                $st->execute($p);
            }
        }

        $n = (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
        if ($n === 0) {
            $st = $pdo->prepare(
                'INSERT INTO reviews (id, author, rating, text, date, product_id) VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach (self::DEMO_REVIEWS as $r) {
                $st->execute($r);
            }
        }

        $n = (int) $pdo->query('SELECT COUNT(*) FROM faqs')->fetchColumn();
        if ($n === 0) {
            $st = $pdo->prepare('INSERT INTO faqs (id, question, answer, category) VALUES (?, ?, ?, ?)');
            foreach (self::DEMO_FAQS as $f) {
                $st->execute($f);
            }
        }

    }

    /** @return list<array<int, mixed>> */
    private static function demoProducts(): array
    {
        return [
            [
                '1',
                'Розовый браслет «Весенняя нежность»',
                'Изящный браслет из бисера нежно-розовых оттенков, созданный с любовью и вниманием к деталям. Идеально дополнит ваш романтичный образ.',
                2500.0,
                'bracelet',
                '["/images/products/bracelet-1-1.svg","/images/products/bracelet-1-2.svg","/images/products/bracelet-1-3.svg"]',
                '["Чешский бисер","Нитки мулине","Застежка из ювелирного сплава"]',
                'Обхват 16-18 см',
                'Монастырское плетение',
                1,
                1,
                '2024-01-15 00:00:00',
            ],
            [
                '2',
                'Колье «Лавандовые мечты»',
                'Элегантное колье в лавандовых тонах, созданное техникой мозаичного плетения. Станет украшением вашего вечернего образа.',
                4500.0,
                'necklace',
                '["/images/products/necklace-1-1.svg","/images/products/necklace-1-2.svg"]',
                '["Японский бисер TOHO","Серебряная фурнитура"]',
                'Длина 45 см',
                'Мозаичное плетение',
                1,
                1,
                '2024-01-20 00:00:00',
            ],
            [
                '3',
                'Серьги «Нежные капли»',
                'Лёгкие серьги из бисера в форме капель. Идеальны для повседневного ношения и особых случаев.',
                1800.0,
                'earrings',
                '["/images/products/earrings-1-1.svg","/images/products/earrings-1-2.svg"]',
                '["Бисер PRECIOSA","Серебряные крючки"]',
                'Длина 4 см',
                'Кирпичное плетение',
                1,
                0,
                '2024-02-01 00:00:00',
            ],
            [
                '5',
                'Браслет «Морская волна»',
                'Свежий браслет в синих и бирюзовых тонах, напоминающий о морских просторах. Создан техникой сеточного плетения.',
                2800.0,
                'bracelet',
                '["/images/products/bracelet-2-1.svg","/images/products/bracelet-2-2.svg"]',
                '["Бисер разных оттенков синего","Эластичная нить"]',
                'Обхват 17-19 см',
                'Сеточное плетение',
                1,
                1,
                '2024-02-15 00:00:00',
            ],
            [
                '6',
                'Колье «Винтажная роза»',
                'Роскошное колье в винтажном стиле с центральным элементом в виде розы. Создано для особенных случаев.',
                6500.0,
                'necklace',
                '["/images/products/necklace-2-1.svg","/images/products/necklace-2-2.svg","/images/products/necklace-2-3.svg"]',
                '["Бисер PRECIOSA","Россыпи кристаллов","Бронзовая фурнитура"]',
                'Длина 50 см',
                'Объемное плетение',
                1,
                1,
                '2024-02-20 00:00:00',
            ],
        ];
    }

    /** @var list<array{0:string,1:string,2:int,3:string,4:string,5:?string}> */
    private const DEMO_REVIEWS = [
        ['1', 'Анна Петрова', 5, 'Заказала браслет «Весенняя нежность» — просто восторг! Очень качественная работа.', '2024-01-25 00:00:00', '1'],
        ['2', 'Мария Соколова', 5, 'Колье «Лавандовые мечты» превзошло все мои ожидания!', '2024-02-05 00:00:00', '2'],
        ['3', 'Елена Иванова', 5, 'Серьги «Нежные капли» — мои любимые! Лёгкие, красивые.', '2024-02-12 00:00:00', '3'],
    ];

    /** @var list<array{0:string,1:string,2:string,3:string}> */
    private const DEMO_FAQS = [
        ['1', 'Как сделать заказ?', 'Для заказа выберите изделие и свяжитесь со мной через контакты на сайте.', 'order'],
        ['2', 'Какие способы оплаты?', 'Банковская карта, перевод на карту, электронные кошельки.', 'order'],
        ['3', 'Как долго выполняется заказ?', 'В наличии — отправка 1–2 дня; на заказ — 5–7 дней.', 'shipping'],
        ['4', 'Как осуществляется доставка?', 'Почта России, СДЭК и другие ТК по согласованию.', 'shipping'],
        ['5', 'Как ухаживать за изделиями?', 'Беречь от влаги, солнца и ударов; хранить в сухом месте.', 'care'],
        ['6', 'Можно ли мыть изделия?', 'Мыть не рекомендуется; при необходимости — слегка протереть и высушить.', 'care'],
        ['7', 'Какой бисер используется?', 'PRECIOSA, TOHO, Miyuki — качественный бисер для долговечности.', 'materials'],
        ['8', 'Индивидуальный заказ?', 'Да, напишите — обсудим цвет, размер и дизайн.', 'order'],
    ];
}
