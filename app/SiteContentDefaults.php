<?php

// FILE: app/SiteContentDefaults.php
// VERSION: 3.12.4
// START_MODULE_CONTRACT
//   PURPOSE: Ключи и дефолтные значения CMS-текстов; группы для админки; image/boolean ключи
//   SCOPE: defaults, adminGroups, imageKeys, layoutBooleanKeys, fieldLabel, inheritLegacyLayoutToggles
//   DEPENDS: none
//   LINKS: M-CONTENT-DEFAULTS
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   allKeys                    — список всех ключей
//   defaults                   — массив key => default_value
//   adminGroups                — группы ключей для формы в админке
//   imageKeys                  — ключи с путями к картинкам
//   layoutBooleanKeys          — чекбоксы видимости элементов
//   fieldLabel                 — человекочитаемая метка по ключу
//   inheritLegacyLayoutToggles — миграция устаревших layout-ключей
// END_MODULE_MAP

declare(strict_types=1);

namespace App;

final class SiteContentDefaults
{
    /** @return list<string> */
    public static function allKeys(): array
    {
        return array_keys(self::defaults([]));
    }

    /**
     * @param array<string, mixed> $settings из config/settings.php (для контактов и бренда)
     *
     * @return array<string, string>
     */
    public static function defaults(array $settings): array
    {
        return [
            // Бренд и контакты (подставляются из .env, если в БД пусто — см. merge в Database)
            'brand.master_name' => (string) ($settings['master_name'] ?? 'Мастер'),
            'brand.tagline' => (string) ($settings['master_tagline'] ?? 'Украшения из бисера ручной работы'),
            'contact.email' => (string) ($settings['contact_email'] ?? ''),
            'contact.phone' => (string) ($settings['contact_phone'] ?? ''),
            'contact.whatsapp' => (string) ($settings['contact_whatsapp'] ?? ''),
            'social.instagram' => (string) ($settings['social_instagram'] ?? '#'),
            'social.telegram' => (string) ($settings['social_telegram'] ?? '#'),
            'social.vk' => (string) ($settings['social_vk'] ?? '#'),

            'meta.description' => '%SITE% — %TAGLINE% Авторские украшения и бижутерия из бисера: колье, браслеты, серьги, броши. Оригинальные подарки ручной работы, бисероплетение на заказ.',
            'meta.keywords' => 'украшения из бисера, бисер, бисероплетение, бижутерия, бижутерия ручной работы, оригинальные подарки, подарки ручной работы, рукоделие, авторские украшения, изделия из бисера, колье из бисера, браслеты из бисера, серьги из бисера, броши из бисера, купить украшение из бисера, бисер купить, бисерные украшения, handmade, %SITE%',

            'layout.show_home_hero_badge' => '1',
            'layout.show_home_hero_title' => '1',
            'layout.show_home_hero_lead' => '1',
            'layout.show_home_hero_buttons' => '1',
            'layout.show_home_hero_image' => '1',
            'layout.show_home_categories_title' => '1',
            'layout.show_home_categories_sub' => '1',
            'layout.show_home_categories_grid' => '1',
            'layout.show_home_featured_title' => '1',
            'layout.show_home_featured_sub' => '1',
            'layout.show_home_featured_grid' => '1',
            'layout.show_home_reviews_title' => '1',
            'layout.show_home_reviews_sub' => '1',
            'layout.show_home_reviews_grid' => '1',
            'layout.show_product_breadcrumbs' => '1',
            'layout.show_product_gallery_main' => '1',
            'layout.show_product_gallery_thumbs' => '1',
            'layout.show_product_title' => '1',
            'layout.show_product_price' => '1',
            'layout.show_product_description' => '1',
            'layout.show_product_meta' => '1',
            'layout.show_product_materials' => '1',
            'layout.show_product_order_title' => '1',
            'layout.show_product_order_steps' => '1',
            'layout.show_product_btn_order' => '1',
            'layout.show_product_btn_telegram' => '1',
            'layout.show_product_btn_whatsapp' => '1',
            'layout.show_product_reviews_title' => '1',
            'layout.show_product_review_items' => '1',

            'header.logo_suffix' => 'бисер',
            'nav.home' => 'Главная',
            'nav.catalog' => 'Каталог',
            'nav.about' => 'О мастере',
            'nav.contact' => 'Контакты',
            'nav.faq' => 'FAQ',
            'nav.articles' => 'Статьи',

            'articles.page_title' => 'Статьи о бисере',
            'articles.page_sub' => 'Материалы, техники, идеи и вдохновение для рукоделия.',
            'articles.topic_empty' => 'В этой теме пока нет опубликованных материалов.',
            'articles.read' => 'Читать',
            'articles.breadcrumb' => 'Статьи',

            'home.badge_prefix' => '✨ ',
            'home.badge_suffix' => ' — бисерные чудеса',
            'home.hero_title' => 'Маленькие чудеса ',
            'home.hero_title_accent' => 'из бисера',
            'home.hero_lead' => 'Авторская бижутерия и украшения из бисера ручной работы: колье, браслеты, серьги, броши. Оригинальные подарки и уникальные изделия — бисероплетение с любовью к деталям.',
            'home.btn_catalog' => 'Открыть каталог',
            'home.btn_contact' => 'Связаться с мастером',
            'home.hero_image' => '/images/hero-lace.png',
            'home.hero_image_alt' => 'Украшение из бисера — рукоделие, авторская работа для подарка',
            'home.section_categories_title' => 'Категории',
            'home.section_categories_sub' => 'Украшения из бисера ручной работы, бижутерия, оригинальные подарки — выберите категорию в каталоге.',
            'home.section_featured_title' => 'Избранное',
            'home.section_featured_sub' => 'Авторская бижутерия в наличии — бисерные украшения и оригинальные подарки ручной работы.',
            'home.featured_empty' => 'Пока нет избранных товаров — загляните в <a href="/catalog">каталог</a>.',
            'home.section_reviews_title' => 'Отзывы',
            'home.section_reviews_sub' => 'Несколько слов от покупательниц.',
            'home.card_link' => 'Смотреть →',
            'home.card_more' => 'Подробнее',

            'footer.note' => 'Изделия ручной работы: сроки и возврат обсуждаются индивидуально.',

            'about.page_title' => 'О мастере',
            'about.intro_p1' => 'Я создаю украшения из бисера вручную — колье, браслеты, серьги и другие изделия. Каждая работа уникальна: подбираю цвета, фурнитуру и технику плетения так, чтобы украшение гармонировало с вашим образом.',
            'about.intro_p2' => 'Работаю с качественным бисером (PRECIOSA, TOHO, Miyuki), нитками и фурнитурой, которые не теряют вид со временем. Если хотите что-то особенное — напишите: обсудим идею, сроки и стоимость.',
            'about.btn_catalog' => 'Смотреть каталог',
            'about.btn_contact' => 'Связаться',

            'contact.page_title' => 'Свяжитесь со мной',
            'contact.page_sub' => 'Расскажите о вашей идее — вместе создадим украшение из бисера.',
            'contact.box_contacts_title' => 'Контакты',
            'contact.box_message_title' => 'Написать сообщение',
            'contact.label_phone' => 'Телефон:',
            'contact.label_email' => 'Email:',
            'contact.label_social' => 'Соцсети:',
            'contact.btn_whatsapp' => 'Написать в WhatsApp',
            'contact.btn_telegram' => 'Открыть Telegram',
            'contact.form_name' => 'Имя',
            'contact.form_email' => 'Email',
            'contact.form_message' => 'Сообщение',
            'contact.form_submit' => 'Отправить',
            'contact.form_success' => 'Спасибо! Сообщение сохранено — мастер увидит его в админке и ответит. Обычно в течение суток: проверьте почту или мессенджер.',
            'contact.form_error' => 'Укажите имя и текст сообщения.',
            'contact.form_hint' => 'Заявка попадает в личный кабинет мастера (раздел «Заявки»). Также можно написать на почту или в мессенджер.',

            'catalog.page_title' => 'Каталог',
            'catalog.page_sub' => 'Каталог бижутерии и украшений из бисера ручной работы: колье, браслеты, серьги, броши. Оригинальные подарки, бисероплетение на заказ.',
            'catalog.search_placeholder' => 'Поиск по названию',
            'catalog.sort_new' => 'Сначала новые',
            'catalog.sort_price_asc' => 'Цена: по возрастанию',
            'catalog.sort_price_desc' => 'Цена: по убыванию',
            'catalog.btn_apply' => 'Применить',
            'catalog.chip_all' => 'Все',
            'catalog.card_open' => 'Открыть',
            'catalog.empty_hint' => 'Ничего не найдено. <a href="/catalog">Сбросить фильтры</a> или измените запрос.',

            'faq.page_title' => 'Вопросы и ответы',
            'faq.page_sub' => 'Как заказать украшения из бисера, доставка бижутерии и подарков ручной работы, уход за бисерными изделиями.',

            'product.breadcrumb_home' => 'Главная',
            'product.breadcrumb_catalog' => 'Каталог',
            'product.meta_technique' => 'Техника',
            'product.meta_size' => 'Размер',
            'product.meta_stock' => 'В наличии',
            'product.meta_stock_yes' => 'да',
            'product.meta_stock_no' => 'под заказ',
            'product.materials_label' => 'Материалы',
            'product.order_title' => 'Как заказать',
            'product.order_step_1' => 'Напишите в <a href="/contact">контакты</a> или в мессенджер — укажите изделие или идею.',
            'product.order_step_2' => 'Согласуем детали, срок и стоимость.',
            'product.order_step_3' => 'После подтверждения — изготовление и отправка.',
            'product.btn_order' => 'Заказать или задать вопрос',
            'product.reviews_title' => 'Отзывы об этом изделии',

            'error404.text' => 'Такой страницы нет. Перейдите в каталог или на главную.',

            'audit.log_enabled' => '0',
            'audit.log_verbose' => '1',
        ];
    }

