<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

JLoader::register('Nicepage_Editor', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/editor.php');
/**
 * Class NicepageViewTheme
 */
class NicepageViewTheme extends JViewLegacy
{
    /**
     * Render display html page
     *
     * @param null $tpl Template name
     */
    public function display($tpl = null)
    {
        $editor = new Nicepage_Editor();
        $editor->addCommonScript();
        $editor->addLinkDialogScript();
        $editor->addDataBridgeScript();
        $editor->addMainScript();
        $editor->includeScripts();

        return parent::display($tpl);
    }
}