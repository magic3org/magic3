<?php

defined('JPATH_PLATFORM') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.form.formfield');

class JFormFieldSliders extends JFormField
{
    protected $type = 'Sliders';

    protected function getInput()
    {
        $table = JTable::getInstance('Style', 'TemplatesTable');
        $table->load(JRequest::getInt('id'));

        $themeDir = JPATH_SITE . '/templates/' . $table->template;

        jimport('joomla.application.module.helper');
        include_once $themeDir . '/library/Designer/CustomModuleHelper.php';

        $options = array(JHtml::_('select.option', '-1', 'Virtuemart frontpage'));
        $pathToManifest = $themeDir . '/templateDetails.xml';
        if (file_exists($pathToManifest)) {
            // instantiate the frontend application.
            JFactory::$application = JApplication::getInstance('site');
            $slidersModules = array();
            $xml = simplexml_load_file($pathToManifest);
            if (isset($xml->positions[0])) {
                foreach ($xml->positions[0] as $position) {
                    jimport('joomla.application.module.helper');
                    $modules = CustomModuleHelper::getModules($position);
                    foreach ($modules as $mod) {
                        if('mod_virtuemart_product' == $mod->module) {
                            $slidersModules[] = $mod;
                        }
                    }
                }
            }
            foreach($slidersModules as $module) {
                $options[] = JHtml::_('select.option', $module->id, $module->title . ' - ' . $module->position . '');
            }
            // instantiate the frontend application.
            CustomModuleHelper::clean();
            JFactory::$application = JApplication::getInstance('administrator');
        }
        $html = JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value, $this->id);
        ob_start();
        echo $html;
        ?>
        <script>if ('undefined' != typeof jQuery) document._jQuery = jQuery;</script>
        <script src="<?php echo JURI::root() . 'templates/' . $table->template . '/jquery.js' ?>" type="text/javascript"></script>
        <script>jQuery.noConflict();</script>
        <script>
            jQuery(function ($) {
                var modulesObj = $('#<?php echo $this->id; ?>'),
                    slidersOptions = $('#jform_params_slidersOptions'),
                    desktops = $('#jform_params_desktops'),
                    laptops = $('#jform_params_laptops'),
                    tablets = $('#jform_params_tablets'),
                    phones = $('#jform_params_phones'),
                    list = [modulesObj, desktops, laptops, tablets, phones],
                    storage = {};

                $.each(list, function(index, object) {
                    object.show();
                    $('#' + object.attr('id') + '_chzn').hide();
                });

                function toObject(str) {
                    return JSON.parse(atob(str));
                }

                function toString(obj) {
                    return btoa(JSON.stringify(obj));
                }

                function save(storage) {
                    slidersOptions.val(toString(storage));
                }

                if (slidersOptions.val()) {
                    storage = toObject(slidersOptions.val());
                }

                modulesObj.change(function () {
                    var value = $(this).val(),
                        defaults = { desktops : '', laptops : '', tablets : '12', phones : ''},
                        options = $.extend({}, defaults, storage[value]);
                    if (options) {
                        desktops.val(options.desktops);
                        laptops.val(options.laptops);
                        tablets.val(options.tablets);
                        phones.val(options.phones);
                    }
                    storage[value] = options;
                    save(storage);
                });

                desktops.change(function () {
                    storage[modulesObj.val()].desktops = $(this).val();
                    save(storage);
                });
                laptops.change(function () {
                    storage[modulesObj.val()].laptops = $(this).val();
                    save(storage);
                });
                tablets.change(function () {
                    storage[modulesObj.val()].tablets = $(this).val();
                    save(storage);
                });
                phones.change(function () {
                    storage[modulesObj.val()].phones = $(this).val();
                    save(storage);
                });
                modulesObj.change();
            });
        </script>
        <script>if (document._jQuery) jQuery = document._jQuery;</script>
        <?php
        return ob_get_clean();
    }
}