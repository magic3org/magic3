<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

/**
 * Class NicepageViewActions
 */
class NicepageViewActions extends JViewLegacy
{
    /**
     * @param null $tpl Template name
     * 
     * @return mixed
     */
    public function display($tpl = null)
    {
        $input = JFactory::getApplication()->input;
        $action = $input->get('task', '');
        if ($action) {
            $this->result = $this->getModel('Actions')->{$action}($input);
        }
        return parent::display($tpl);
    }
}