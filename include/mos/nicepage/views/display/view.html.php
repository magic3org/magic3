<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class NicepageViewDisplay
 */
class NicepageViewDisplay extends JViewLegacy
{
    /**
     * Render display html page
     *
     * @param null $tpl Template name
     */
    public function display($tpl = null)
    {
        $this->startFiles = NicepageHelpersNicepage::getStartFiles();
        return parent::display($tpl);
    }
}