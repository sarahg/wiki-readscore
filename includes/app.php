<?php
/**
 * @file app.php
 */

require '../vendor/autoload.php';

Use Readscore\ArticleLister;

$category = $_POST['category'];
echo new ArticleLister($category);

?>