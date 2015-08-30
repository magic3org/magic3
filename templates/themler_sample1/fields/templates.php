<?php

defined('JPATH_PLATFORM') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.form.formfield');

class JFormFieldTemplates extends JFormField
{
    protected $type = 'Templates';

    protected function getInput()
    {
        $table = JTable::getInstance('Style', 'TemplatesTable');
        $table->load(JRequest::getInt('id'));

        $themeDir = JPATH_SITE . '/templates/' . $table->template;
        include_once $themeDir . '/library/Designer/CustomModuleHelper.php';

        $kind = $this->getAttribute('kind');
        $templatesInfo = array();
        include $themeDir . '/templates/' . 'list.php';
        $options = array();
        foreach($templatesInfo as $templateInfo) {
            if ($templateInfo['kind'] == $kind) {
                if ('false' == $templateInfo['isCustom'])
                    array_unshift($options, JHtml::_('select.option', $templateInfo['fileName'],
                        $templateInfo['defaultTemplateCaption']));
                else
                    $options[] = JHtml::_('select.option', $templateInfo['fileName'],
                        $templateInfo['caption']);
            }
        }
        $html = JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text',
            ($this->value ? $this->value : null), $this->id);
        ob_start();
        echo $html;
        return ob_get_clean();
    }

    public function getAttribute($name, $default = '')
    {
        if ($this->element instanceof SimpleXMLElement)
        {
            $attributes = $this->element->attributes();

            // Ensure that the attribute exists
            if (property_exists($attributes, $name))
            {
                $value = $attributes->$name;

                if ($value !== null)
                {
                    return (string) $value;
                }
            }
        }

        return $default;
    }
}