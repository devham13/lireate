<?php
/**
 * Fixture: production CMS news list fragment (section#blog on /news/).
 */
$news = isset($news) ? $news : array(
    array(
        'name' => 'Ответ на претензию правообладателя товарного знака: порядок, срок 30 дней и образец',
        'seo_url' => 'otvet_na_pretenziyu_pravoobladatelya_tovarnogo_znaka_poryadok_srok_30_dney_i_obrazec',
        'datetime' => 1760832000,
        'category_name' => 'Товарный знак',
        'short_story' => 'Пришла претензия о нарушении чужого товарного знака: требуют убрать товар и заплатить компенсацию.',
    ),
    array(
        'name' => 'Проверка товарного знака перед регистрацией: чек-лист по реестрам Роспатента',
        'seo_url' => 'proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta',
        'datetime' => 1760832000,
        'category_name' => 'Товарный знак',
        'short_story' => 'Проверка по бесплатным базам Роспатента и ФИПС отсекает часть рисков до подачи.',
    ),
);
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Новости</title></head>
<body class="cms-default">
<section id="blog">
    <div class="sectionPos">
        <h2>Самое свежее и интересное</h2>
        <div class="tags">
            <a href="/news/">Все категории</a>
            <a href="?num=2&amp;category=9" id="cat2">Товарный знак</a>
        </div>
        <div class="blogFace">
            <div class="lastNews">
                <a class="imgBlock" href="/<?php echo htmlspecialchars($news[0]['seo_url']); ?>">
                    <div class="faceTitle">
                        <p>Последняя новость</p>
                        <h4><?php echo htmlspecialchars($news[0]['name']); ?></h4>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="sectionPos2">
        <div class="articlesBlock" id="atlass">
            <?php foreach ($news as $item) : ?>
            <a href="/<?php echo htmlspecialchars($item['seo_url']); ?>">
                <div>
                    <div class="imgBackground"></div>
                    <div class="imgDescript">
                        <p><?php echo htmlspecialchars($item['name']); ?></p>
                        <p class="articlesDate">Дата: <?php echo date('d.m.Y', (int) $item['datetime']); ?></p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</body>
</html>
