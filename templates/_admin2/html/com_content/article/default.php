<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

echo '<div class="m3widget_main">';

if ($this->params->get('show_title')) {
?>
 <h2 class="art-postheader"><?php echo $this->article->title; ?></h2>
<?php
}
echo "<div class=\"art-postcontent\">\r\n    <!-- article-content -->\r\n";
echo "<div class=\"art-article\">";
echo $this->article->text;
echo "</div>";
echo "\r\n    <!-- /article-content -->\r\n</div>\r\n<div class=\"cleared\"></div>\r\n";
echo '</div>';
?>
