<?php
/**
 * Fixture: legacy newStyle fragment with category read before article HTML (like prod ~line 260).
 */
$_lireate_legacy_cat = $news_article[0]['category'];
?>
<section id="articleBox">
<div class="article-wrap">
<h1><?php echo htmlspecialchars($news_article[0]['name']); ?></h1>
<div id="articleInfo">
  <a href="<?php echo $catUrl; ?>" id="tag"><?php echo htmlspecialchars($category); ?></a>
  <p><?php echo date('d.m.Y', $news_article[0]['datetime']); ?></p>
</div>
<a class="returnBlog" href="/news/">Вернуться к другим новостям</a>
<div id="textBox">
<?php echo $news_article[0]['full_story']; ?>
</div>
</div>
</section>
