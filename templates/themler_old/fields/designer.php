<?php

defined('JPATH_PLATFORM') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.form.formfield');

class JFormFieldDesigner extends JFormField
{
    protected $type = 'Designer';

    protected function getInput()
    {
        $content = '<div style="float:left">' . JText::_('TPL_CONFIGURATOR_NOT_FOUND') . '</div>';

        $table = JTable::getInstance('Style', 'TemplatesTable');
        $table->load(JRequest::getInt('id'));

        $configFile = JPATH_SITE . '/templates/' . $table->template . '/app/classes/' . 'Config.php';

        if (file_exists($configFile)) {
            include_once($configFile);
            $content = Config::injectionDesigner(array(
                'parameterObject' => $this,
                'themeObject' => $table));
        }
        return $content;
    }
}