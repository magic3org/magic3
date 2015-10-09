<?php

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'functions.php';

JHtml::_('stylesheet', 'mod_languages/template.css', array(), true);
?>
<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown', 1)) : ?>
    <?php
$settings = array(
    'showLabel' => false,
    'showArrow' => true,
    'textType' => 'short'
);
$activeLang = null;
?>
<form name="lang" method="post" action="<?php echo JURI::current(); ?>">
    <div class="data-control-id-238921 bd-horizontalmenu-1 clearfix">
        <?php ob_start(); ?>
        <div class="bd-menu-2-popup">
    
    <ul class="data-control-id-238941 bd-menu-2">
        <?php foreach($list as $language):?>
            <?php if($language->active):?>
                <?php $activeLang = $language; ?>
            <?php endif; ?>
            <li dir=<?php echo JLanguage::getInstance($language->lang_code)->isRTL() ? '"rtl"' : '"ltr"'?> class="data-control-id-238942 bd-menuitem-2">
        <a class="<?php if ($language->active) : ?> active<?php endif; ?>" href="<?php echo $language->link;?>">
            <span><?php echo $language->title_native;?></span>
        </a>
    </li>
        <?php endforeach; ?>
    </ul>
    
</div>
        <?php $submenu = ob_get_clean(); ?>
        <ul class="data-control-id-238922 bd-menu-1 nav nav-pills navbar-left">
    <li class="data-control-id-238923 bd-menuitem-1">
    <a class="dropdown-toggle" >
        <span>
            <?php if ($settings['showLabel']): ?>Language: <?php endif ?>
            <?php if (!is_null($activeLang)) : ?>
                <?php if ($settings['textType'] === 'noText') : ?>
                    <?php echo JHtml::_('image', 'mod_languages/'.$activeLang->image.'.gif', $activeLang->title_native, array('title'=>$activeLang->title_native), true);?>
                <?php else : ?>
                    <?php echo $settings['textType'] === 'short' ? strtoupper($activeLang->sef) : $activeLang->title_native;?>
                <?php endif; ?>
            <?php endif; ?>
        </span>
        <?php if ($settings['showArrow']): ?><span class="caret"></span><?php endif ?>
    </a>
    <?php echo $submenu; ?>
</li>
</ul>
    </div>
</form>
<?php else : ?>
	<ul class="<?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block';?>">
	<?php foreach($list as $language):?>
		<?php if ($params->get('show_active', 0) || !$language->active):?>
			<li class="<?php echo $language->active ? 'lang-active' : '';?>" dir="<?php echo JLanguage::getInstance($language->lang_code)->isRTL() ? 'rtl' : 'ltr' ?>">
			<a href="<?php echo $language->link;?>">
			<?php if ($params->get('image', 1)):?>
				<?php echo JHtml::_('image', 'mod_languages/'.$language->image.'.gif', $language->title_native, array('title'=>$language->title_native), true);?>
			<?php else : ?>
				<?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
			<?php endif; ?>
			</a>
			</li>
		<?php endif;?>
	<?php endforeach;?>
	</ul>
<?php endif; ?>

<?php if ($footerText) : ?>
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>