<?php

defined('JPATH_PLATFORM') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.form.formfield');

class JFormFieldImport extends JFormField
{
    protected $type = 'Import';
    protected $pluginName = 'nicepage';

    protected function getInput()
    {
        JHtml::_ ( 'behavior.modal' ); // for SqueezeBox

        $text   = $this->element['text'] ? $this->element['text'] : '';

        $id     = JRequest::getInt('id');
        $table = JTable::getInstance('Style', 'TemplatesTable');

        $table->load($id);
        $themeName = $table->template;
        $dataFolder = JURI::root(true).'/templates/'. $themeName .'/content';

        $editorName = $this->pluginName;
        $editorIsInstalled = $this->_npInstalled() ? '1' : '0';
        ob_start();
        ?>
        <script>if ('undefined' != typeof jQuery) document._jQuery = jQuery;</script>
        <script src="<?php echo JURI::root() . 'templates/' . $themeName . '/scripts/jquery.js' ?>" type="text/javascript"></script>
        <script>jQuery.noConflict();</script>
        <script src="<?php echo JURI::root() . 'templates/' . $themeName . '/content/loader.js' ?>" type="text/javascript"></script>
        <script>if (document._jQuery) jQuery = document._jQuery;</script>
        <button class="modal btn" type="submit" name="<?php echo $this->name; ?>" id="<?php echo $this->id; ?>">
            <?php echo JText::_($text); ?>
        </button>
        <input type="hidden" id="dataFolder" value="<?php echo $dataFolder; ?>">
        <input type="hidden" id="editorIsInstalled" value="<?php echo $editorIsInstalled; ?>">
        <input type="hidden" id="themeId" value="<?php echo $id; ?>">
        <div id="log" style="float:left;width:100%;margin-left:150px"></div>
        <?php
        return ob_get_clean();
    }

    private function _npInstalled() {
        if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_' . $this->pluginName)) {
            return false;
        }
        if (!JComponentHelper::getComponent('com_' . $this->pluginName, true)->enabled) {
            return false;
        }

        return true;
    }

}