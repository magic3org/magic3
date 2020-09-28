<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class NicepageViewImport
 */
class NicepageViewImport extends JViewLegacy
{
    /**
     * Render display html page
     *
     * @param null $tpl Template name
     */
    public function display($tpl = null)
    {
        $this->maxRequestSize = NicepageHelpersNicepage::getMaxRequestSize();
        $this->adminUrl = dirname(dirname((JURI::current()))) . '/administrator';
        JToolbarHelper::title(JText::_('COM_NICEPAGE_IMPORT_HEADER'));

        NicepageHelpersNicepage::addSubmenu('import');
        $this->sidebar = JHtmlSidebar::render();

        return parent::display($tpl);
    }
}