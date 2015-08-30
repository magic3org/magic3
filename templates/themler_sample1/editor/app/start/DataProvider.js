/* exported DataProvider */
/* global SessionTimeoutError, DataProviderHelper, ErrorUtility, ServerPermissionError, $ */

var DataProvider = (function () {
    'use strict';

    var context   = window,
        config    = context.config,
        provider  = {};

    provider.validateResponse = function validateResponse(xhr) {
        var error = DataProviderHelper.validateRequest(xhr);
        if (!error) {
            var response = xhr.responseText;
            if (typeof response === 'string' && '' !== response) {
                try {
                    var result = JSON.parse(response);
                    if (result.error === 'sessions') {
                        error = new SessionTimeoutError();
                        error.loginUrl = window.location.href;
                    } else if (result.error === 'permissions') {
                        error = new ServerPermissionError(result.message);
                    } else if (result.status === 'error' && result.message) {
                        error = new Error(result.message);
                    }
                } catch (e) {
                }
            }
        }
        return error;
    };

    function ajaxFailHandler(url, xhr, status, callback) {
        var error = DataProvider.validateResponse(xhr);
        if (!error) {
            error = ErrorUtility.createRequestError(url, xhr, status, 'Request failed');
        }
        callback(error);
    }

    provider.reloadTemplatesInfo = function reloadTemplatesInfo(callback) {
        var url = config.index + '?action=getTemplates';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                template: config.templateName,
                frontend: true
            },
            success: function reloadTemplatesInfoSuccess(data, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    $.each(data, function(key, value) {
                        config.infoData[key] = value;
                    });
                }
                callback(error);
            },
            error: function reloadTemplatesInfoFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.reloadThemesInfo = function reloadThemesInfo(callback) {
        var url = config.index + '?action=getThemes';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                template: config.templateName
            },
            success: function reloadThemesInfoSuccess(data, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    config.infoData.themes = data;
                }
                callback(error, JSON.stringify(config.infoData));
            },
            error: function reloadThemesInfoFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.backToAdmin = function backToAdmin() {
        var currentUrl = context.location.href,
            index = currentUrl.lastIndexOf('&editor=1'),
            url = currentUrl.substr(0, index);
        context.location.replace(url);
    };

    provider.getMaxRequestSize = function getMaxRequestSize() {
        return config.infoData.maxRequestSize;
    };

    provider.doExport = function doExport(data, callback) {
        var request = {
            'save': {
                'post': {
                    data: JSON.stringify(data),
                    template : config.templateName
                },
                'url': config.index + '?action=doExport'
            },
            'clear': {
                'post': {},
                'url': config.index + '?action=clearChunks'
            },
            'errorHandler': DataProvider.validateResponse,
            'encode': true,
            'blob': true
        };
        DataProviderHelper.chunkedRequest(request, callback);
    };

    provider.save = function save(saveData, callback) {
        var request = {
            'save': {
                'post': {
                    data: JSON.stringify(saveData),
                    template : config.templateName
                },
                'url': config.index + '?action=saveProject'
            },
            'clear': {
                'post': {},
                'url': config.index + '?action=clearChunks'
            },
            'errorHandler': DataProvider.validateResponse,
            'encode': true,
            'blob': true
        };
        DataProviderHelper.chunkedRequest(request, callback);
    };

    provider.updatePreviewTheme = function updatePreviewTheme(callback) {
        callback();
    };

    provider.getTheme = function getTheme(options, callback) {
        var url = config.index + '?action=getTheme';
        $.ajax({
            type: "get",
            url: url,
            dataType: "text",
            data: {
                template: config.templateName,
                resultType: 'content',
                themeName: options.themeName || config.infoData.themeName,
                includeEditor: options.includeEditor
            },
            success: function getThemeSuccess(data, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    callback(null, data);
                } else {
                    callback(error);
                }
            },
            error: function getThemeFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };
    provider.themeArchiveExt = 'zip';

    provider.canRename = function canRename(themeName, callback) {
        var url = config.index + '?action=canRename';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                themeName: themeName
            },
            success: function canRenameSuccess(can, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    callback(null, can);
                } else {
                    callback(error);
                }
            },
            error: function canRenameFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.rename = function rename(themeName, callback) {
        var url = config.index + '?action=renameTheme';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                oldThemeName: config.infoData.themeName,
                newThemeName: themeName || config.infoData.themeName
            },
            success: function renameSuccess(response, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    var href = context.location.href,
                        name = config.infoData.themeName,
                        regExp = new RegExp('theme=' + name);
                    if (href.search(regExp) === -1) {
                        href = href.replace('editor=1', 'editor=1&theme=' +  themeName);
                    } else {
                        href = href.replace(regExp, 'theme=' + themeName);
                    }
                    callback(null, href);
                } else {
                    callback(error);
                }
            },
            error: function renameFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.getFiles = function getFiles(mask, filter, callback) {
        var url = config.index + '?action=getFiles';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                mask: mask || '*',
                filter: filter || '',
                template: config.templateName
            },
            success: function getFilesSuccess(response, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    callback(null, response.files);
                } else {
                    callback(error);
                }
            },
            error: function getFilesFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.setFiles = function setFiles(files, callback) {
        var request = {
            'save': {
                'post': {
                    data: JSON.stringify(files),
                    template : config.templateName
                },
                'url': config.index + '?action=setFiles'
            },
            'clear': {
                'post': {},
                'url': config.index + '?action=clearChunks'
            },
            'errorHandler': DataProvider.validateResponse,
            'encode': true,
            'blob': true
        };
        DataProviderHelper.chunkedRequest(request, callback);
    };

    provider.load = function () {
        return JSON.parse(context.atob(config.projectData)) || {};
    };

    provider.getAllCssJsSources = function () {
        return config.cssJsSources;
    };

    provider.getMd5Hashes = function () {
        return config.md5Hashes;
    };

    provider.getThemeVersion   = function () {
        return config.revision;
    };

    provider.makeThemeAsActive = function makeThemeAsActive(callback, id) {
        var url = config.index + '?action=makeThemeAsActive';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                themeId: id || ''
            },
            success: function themeActiveSuccess(response, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                callback(error);
            },
            error: function themeActiveFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.renameTheme = function renameTheme(themeName, newName, callback) {
        var url = config.index + '?action=renameTheme';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                oldThemeName: themeName,
                newThemeName: newName
            },
            success: function renameSuccess(response, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    var href = context.location.href,
                        name = config.infoData.themeName,
                        regExp = new RegExp('theme=' + name);
                    if (name.search(regExp) === -1) {
                        href = href.replace('editor=1', 'editor=1&theme=' +  themeName);
                    } else {
                        href = href.replace(regExp, 'theme=' + newName);
                    }
                    callback(null, config.infoData.themeName === themeName ? href : null);
                } else {
                    callback(error);
                }
            },
            error: function renameFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.removeTheme = function removeTheme(id, callback) {
        var url = config.index + '?action=removeTheme';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                templateId: id
            },
            success: function removeSuccess(response, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    callback(null);
                } else {
                    callback(error);
                }
            },
            error: function removeFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.downloadTheme = function downloadTheme(id, callback) {
        var url = config.index + '?action=downloadTheme';
        $.ajax({
            type: "get",
            url: url,
            dataType: "text",
            data: {
                templateId: id
            },
            success: function downloadSuccess(data, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    callback(null, data);
                } else {
                    callback(error);
                }
            },
            error: function downloadFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.copyTheme = function copyTheme(id, newName, callback) {
        var url = config.index + '?action=copyTheme';
        $.ajax({
            type: "post",
            url: url,
            dataType: "json",
            data: {
                templateId: id,
                newThemeName: newName || ''
            },
            success: function copySuccess(response, status, xhr) {
                var error = DataProvider.validateResponse(xhr);
                if (!error) {
                    callback(null);
                } else {
                    callback(error);
                }
            },
            error: function copyFail(xhr, status) {
                ajaxFailHandler(url, xhr, status, callback);
            }
        });
    };

    provider.getInfo = function() {
        var info = {
            cmsName : 'Joomla',
            cmsVersion : config.infoData.cmsVersion,
            adminPage: config.infoData.adminPage,
            startPage: config.infoData.startPage,
            templates: config.infoData.templates,
            canDuplicateTemplatesConstructors : config.infoData.canDuplicateTemplatesConstructors,
            thumbnails : [
                { name: 'template_preview.png', width: 800, height: 600 },
                { name: 'template_thumbnail.png', width: 206, height: 150 }
            ],
            themeName : config.templateName,
            isThemeActive : config.infoData.isThemeActive,
            uploadImage : config.index + '?action=uploadImage&template=' + config.templateName,
            uploadTheme : config.index + '?action=uploadTheme&template=' + config.templateName,
            themes : $.extend({}, config.infoData.themes),
            pathToManifest : '/app/themler.manifest'
        };
        if (typeof(config.infoData.contentIsImported) !== 'undefined' && false === config.infoData.contentIsImported) {
            info.importContent = config.index + '?action=importContent&id=' + config.styleId + '&template=' + config.templateName;
            info.replaceContent = config.index + '?action=importContent&id=' + config.styleId + '&template=' + config.templateName;
        }
        return info;
    };

    provider.getVersion = function () {
        return "0.0.2";
    };

    return provider;
}());