    /** @return list<string> */
    public static function imageKeys(): array
    {
        return [
            'home.hero_image',
        ];
    }

    /**
     * Секции админки «Видимость» (заголовок => список ключей чекбоксов).
     *
     * @return array<string, list<string>>
     */
    public static function visibilityAdminGroups(): array
    {
        return [
            'Видимость: главная — блок «герой»' => [
                'layout.show_home_hero_badge',
                'layout.show_home_hero_title',
                'layout.show_home_hero_lead',
                'layout.show_home_hero_buttons',
                'layout.show_home_hero_image',
            ],
            'Видимость: главная — категории' => [
                'layout.show_home_categories_title',
                'layout.show_home_categories_sub',
                'layout.show_home_categories_grid',
            ],
            'Видимость: главная — избранное' => [
                'layout.show_home_featured_title',
                'layout.show_home_featured_sub',
                'layout.show_home_featured_grid',
            ],
            'Видимость: главная — отзывы' => [
                'layout.show_home_reviews_title',
                'layout.show_home_reviews_sub',
                'layout.show_home_reviews_grid',
            ],
            'Видимость: страница товара' => [
                'layout.show_product_breadcrumbs',
                'layout.show_product_gallery_main',
                'layout.show_product_gallery_thumbs',
                'layout.show_product_title',
                'layout.show_product_price',
                'layout.show_product_description',
                'layout.show_product_meta',
                'layout.show_product_materials',
                'layout.show_product_order_title',
                'layout.show_product_order_steps',
                'layout.show_product_btn_order',
                'layout.show_product_btn_telegram',
                'layout.show_product_btn_whatsapp',
                'layout.show_product_reviews_title',
                'layout.show_product_review_items',
            ],
        ];
    }

