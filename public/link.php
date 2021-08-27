<?php
echo 'link:';
$target = "/home/danangsu/teebiz.net/storage/app/public";
$shortcut = "/home/danangsu/teebiz.net/public/storage";

echo symlink($target, $shortcut);
?>
