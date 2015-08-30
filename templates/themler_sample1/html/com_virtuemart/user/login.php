<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
    require_once $templateDir . '/app/' . 'Editor.php';
    $ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
    $editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>
<?php
//set variables, usually set by shopfunctionsf::getLoginForm in case this layout is differently used
if (!isset( $this->show )) $this->show = TRUE;
if (!isset( $this->from_cart )) $this->from_cart = FALSE;
if (!isset( $this->order )) $this->order = FALSE ;

if (empty($this->url)){
	$uri = JFactory::getURI();
	$url = $uri->toString(array('path', 'query', 'fragment'));
} else{
	$url = $this->url;
}

$user = JFactory::getUser();

if ($this->show and $user->id == 0  ) {
JHtml::_('behavior.formvalidation');
JHtml::_ ( 'behavior.modal' );

	//Extra login stuff, systems like openId and plugins HERE
    if (JPluginHelper::isEnabled('authentication', 'openid')) {
        $lang = JFactory::getLanguage();
        $lang->load('plg_authentication_openid', JPATH_ADMINISTRATOR);
        $langScript = '
//<![CDATA[
'.'var JLanguage = {};' .
                ' JLanguage.WHAT_IS_OPENID = \'' . vmText::_('WHAT_IS_OPENID') . '\';' .
                ' JLanguage.LOGIN_WITH_OPENID = \'' . vmText::_('LOGIN_WITH_OPENID') . '\';' .
                ' JLanguage.NORMAL_LOGIN = \'' . vmText::_('NORMAL_LOGIN') . '\';' .
                ' var comlogin = 1;
//]]>
                ';
        $document = JFactory::getDocument();
        $document->addScriptDeclaration($langScript);
        JHTML::_('script', 'openid.js');
    }

    $html = '';
    JPluginHelper::importPlugin('vmpayment');
    $dispatcher = JDispatcher::getInstance();
    $returnValues = $dispatcher->trigger('plgVmDisplayLogin', array($this, &$html, $this->from_cart));

    if (is_array($html)) {
		foreach ($html as $login) {
		    echo $login.'<br />';
		}
    }
    else {
		echo $html;
    }
?>
    <?php if ($this->order): ?>
    <div class="order-view">
        <h1><?php echo vmText::_('COM_VIRTUEMART_ORDER_ANONYMOUS') ?></h1>
        <form action="<?php echo JRoute::_( 'index.php', 1, $this->useSSL); ?>" method="post" name="com-login" >
            <div id="com-form-order-number">
                <label for="order_number"><?php echo vmText::_('COM_VIRTUEMART_ORDER_NUMBER') ?></label><br />
                <input type="text" id="order_number" name="order_number" class="inputbox" size="18" alt="order_number" />
            </div>
            <div id="com-form-order-pass">
                <label for="order_pass"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PASS') ?></label><br />
                <input type="text" id="order_pass" name="order_pass" class="inputbox" size="18" alt="order_pass" value="p_"/>
            </div>
            <div id="com-form-order-submit">
                <input type="submit" name="Submitbuton" class="button" value="<?php echo vmText::_('COM_VIRTUEMART_ORDER_BUTTON_VIEW') ?>" />
            </div>
            <div class="clr"></div>
            <input type="hidden" name="option" value="com_virtuemart" />
            <input type="hidden" name="view" value="orders" />
            <input type="hidden" name="layout" value="details" />
            <input type="hidden" name="return" value="" />
        </form>
    </div>
    <?php endif; ?>
    <form class="form-horizontal" id="com-form-login" action="<?php echo JRoute::_('index.php', $this->useXHTML, $this->useSSL); ?>" method="post" name="com-login" >

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-22">
                <?php if (!$this->from_cart ): ?>
                    <h2><?php echo vmText::_('COM_VIRTUEMART_ORDER_CONNECT_FORM'); ?></h2>
                <div class="clear"></div>
                <?php else: ?>
                    <p><?php echo vmText::_('COM_VIRTUEMART_ORDER_CONNECT_FORM'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group" id="com-form-login-username">
            <div class="col-sm-offset-2 col-sm-22">
                <div class="inputbox">
                    <input type="text" name="username" class="form-control" size="18" alt="<?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_USERNAME'); ?>" onblur="if(this.value=='') this.value='<?php echo addslashes(vmText::_('COM_VIRTUEMART_USERNAME')); ?>';" onfocus="if(this.value=='<?php echo addslashes(vmText::_('COM_VIRTUEMART_USERNAME')); ?>') this.value='';" />
                </div>
            </div>
        </div>

        <div class="form-group" id="com-form-login-password">
            <div class="col-sm-offset-2 col-sm-22">
                <div class="inputbox">
                    <input id="modlgn-passwd" type="password" name="password" class="form-control" size="18" alt="<?php echo vmText::_('COM_VIRTUEMART_PASSWORD'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_PASSWORD'); ?>" onblur="if(this.value=='') this.value='<?php echo addslashes(vmText::_('COM_VIRTUEMART_PASSWORD')); ?>';" onfocus="if(this.value=='<?php echo addslashes(vmText::_('COM_VIRTUEMART_PASSWORD')); ?>') this.value='';" />
                </div>
            </div>
        </div>
        <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
        <div class="form-group" id="com-form-login-remember">
            <div class="col-sm-offset-2 col-sm-22">
                <label for="remember">
                    <input type="checkbox" id="remember" name="remember" value="yes" alt="Remember Me" />
                    <?php echo $remember_me = vmText::_('JGLOBAL_REMEMBER_ME') ?>
                </label>
            </div>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-22">
                <div class="default">
                    <input type="submit" name="Submit" class="form-control" value="<?php echo vmText::_('COM_VIRTUEMART_LOGIN') ?>" />
                </div>
            </div>
        </div>
        <div class="clr"></div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-22">
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
                <?php echo vmText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_USERNAME'); ?></a>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-22">
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                <?php echo vmText::_('COM_VIRTUEMART_ORDER_FORGOT_YOUR_PASSWORD'); ?></a>
            </div>
        </div>

        <div class="clr"></div>


        <input type="hidden" name="task" value="user.login" />
        <input type="hidden" name="option" value="com_users" />
        <input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </form>

<?php } else if ( $user->id ) { ?>
   <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="login" id="form-login">
   <?php echo vmText::sprintf( 'COM_VIRTUEMART_HINAME', $user->name ); ?>
        <input type="submit" name="Submit" class="button" value="<?php echo vmText::_( 'COM_VIRTUEMART_BUTTON_LOGOUT'); ?>" />
            <input type="hidden" name="option" value="com_users" />
            <input type="hidden" name="task" value="user.logout" />
            <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="return" value="<?php echo base64_encode($url) ?>" />
    </form>
<?php } ?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>