<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.popover');
?>
<style type="text/css">
    .config-container {
        margin: 10px 0 0 0;
        border:1px solid #e3e3e3;
        padding: 20px 0 0 20px
    }
</style>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <div class="config-container">
        <form action="" class="form-validate form-horizontal" method="post" id="npconfig">
            <div class="control-group">
                <div class="control-label">
                    <label for="checkbox-field"><?php echo JText::_('COM_NICEPAGE_CONFIG_JQUERY_LABEL'); ?></label>
                </div>
                <div class="controls">
                    <input type="checkbox" name="enable_jquery" id="enable_jquery" <?php echo $this->jQuery; ?>>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <label for="checkbox-field" class="hasPopover" data-content="<?php echo JText::_('COM_NICEPAGE_CONFIG_AUTO_RESPONSIVE_DESC'); ?>" data-original-title="<?php echo JText::_('COM_NICEPAGE_CONFIG_AUTO_RESPONSIVE_LABEL'); ?>"><?php echo JText::_('COM_NICEPAGE_CONFIG_AUTO_RESPONSIVE_LABEL'); ?></label>
                </div>
                <div class="controls">
                    <input type="checkbox" name="auto_responsive" id="auto_responsive" <?php echo $this->autoResponsive; ?>>
                </div>
            </div>
            <?php if ($this->templateOptions) : ?>
                <div class="control-group">
                    <div class="control-label">
                        <label for="checkbox-field" class="hasPopover" data-content="<?php echo JText::_('COM_NICEPAGE_CONFIG_TEMPLATE_OPTIONS_LABEL'); ?>" data-original-title="<?php echo JText::_('COM_NICEPAGE_CONFIG_TEMPLATE_OPTIONS_LABEL'); ?>"><?php echo JText::_('COM_NICEPAGE_CONFIG_TEMPLATE_OPTIONS_DESC'); ?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->templateOptions; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($this->disableAutoSaveTemplate) : ?>
                <div class="control-group">
                    <div class="control-label">
                        <label for="checkbox-field" class="hasPopover" data-content="<?php echo JText::_('COM_NICEPAGE_CONFIG_AUTOSAVE_DESC'); ?>" data-original-title="<?php echo JText::_('COM_NICEPAGE_CONFIG_AUTOSAVE_LABEL'); ?>"><?php echo JText::_('COM_NICEPAGE_CONFIG_AUTOSAVE_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->disableAutoSaveTemplate; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="control-group">
                <div class="control-label">
                </div>
                <div class="controls">
                    <button class="btn btn-success" type="submit">
                        <span class="icon-apply icon-white" aria-hidden="true"></span>
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(function($) {
        $('#npconfig').on('submit', function(e) {
            e.preventDefault();
            $('.btn-success').attr('disabled', true);
            var jQueryStatus = $('input[id="enable_jquery"]').get(0).checked ? '1' : '0',
                autoResponsive = $('input[id="auto_responsive"]').get(0).checked ? '1' : '0',
                pageType = $('select[id="nicepage-select-template"]').val();
            autoSave = $('select[id="nicepage-select-autosave"]').val();
            $.ajax({
                url: '<?php echo $this->adminUrl . '/index.php?option=com_nicepage&task=actions.saveConfig'; ?>',
                data: {
                    jquery : jQueryStatus,
                    autoResponsive : autoResponsive,
                    pageType: pageType,
                    disableAutosave: autoSave
                },
                type: 'POST',
                success: function (response) {
                    console.log(response);
                    $('.btn-success').attr('disabled', false);
                },
                error: function (xhr, status) {
                    alert('Response failed');
                }
            });
        });
    });
</script>