    /**
     * Ключи «вкл/выкл» для видимости (чекбоксы в админке, значения 1/0).
     *
     * @return list<string>
     */
    public static function layoutBooleanKeys(): array
    {
        $keys = [];
        foreach (self::visibilityAdminGroups() as $group) {
            foreach ($group as $k) {
                $keys[] = $k;
            }
        }

        return $keys;
    }

    /**
     * Если в БД остались старые флаги блоков (до детальных переключателей),
     * применяем их к дочерним ключам, пока для дочернего нет своей записи в site_content.
     *
     * @param array<string, string> $merged
     * @param array<string, string> $over только то, что реально пришло из БД
     *
     * @return array<string, string>
     */
    public static function inheritLegacyLayoutToggles(array $merged, array $over): array
    {
        /** @var array<string, list<string>> $map */
        $map = [
            'layout.show_home_hero' => [
                'layout.show_home_hero_badge',
                'layout.show_home_hero_title',
                'layout.show_home_hero_lead',
                'layout.show_home_hero_buttons',
                'layout.show_home_hero_image',
            ],
            'layout.show_home_categories' => [
                'layout.show_home_categories_title',
                'layout.show_home_categories_sub',
                'layout.show_home_categories_grid',
            ],
            'layout.show_home_featured' => [
                'layout.show_home_featured_title',
                'layout.show_home_featured_sub',
                'layout.show_home_featured_grid',
            ],
            'layout.show_home_reviews' => [
                'layout.show_home_reviews_title',
                'layout.show_home_reviews_sub',
                'layout.show_home_reviews_grid',
            ],
            'layout.show_product_reviews' => [
                'layout.show_product_reviews_title',
                'layout.show_product_review_items',
            ],
        ];
        foreach ($map as $legacy => $children) {
            if (!array_key_exists($legacy, $over)) {
                continue;
            }
            $pv = $merged[$legacy] ?? '0';
            foreach ($children as $ch) {
                if (!array_key_exists($ch, $over)) {
                    $merged[$ch] = $pv;
                }
            }
        }

        return $merged;
    }

