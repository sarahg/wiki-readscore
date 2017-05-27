<?php
/**
 * @file app.php
 */

include_once('ArticleLister.php');

$category = $_POST['category'];
echo new ArticleLister($category);

?>