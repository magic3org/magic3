<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

?>

<style type="text/css">
    .import-container {
        margin: 10px 0 0 0;
        border:1px solid #e3e3e3;
        padding: 20px 0 50px 20px
    }

    #file-list {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    #file-list li {
        margin: 10px;
        width: 152px;
        text-align: center;
        display: inline-block;
    }

    #file-list div.progress {
        width: 150px;
        border: 1px solid #888888;
        height: 18px;
        text-align: center;
        background-image: url('<?php echo $this->adminUrl . '/components/com_nicepage/assets/images/progress.png'; ?>');
        background-repeat: no-repeat;
        background-position: -150px center;
    }

    #log {
        color: #0667FF;
        font-size: 14px;
    }

    .import-group {

    }

    .import-control {
        float: left;
        margin-right: 10px;
        height: 40px;
    }

    input[type="checkbox"] {
        margin-top: 7px;
    }

    .import-label {
        width: auto;
        text-align: left;
        padding-right: 5px;
        padding-top: 5px;
    }

    .clearfix:after {
        content: "";
        display: table;
        clear: both;
    }
</style>
<script>
    jQuery(function($) {

        var fileInput = $('#file-field');
        var fileList = $('ul#file-list');
        var uploadBtn = $("#upload-all");
        var replaceChbx = $('input[id="replacecontent"]');
        var updatePluginSettingsChbx = $('input[id="updatePluginSettings"]');
        var previousContentData = $('input[id*="idsData"]');

        var replaceStatus = replaceChbx[0].checked ? '1' : '0';
        replaceChbx.click(function() {
            replaceStatus = this.checked ? '1' : '0';
        });

        var updatePluginSettings = updatePluginSettingsChbx[0].checked ? '1' : '0';
        updatePluginSettingsChbx.click(function() {
            updatePluginSettings = this.checked ? '1' : '0';
        });

        function log(msg, color) {
            $('#log').append($('<div></div>').text(msg).css('color', color));
        }

        function ChunkedUploader(file, params) {
            var _file = file;
            if (_file instanceof Uint8Array) {
                _file = new Blob([_file]);
            }
            var maxChunkLength = 1024 * 1024; // 1 Mb
            var CHUNK_SIZE = parseInt(<?php echo $this->maxRequestSize;?> || maxChunkLength, 10);
            var uploadedChunkNumber = 0, allChunks;
            var fileName = (_file.name || window.createGuid()).replace(/[^A-Za-z0-9\._]/g, '');
            var fileSize = _file.size || _file.length;
            var total = Math.ceil(fileSize / CHUNK_SIZE);

            var rangeStart = 0;
            var rangeEnd = CHUNK_SIZE;
            validateRange();

            var sliceMethod;

            if ('mozSlice' in _file) {
                sliceMethod = 'mozSlice';
            }
            else if ('webkitSlice' in _file) {
                sliceMethod = 'webkitSlice';
            }
            else {
                sliceMethod = 'slice';
            }

            this.upload = upload;

            function upload() {
                var data;

                setTimeout(function () {
                    var requests = [];

                    for (var chunk = 0; chunk < total - 1; chunk++) {
                        data = _file[sliceMethod](rangeStart, rangeEnd);
                        requests.push(createChunk(data));
                        incrementRange();
                    }

                    allChunks = requests.length;

                    $.when.apply($, requests).then(
                        function success() {
                            var lastChunkData = _file[sliceMethod](rangeStart, rangeEnd);

                            createChunk(lastChunkData, {last: true})
                                .done(onUploadCompleted)
                                .fail(onUploadFailed);
                        },
                        onUploadFailed
                    );
                }, 0);
            }

            function createChunk(data, params) {
                var formData = new FormData();
                formData.append('filename', fileName);
                formData.append('replaceStatus', replaceStatus);
                formData.append('updatePluginSettings', updatePluginSettings);
                formData.append('chunk', new Blob([data], { type: 'application/octet-stream' }), 'blob');

                if (typeof params === 'object') {
                    for (var i in params) {
                        if (params.hasOwnProperty(i)) {
                            formData.append(i, params[i]);
                        }
                    }
                }

                return $.ajax({
                    url: '<?php echo $this->adminUrl . "/index.php?option=com_nicepage&task=actions.importData"; ?>',
                    data: formData,
                    type: 'POST',
                    mimeType: 'application/octet-stream',
                    processData: false,
                    contentType: false,
                    headers: (rangeEnd <= fileSize) ? {
                        'Content-Range': ('bytes ' + rangeStart + '-' + rangeEnd + '/' + fileSize)
                    } : {},
                    success: onChunkCompleted,
                    error: function (xhr, status) {
                        alert('Failed  chunk');
                    }
                });
            }

            function validateRange() {
                if (rangeEnd > fileSize) {
                    rangeEnd = fileSize;
                }
            }

            function incrementRange() {
                rangeStart = rangeEnd;
                rangeEnd = rangeStart + CHUNK_SIZE;
                validateRange();
            }

            function onUploadCompleted(response, status, xhr) {
                previousContentData.val(response);
                params.complete();
            }

            function onUploadFailed(xhr, status) {
                alert('onUploadFailed');
            }

            function onChunkCompleted() {

                if (uploadedChunkNumber >= allChunks)
                    return;

                ++uploadedChunkNumber;
                params.progress(Math.round((100 * uploadedChunkNumber)/allChunks));
            }
        }

        function updateProgress(bar, value) {
            var width = bar.width();
            var bgrValue = -width + (value * (width / 100));
            bar.attr('rel', value).css('background-position', bgrValue+'px center').text(value+'%');
        }

        function displayFiles(files) {
            var imageType = /(zip).*/;
            var num = 0;

            $.each(files, function(i, file) {
                if (/\.zip$/.test(file.name) === false) {
                    alert('File is not allowed: "' + file.name + '" (type ' + file.type + ')');
                    fileInput.val('');
                    fileList.html('');
                    uploadBtn.attr('disabled', true);
                    return true;
                }
                num++;
                var li = $('<li/>');
                var img = $('<img/>').appendTo(li);
                $('<div/>').addClass('progress').attr('rel', '0').text('0%').appendTo(li);
                li.get(0).file = file;
                fileList.html(li);
                var thumbDataUrl = '<?php echo $this->adminUrl . '/components/com_nicepage/assets/images/zip.png'; ?>';
                img.attr('src', thumbDataUrl);
                img.attr('width', 150);
                $('#log').html('');
                uploadBtn.attr('disabled', false);
            });
        }

        fileInput.bind({
            change: function() {
                displayFiles(this.files);
            }
        });

        uploadBtn.click(function(e) {
            e.preventDefault();
            uploadBtn.attr('disabled', true);
            fileList.find('li').each(function() {

                var uploadItem = this;
                var pBar = $(uploadItem).find('.progress');
                var onprogress = function(percents) {
                        updateProgress(pBar, percents);
                    },
                    oncomplete = function() {
                        updateProgress(pBar, 100);
                        setTimeout(function(){
                            fileInput.val('');
                            fileList.html('');
                            uploadBtn.attr('disabled', true);
                            log('The data has bees successfully installed.');
                        }, 1000);

                    };

                var uploader = new ChunkedUploader(uploadItem.file, {'progress' : onprogress, 'complete' : oncomplete});

                uploader.upload();
            });
        });
    });
