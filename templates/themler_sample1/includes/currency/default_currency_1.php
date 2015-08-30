<?php
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'functions.php'; ?>

<!-- Currency Selector Module -->
<?php echo $text_before ?>
<form action="<?php echo JURI::getInstance()->toString(); ?>" method="post">
	<?php
$settings = array(
    'showLabel' => true,
    'showArrow' => true,
    'textType' => 'short'
);
$activeCurrency = null;
?>

<div class=" bd-currency-3" data-responsive-menu="false" data-responsive-levels="">
    <div class=" bd-horizontalmenu-53 clearfix">
        <div class="bd-container-inner">
            <?php ob_start(); ?>
            <div class="bd-menu-111-popup">
    
    <ul class=" bd-menu-111">
        <?php foreach($currencies as $currency):?>
            <?php if ($currency->virtuemart_currency_id == $virtuemart_currency_id) : ?>
                <?php $activeCurrency = $currency; ?>
            <?php endif; ?>
            <li class=" bd-menuitem-111">
    <?php 
        $uri = JFactory::getURI();
        $uri->setVar('virtuemart_currency_id', $currency->virtuemart_currency_id);
        $url = $uri->toString(array('path', 'query', 'fragment'));
    ?>
   <a href="<?php echo $url; ?>" class="<?php if ($currency->virtuemart_currency_id == $virtuemart_currency_id) : ?> active<?php endif; ?>">
       <span><?php echo $currency->currency_txt;?></span>
   </a>
</li>
        <?php endforeach; ?>
    </ul>
    
</div>
            <?php $submenu = ob_get_clean(); ?>
            <ul class=" bd-menu-110 nav nav-pills navbar-left">
    <li class=" bd-menuitem-110">
    <a class="dropdown-toggle" >
        <span>
            <?php
                reset($currencies);
                $firtsCurrency = current($currencies);
                $parts = explode(' ', $activeCurrency ? $activeCurrency->currency_txt : $firtsCurrency->currency_txt);
                $symbol = array_pop($parts);
                $name = implode(' ', $parts);
            ?>
            <?php if ($settings['showLabel']): ?>Currency: <?php endif ?>
            <?php if ($settings['textType'] === 'noText' || $settings['textType'] === 'short') : ?>
                <?php echo $symbol; ?>
            <?php else : ?>
                <?php echo $name; ?>
            <?php endif; ?>
        </span>
        <?php if ($settings['showArrow']): ?><span class="caret"></span><?php endif ?>
    </a>
    <?php echo $submenu; ?>
</li>
</ul>
        </div>
    </div>
</div>
    <!--input class="button" type="submit" name="submit" value="<?php echo JText::_('MOD_VIRTUEMART_CURRENCIES_CHANGE_CURRENCIES') ?>" /-->
    <input id="virtuemart_currency_id" type="hidden" value="<?php echo $virtuemart_currency_id; ?>">
</form>