    public static function adminGroups(): array
    {
        return array_merge(self::visibilityAdminGroups(), [
            'Бренд, контакты и соцсети' => [
                'brand.master_name', 'brand.tagline', 'contact.email', 'contact.phone', 'contact.whatsapp',
                'social.instagram', 'social.telegram', 'social.vk',
            ],
            'SEO' => ['meta.description', 'meta.keywords'],
            'Шапка и меню' => [
                'header.logo_suffix', 'nav.home', 'nav.catalog', 'nav.about', 'nav.contact', 'nav.faq', 'nav.articles',
            ],
            'Главная страница' => [
                'home.badge_prefix', 'home.badge_suffix', 'home.hero_title', 'home.hero_title_accent', 'home.hero_lead',
                'home.btn_catalog', 'home.btn_contact', 'home.hero_image', 'home.hero_image_alt',
                'home.section_categories_title', 'home.section_categories_sub',
                'home.section_featured_title', 'home.section_featured_sub', 'home.featured_empty',
                'home.section_reviews_title', 'home.section_reviews_sub', 'home.card_link', 'home.card_more',
            ],
            'Подвал' => ['footer.note'],
            'О мастере' => [
                'about.page_title', 'about.intro_p1', 'about.intro_p2', 'about.btn_catalog', 'about.btn_contact',
            ],
            'Контакты (страница)' => [
                'contact.page_title', 'contact.page_sub', 'contact.box_contacts_title', 'contact.box_message_title',
                'contact.label_phone', 'contact.label_email', 'contact.label_social',
                'contact.btn_whatsapp', 'contact.btn_telegram',
                'contact.form_name', 'contact.form_email', 'contact.form_message', 'contact.form_submit',
                'contact.form_success', 'contact.form_error', 'contact.form_hint',
            ],
            'Каталог' => [
                'catalog.page_title', 'catalog.page_sub', 'catalog.search_placeholder',
                'catalog.sort_new', 'catalog.sort_price_asc', 'catalog.sort_price_desc', 'catalog.btn_apply',
                'catalog.chip_all', 'catalog.card_open', 'catalog.empty_hint',
            ],
            'FAQ (заголовки страницы)' => ['faq.page_title', 'faq.page_sub'],
            'Статьи (заголовки раздела)' => [
                'articles.page_title', 'articles.page_sub', 'articles.topic_empty', 'articles.read', 'articles.breadcrumb',
            ],
            'Карточка товара' => [
                'product.breadcrumb_home', 'product.breadcrumb_catalog',
                'product.meta_technique', 'product.meta_size', 'product.meta_stock', 'product.meta_stock_yes', 'product.meta_stock_no',
                'product.materials_label', 'product.order_title', 'product.order_step_1', 'product.order_step_2', 'product.order_step_3',
                'product.btn_order', 'product.reviews_title',
            ],
            'Ошибка 404' => ['error404.text'],
            'Журнал событий (аудит)' => [
                'audit.log_enabled',
                'audit.log_verbose',
            ],
        ]);
    }