</script>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
    <div class="import-container">
        <div style="margin-bottom: 20px;">
            <?php echo JText::_('COM_NICEPAGE_IMPORT_DESC'); ?>
        </div>
        <div class="import-group clearfix">
            <div class="import-label">
                <label for="file-field"><?php echo JText::_('COM_NICEPAGE_IMPORT_LABEL'); ?></label>
            </div>
            <div class="import-control">
                <input type="file" name="file" id="file-field" multiple="true" />
                <button id="upload-all" disabled class="btn"><?php echo JText::_('COM_NICEPAGE_IMPORT_BUTTON_TEXT'); ?></button>
            </div>
        </div>
        <div class="import-group clearfix">
            <div class="import-control">
                <input type="checkbox" name="replacecontent" id="replacecontent">
            </div>
            <div class="import-label">
                <label for="checkbox-field"><?php echo JText::_('COM_NICEPAGE_IMPORT_REPLACE_LABEL'); ?></label>
            </div>
        </div>
        <div class="import-group clearfix">
            <div class="import-control">
                <input type="checkbox" name="updatePluginSettings" id="updatePluginSettings">
            </div>
            <div class="import-label">
                <label for="checkbox-field"><?php echo JText::_('COM_NICEPAGE_IMPORT_UPDATE_PLUGIN_SETTINGS'); ?>
                </label>
            </div>
        </div>
        <div id="log"></div>
        <ul id="file-list"></ul>
    </div>
</div>