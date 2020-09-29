SqueezeBox.extend({
    applyContent: function(content, size) {
        if (!this.isOpen && !this.applyTimer) return;
        this.applyTimer = clearTimeout(this.applyTimer);
        this.hideContent();
        if (!content) {
            this.toggleLoading(true);
        } else {
            if (this.isLoading) this.toggleLoading(false);
            this.fireEvent('onUpdate', [this.content], 20);
        }
        if (content) {
            if (['string', 'array'].contains(typeOf(content))) {
                this.content.set('html', content);
            } else if (!(content !== this.content && this.content.contains(content))) {
                this.content.adopt(content);
            }
        }
        this.callChain();
        if (!this.isOpen) {
            this.toggleListeners(true);
            this.resize(size, true);
            this.isOpen = true;
            this.win.setProperty('aria-hidden', 'false');
            this.fireEvent('onOpen', [this.content]);
        } else {
            this.resize(size);
        }
    }
});

jQuery(function ($) {
    'use strict';

    var button = $('button.modal'),
        dataFolder = $('#dataFolder').val(),
        editorInstalled = $('#editorIsInstalled').val(),
        id = $('#themeId').val(),
        replace = false,
        updatePluginSettings = false,
        importMenus = true;

    function log(msg, color) {
        SqueezeBox.close();
        $('#log').append($('<div></div>').text(msg).css('color', color));
    }

    function requestParams(action) {
        var data = { 'action' : action };
        if ('' !== id) {
            data.id = id;
        }
        data.replace = replace ? '1' : '0';
        data.updatePluginSettings = updatePluginSettings ? '1' : '0';
        data.importMenus = importMenus ? '1' : '0';
        return data;
    }

    function request(data, callback) {
        $.ajax({
            url: dataFolder + '/install.php',
            data : data,
            dataType : 'text',
            success : function (data) {
                if (data.match(/^result:/)) {
                    callback(data.substring('result:'.length));
                } else if (data.match(/^error:/)) {
                    log(data.split(':').pop());
                } else {
                    log(data);
                }
            },
            error : function (xhr, textStatus, errorThrown) {
                log('Request failed: ' + xhr.status, 'red');
            }
        });
    }

    function check(callback) {
        request(requestParams('check'), function (data) {
            callback('1' === data);
        });
    }

    function editorIsInstalled(callback)
    {
        if ('1' === editorInstalled) {
            callback();
        } else {
            SqueezeBox.fromElement(dataFolder + '/error.html', {
                size: { x : 420, y : 140 },
                iframePreload: true,
                handler: 'iframe',
                onOpen: function (container) {
                    var ifrDoc = container.firstChild.contentDocument;
                    $('#ok', ifrDoc).bind('click', function () {
                        SqueezeBox.close();
                    });
                }
            });
        }
    }

    function run() {
        request(requestParams('run'), function (data) {
            if (data && data !== 'ok') {
                var parameters = $.parseJSON(data);
                $.each(parameters, function (index, value) {
                    $('#' + index).val(value);
                });
            }
            SqueezeBox.fromElement(dataFolder + '/success.html', {
                size: { x : 290, y : 100 },
                onUpdate: function (container) {
                    $('#continue', container.firstChild.contentDocument).bind('click', function () {
                        SqueezeBox.close();
                    });
                }
            });
        });
    }

    button.bind('click', function (event) {
        event.preventDefault();
        // Clear log container
        $('#log').html('');
        editorIsInstalled(function() {
            check(function (installed) {
                var dialogWidth = 455,
                    dialogHeight = 245;
                SqueezeBox.fromElement(dataFolder + '/warning.html', {
                    size: {x: dialogWidth, y: dialogHeight},
                    iframePreload: true,
                    handler: 'iframe',
                    onOpen: function (container, showContent) {
                        var ifrDoc = container.firstChild.contentDocument,
                            loadBtn = $('#load', ifrDoc);
                        $('#replace', ifrDoc).off().css('display', !installed ? 'none' : '').bind('click', function () {
                            $(this).attr('disabled', 'disabled');
                            replace = true;
                            run();
                        });
                        loadBtn.off().bind('click', function () {
                            $(this).attr('disabled', 'disabled');
                            replace = false;
                            run();
                        });

                        if (installed) {
                            loadBtn.text(loadBtn.data('new'));
                            loadBtn.css('width', loadBtn.data('width') + 'px');
                        }

                        $('#updatePluginSettings', ifrDoc).bind('click', function () {
                            updatePluginSettings = this.checked;
                        });
                        $('#importMenus', ifrDoc).bind('click', function () {
                            importMenus = this.checked;
                        });
                        $('#cancel', ifrDoc).bind('click', function () {
                            SqueezeBox.close();
                        });
                        container.setStyle('display', showContent ? '' : 'none');
                    }
                });
                window.setTimeout(function () {
                    SqueezeBox.fireEvent('onOpen', [SqueezeBox.content, true]);
                }, 1000);
            });
        })
    });
});