    public static function fieldLabel(string $key): string
    {
        return match ($key) {
            'brand.master_name' => 'Имя мастера',
            'brand.tagline' => 'Слоган бренда',
            'contact.email' => 'Email',
            'contact.phone' => 'Телефон (как на сайте)',
            'contact.whatsapp' => 'WhatsApp (только цифры)',
            'social.instagram' => 'Instagram URL',
            'social.telegram' => 'Telegram URL',
            'social.vk' => 'ВКонтакте URL',
            'meta.description' => 'Meta description (подставьте %SITE% и %TAGLINE%; 150–160 символов желательно)',
            'meta.keywords' => 'Meta keywords (через запятую; для Яндекса; %SITE% и %TAGLINE%)',
            'layout.show_home_hero_badge' => 'Главная, герой: строка-бейдж над заголовком',
            'layout.show_home_hero_title' => 'Главная, герой: заголовок (H1)',
            'layout.show_home_hero_lead' => 'Главная, герой: текст под заголовком',
            'layout.show_home_hero_buttons' => 'Главная, герой: кнопки «Каталог» и «Связаться»',
            'layout.show_home_hero_image' => 'Главная, герой: картинка справа',
            'layout.show_home_categories_title' => 'Главная, категории: заголовок секции',
            'layout.show_home_categories_sub' => 'Главная, категории: подзаголовок',
            'layout.show_home_categories_grid' => 'Главная, категории: сетка карточек',
            'layout.show_home_featured_title' => 'Главная, избранное: заголовок секции',
            'layout.show_home_featured_sub' => 'Главная, избранное: подзаголовок',
            'layout.show_home_featured_grid' => 'Главная, избранное: карточки товаров (и пустое состояние)',
            'layout.show_home_reviews_title' => 'Главная, отзывы: заголовок секции',
            'layout.show_home_reviews_sub' => 'Главная, отзывы: подзаголовок',
            'layout.show_home_reviews_grid' => 'Главная, отзывы: список отзывов',
            'layout.show_product_breadcrumbs' => 'Товар: хлебные крошки',
            'layout.show_product_gallery_main' => 'Товар: главное фото',
            'layout.show_product_gallery_thumbs' => 'Товар: миниатюры галереи',
            'layout.show_product_title' => 'Товар: название',
            'layout.show_product_price' => 'Товар: цена',
            'layout.show_product_description' => 'Товар: описание',
            'layout.show_product_meta' => 'Товар: блок «Техника / размер / наличие»',
            'layout.show_product_materials' => 'Товар: список материалов',
            'layout.show_product_order_title' => 'Товар: заголовок «Как заказать»',
            'layout.show_product_order_steps' => 'Товар: нумерованные шаги заказа',
            'layout.show_product_btn_order' => 'Товар: кнопка «Заказать / вопрос»',
            'layout.show_product_btn_telegram' => 'Товар: кнопка Telegram',
            'layout.show_product_btn_whatsapp' => 'Товар: кнопка WhatsApp',
            'layout.show_product_reviews_title' => 'Товар: заголовок блока отзывов',
            'layout.show_product_review_items' => 'Товар: сами отзывы',
            'header.logo_suffix' => 'Текст в логотипе после названия',
            'nav.home' => 'Меню: Главная',
            'nav.catalog' => 'Меню: Каталог',
            'nav.about' => 'Меню: О мастере',
            'nav.contact' => 'Меню: Контакты',
            'nav.faq' => 'Меню: FAQ',
            'nav.articles' => 'Меню: Статьи',
            'articles.page_title' => 'Статьи: заголовок H1 на списке тем',
            'articles.page_sub' => 'Статьи: подзаголовок под H1',
            'articles.topic_empty' => 'Статьи: текст, если в теме нет опубликованных статей',
            'articles.read' => 'Статьи: ссылка «Читать» на карточке',
            'articles.breadcrumb' => 'Статьи: подпись в хлебных крошках',
            'home.hero_image' => 'Главная: картинка hero, лучше 4:3 (путь или загрузка)',
            'audit.log_enabled' => 'Журнал: включить запись всех HTTP-запросов (1 = да, 0 = нет)',
            'audit.log_verbose' => 'Журнал: подробный режим (query string, referer, имена полей POST/файлов; 1/0)',
            default => $key,
        };
    }

    /**
     * @param array<string, string> $merged
     *
     * @return array<string, string>
     */
    public static function applyMetaPlaceholders(array $merged, string $siteName): array
    {
        $tag = $merged['brand.tagline'] ?? '';
        if (isset($merged['meta.description'])) {
            $merged['meta.description'] = str_replace(
                ['%SITE%', '%TAGLINE%'],
                [$siteName, $tag],
                $merged['meta.description']
            );
        }
        if (isset($merged['meta.keywords'])) {
            $merged['meta.keywords'] = str_replace(
                ['%SITE%', '%TAGLINE%'],
                [$siteName, $tag],
                $merged['meta.keywords']
            );
        }

        return $merged;
    }
}
