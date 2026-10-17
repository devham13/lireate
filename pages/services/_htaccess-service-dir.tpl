# LIREATE service directory — copy as .htaccess next to index.php
DirectoryIndex index.php
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^$ index.php [END]
</IfModule>
