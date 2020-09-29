<?php
    $document = JFactory::getDocument();
?>
    <header class="u-clearfix u-header u-palette-2-base u-header" id="sec-e6d6">
  <div class="u-align-left u-clearfix u-sheet u-sheet-1">
    <h3 class="u-align-left-xs u-headline u-text u-text-1">
      <a href="<?php echo JFactory::getDocument()->baseurl; ?>"><?php $siteTitle = getThemeParams('siteTitle');if ($siteTitle) {   echo $siteTitle; } else {    ob_start(); ?> Models Agency   <?php echo ob_get_clean();}?></a>
    </h3>
    <?php echo CoreStatements::position('hmenu', '', 1, 'hmenu'); ?>
  </div>
</header>