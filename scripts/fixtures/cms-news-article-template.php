<?php
/**
 * Fixture: legacy CMS single-news view (for lireate_blog_install tests).
 */
$name = isset($name) ? $name : 'Тестовая новость';
$full_story = isset($full_story) ? $full_story : '<p>Тело</p>';
$date = isset($date) ? $date : '19.10.2026';
$category_name = isset($category_name) ? $category_name : 'Товарный знак';
$seo_url = isset($seo_url) ? $seo_url : 'test_news_article';
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title><?php echo htmlspecialchars($name); ?></title></head>
<body class="cms-default">
<h1><?php echo htmlspecialchars($name); ?></h1>
<div id="articleInfo">
  <a href="/news/" id="tag"><?php echo htmlspecialchars($category_name); ?></a>
  <p><?php echo htmlspecialchars($date); ?></p>
</div>
<a class="returnBlog" href="/news/">Вернуться к другим новостям</a>
<div id="textBox">
<?php echo $full_story; ?>
</div>
</body>
</html>
