<?php
/**
 * Fixture: production CMS fragment (template/page/newStyle.php ~276–300).
 */
?>
<section id="articleBox">
<div class="article-wrap">
<h1><?php echo htmlspecialchars($news_article[0]['name']); ?></h1>
<div id="articleInfo">
  <a href="<?php echo $catUrl; ?>" id="tag"><?php echo htmlspecialchars($category); ?></a>
  <p><?php echo date('d.m.Y', $news_article[0]['datetime']); ?></p>
</div>
<a class="returnBlog" href="/news/">Вернуться к другим новостям</a>
<?php
$imgBlock = '';
if (preg_match('/<img[^>]+src="([^"]*\\/template\\/images\\/blog\\/[^"]+)"/i', $news_article[0]['full_story'], $m)) {
    $imgBlock = '<img src="' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '" alt="" class="article-lead-img">';
}
?>
<div id="imgBlock"><?php echo $imgBlock; ?></div>
<div id="textBox">
<?php echo str_replace('<img alt=', '<img alt=""', $news_article[0]['full_story']); ?>
</div>
</div>
</section>
