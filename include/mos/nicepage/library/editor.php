<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

JLoader::register('NicepageModelActions', JPATH_ADMINISTRATOR . '/components/com_nicepage/models/actions.php');
/**
 * Class Nicepage_Editor
 */
class Nicepage_Editor
{
    private $_adminUrl = '';
    private $_domain = '';

    private $_scripts = '';

    private $_article = null;
    private $_sections = null;
    private $_isConvertRequired = null;

    private $_dataBridgeScripts = '';

    private $_editorPageTypes = array(
        'default' => 'theme-template',
        'landing' => 'np-template-header-footer-from-plugin',
        'landing_with_header_footer' => 'np-template-header-footer-from-theme'
    );

    /**
     * NicepageEditor constructor.
     */
    public function __construct()
    {
        $this->setAdminUrl();
        $this->setDomain();

        $aid = JFactory::getApplication()->input->get('id', '');
        $page = NicepageHelpersNicepage::getSectionsTable();
        if ($page->load(array('page_id' => $aid))) {
            NicepageHelpersNicepage::clearPreview($page);
            $this->_sections = $page;
        }
        $this->_componentConfig = NicepageHelpersNicepage::getConfig();
        $this->_article = JTable::getInstance("content");
        $this->_article->load($aid);
        $this->_isConvertRequired = !$this->_sections && ($this->_article->introtext . $this->_article->fulltext);
    }
    /**
     * Add common scripts
     */
    public function addCommonScript()
    {
        $domain = $this->getDomain();
        $aid = JFactory::getApplication()->input->get('id', '');
        $element = JFactory::getApplication()->input->get('element', '');
        $view = JFactory::getApplication()->input->get('view', '');

        // start nicepage from edit article page
        if ($this->_sections) {
            $parts = '/#/builder/1/page/' . $aid;
        } else if ($view === 'theme') {
            $parts = '/#/builder/1/theme' . ($element ? '/' . $element : '');
        } else {
            $parts = '/#/landing';
        }
        $currentUrl = $this->getAdminUrl() . '/index.php?option=com_nicepage&view=display&ver=' . urlencode('1600320769457')  . ($domain ? '&domain=' . $domain : '') . $parts;

        JHtml::_('behavior.modal'); // for SqueezeBox

        $this->_scripts .= <<<EOF
function runNicepage(autoStart)
{
    if (window.dataBridge) {
        (function($){
            var iframe = $('<iframe>', {
                    src: '$currentUrl',
                    id: 'editor-frame'
                }),
                nav = $('.navbar-fixed-top');
            
            if (!nav.length) {
                nav = $('nav');
            }
            
            if (!nav.length) {
                nav = $('div[id="nav"]');
            }
            
            nav.after(iframe);
            iframe.css('height', 'calc(100vh - ' + nav.height() + 'px)');
            iframe.css('width', '100%');
    
            $(document).scroll(function() {
                $(this).scrollTop(0);
            });
    
            $('body').addClass('editor');
        })(jQuery);
    } else {
        alert('Unable to start the Editor. Please contact the Support.');
        if (autoStart) {
            window.location.href = '{$this->getAdminUrl()}';
        }
    }
}

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

function sendRequest(data, callback) {
    var xhr = new XMLHttpRequest();

    function onError() {
        callback(new Error('Failed to send a request to ' + data.url + ' ' + JSON.stringify({
            responseText: xhr.responseText,
            readyState: xhr.readyState,
            status: xhr.status
        }, null, 4)));
    }

    xhr.onerror = onError;
    xhr.onload = function () {
        if (this.readyState === 4 && this.status === 200) {
            callback(null, this.response);
        } else {
            onError();
        }
    };
    xhr.open(data.method || 'GET', data.url);

    if (data.data) {
        var formData = new FormData();
        formData.append("pageType", data.data.pageType);
        formData.append("pageId", data.data.pageId);
        xhr.send(formData);
    } else {
        xhr.send();
    }
}

function postMessageListener(event) {
    if (event.origin !== location.origin) {
        return;
    }
    var data;
    try {
        data = JSON.parse(event.data);
    } 
	catch(e){
    }
    if (!data) {
        return;
    }
    if (data.action === 'close') {
        window.location.href = data.closeUrl;
    } else if (data.action === 'editLinkDialogOpen') {
    	window.dataForDialog = data.data;
        openEditLinkDialog(data.data);
    }
}

if (window.addEventListener) {
    window.addEventListener("message", postMessageListener);
} else {
    window.attachEvent("onmessage", postMessageListener); // IE8
}
EOF;
    }

    /**
     * Get allowed file extensions
     *
     * @return array
     */
    public function getAllowedExtensions() {
        $params = JComponentHelper::getParams('com_media');
        $exts = $params->get('upload_extensions', 'pdf');
        return explode(',', $exts);
    }

    /**
     * Get video files
     *
     * @return array
     */
    public function getVideoFiles() {
        $files = $this->getMediaFiles('mp4|ogg|ogv|webm');
        $result = array();
        foreach ($files as $file) {
            array_push($result, array ('fileName' => $file['title'], 'id'    => $file['title'], 'publicUrl' => $file['url']));
        }
        return $result;
    }

    /**
     * Get media library files without image files
     *
     * @param string $mask Extenetions mask
     *
     * @return array
     */
    public function getMediaFiles($mask = '') {
        $result = array();
        $params = JComponentHelper::getParams('com_media');
        if (!$mask) {
            $mask = $params->get('upload_extensions', 'pdf');
            $mask = preg_replace('/(bmp|gif|png|jpg|jpeg|ico|BMP|GIF|ICO|JPG|JPEG)\,/', '', $mask); // exlude all image files
        }
        $root = str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT);
        $filesPath = $root . '/' . $params->get('image_path', 'images');
        if (file_exists($filesPath)) {
            jimport('joomla.filesystem.folder');
            $extsParts = '\.' . implode('|\.', explode(',', $mask));
            $fileList = JFolder::files($filesPath, $extsParts, true, true);
            foreach ($fileList as $key => $file) {
                $fileName = basename($file);
                $path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($file));
                $fileLink = str_replace($root, dirname($this->getAdminUrl()), $path);
                array_push($result, array('url' => $fileLink, 'title' => $fileName));
            }
        }
        return $result;
    }

    /**
     * Add joomla link dialog script
     */
    public function addLinkDialogScript()
    {
        $mediaFiles = json_encode($this->getMediaFiles());
        $allowedExtensions = json_encode($this->getAllowedExtensions());
        $maxRequestSize = NicepageHelpersNicepage::getMaxRequestSize();
        $editLinkUrl = $this->getAdminUrl() . '/index.php?option=com_content&view=articles&layout=modal&tmpl=component';
        $uploadFileLink = $this->getAdminUrl() . '/index.php?option=com_nicepage&task=actions.uploadFile';
        $customUrlOptions = <<<HTML
<style>
.custom-url-options {
    width:100%;
}
.custom-url-options label{
    width: 55px;
    display: inline-block;
}
.custom-url-options input[type=text]{
    width: 350px;
}
.custom-url-options:after {
    content: "";
    clear: both;
    display: table;
}
.link-destination,
.target-option {
    margin-left: 70px;
}
.link-destination {
    margin-top: 4px;
}
.link-destination input,
.target-option input {
    margin-right: 10px;
    margin-top: 0px;
}
.link-destination .link-destination-label {
    width: auto;
    display: inline-block;
    vertical-align: top;
    margin-left: -80px;
    margin-top: 4px;
    width: 76px;
}
.link-destination ul {
    list-style-type: none;
    margin-left: 0px;
    margin-top: 4px;
    display: inline-block;
}

.link-destination label {
    width: 120px;
}

.list-container {
    background-color: #F5F5F5;
    border: 1px solid #BFBFBF;
    padding: 4px 6px 4px 10px;
    margin: 10px auto auto 0px;
    height: 300px;
    overflow: auto;
}
.anchors-list,  .files-list {
    
    list-style-type: none;
}
.anchors-list li, .files-list li {
    cursor: pointer;
}
.anchors-list li:hover, .files-list li:hover,
.anchors-list li.selected, .files-list li.selected {
    background-color: #e5f2ff;
}
.anchors-list li a, .files-list li a {
    color: #666;
}

#upload-btn {
    text-decoration: none;
}

a.disabled {
    pointer-events: none;
    color: #999999;
}

</style>
<div class="custom-url-options">
    <div style="float:left;width:90%">
        <div style="float:left;width:65%">
            <div class="caption-option"><label for="caption">{{caption}}</label><input type="text" name="caption" value="" /></div>
            
            <div class="url-option"><label for="url">{{url}}</label><input type="text" name="url" value="" /></div>
            <div class="target-option"><input type="checkbox" name="target" />{{target}}</div>
            
            <div class="phone-option"><label for="phone">{{phoneLink}}</label><input type="tel" name="phone" value="" /></div>
            
            <div class="email-option"><label for="phone">{{emailLink}}</label><input type="email" name="email" value="" /></div>
            <div class="email-subject-option"><label for="phone">{{emailSubject}}</label><input type="text" name="subject" value="" /></div>
        </div>
        
        <div style="float:left;width:35%">
            <div class="link-destination hidden">
            <div class="link-destination-label">{{Destination}}</div>
                <ul>
                    <li><input type="radio" name="link-destination" id="page-link" value="page"/><label for='page-link'>{{pageLink}}</lable></li>
                    <li><input type="radio" name="link-destination" id="anchor-link" value="section"/><label for='anchor-link'>{{anchorLink}}</lable></li>
                    <li>
                        <input type="radio" name="link-destination" id="file-link" value="file"/><label for='file-link'>{{fileLink}}</lable>
                        <input type="file" name="file" id="file-field" multiple="true" style="display: none"/>
                        <a href="#" id="upload-btn">{{upload}}</a>
                    </li>
                    <li><input type="radio" name="link-destination" id="phone-link" value="phone"/><label for='phone-link'>{{phoneLink}}</lable></li>
                    <li><input type="radio" name="link-destination" id="email-link" value="email"/><label for='email-link'>{{emailLink}}</lable></li>
                </ul>
            </div>      
        </div>
    </div>
    <div style="float:right">
        <button type="button" class="btn btn-success" id="save-options">Save</button>
    </div>
</div>
<div class="list-container hidden">
    <ul class="anchors-list hidden" id="anchors-list"></ul>
    <ul class="files-list hidden" id="files-list"></ul>
</div>
HTML;
        $customUrlOptions = call_user_func('base' . '64_encode', $customUrlOptions);
        $script1 = <<<EOF
        <script>
            window.phpVars = {
                'editLinkUrl': '$editLinkUrl',
                'customUrlOptions': '$customUrlOptions', 
                'maxRequestSize': $maxRequestSize,
                'uploadFileLink': '$uploadFileLink',
                'mediaFiles': $mediaFiles,
                'allowedExtensions': $allowedExtensions,
            } 
        </script>   
EOF;
        $script2 = '<script src="' . $this->getAdminUrl() . '/components/com_nicepage/assets/js/link-dialog.js"></script>';
        JFactory::getDocument()->addCustomTag($script1 . $script2);
    }

    /**
     * Add script for making data for editor
     */
    public function addDataBridgeScript()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $aid = $this->_article->id;
        $start = $input->get('start', '0');
        $autostart = $input->get('autostart', '0');
        $domain = $this->getDomain();

        $editorSettings = NicepageHelpersNicepage::getEditorSettings();
        if ($aid) {
            $editorSettings['pageId'] = $this->_isConvertRequired ? '' : $aid;
            $editorSettings['startPageId'] = $aid;
        }

        $cmsSettings = NicepageHelpersNicepage::getCmsSettings();
        $cmsSettings['isFirstStart'] = $start == '1' ? true : false;
        $cmsSettings['disableAutosave'] = $this->getDisableAutoSave();

        $editorSettingsJson = json_encode($editorSettings, JSON_PRETTY_PRINT);
        $cmsSettingsJson = json_encode($cmsSettings, JSON_PRETTY_PRINT);

        $modelActions = new NicepageModelActions();
        $site = $modelActions->getSite();
        $isNewPage = $this->_article->state == '2' && ($start == '1' || $autostart == '1');
        if ($isNewPage) {
            $site['items'][] = array(
                'siteId' => '1',
                'title' => $this->_article->title,
                'id' => (int) $aid,
                'order' => 0,
                'status' => 2,
                'editorUrl' => $this->getAdminUrl() . '/index.php?option=com_nicepage&task=nicepage.autostart&postid=' . $aid . ($domain ? '&domain=' . $domain : ''),
                'htmlUrl' => $this->getAdminUrl() . '/index.php?option=com_nicepage&task=actions.getPageHtml&pageId=' . $aid
            );
        }

        $keys = array('header', 'footer');
        foreach ($keys as $key) {
            $keyJson = '';
            if (isset($this->_componentConfig[$key . ':autosave']) && $this->_componentConfig[$key . ':autosave']) {
                $keyJson = $this->_componentConfig[$key . ':autosave'];
            } else if (isset($this->_componentConfig[$key]) && $this->_componentConfig[$key]) {
                $keyJson = $this->_componentConfig[$key];
            }
            if ($keyJson) {
                $item = json_decode(str_replace('[[site_path_editor]]', dirname($this->getAdminUrl()), $keyJson), true);
                $site[$key] = $item['html'];
            }
        }

        $info = array(
            'productsExists' => $this->vmEnabled(),
            'newPageUrl' => $this->getAdminUrl() . '/index.php?option=com_nicepage&task=nicepage.start' . ($domain ? '&domain=' . $domain : ''),
            'forceModified' => $this->forceModified(),
            'generalSettingsUrl' => $this->getAdminUrl() . '/index.php?option=com_config#page-server',
            'typographyPageHtmlUrl' => $this->getFrontendUrl(),
            'siteIsSecureAndLocalhost' => $this->siteIsSecureAndLocalhost(),
            'newPageTitle' => $isNewPage ? $this->_article->title : '',
            'fontsInfo' => $this->getFontsInfo(),
            'videoFiles' => $this->getVideoFiles(),
        );

        $themeEditorSettings = $this->getEditorSettingsFromDefaultTheme();
        if ($themeEditorSettings) {
            $info['themeTypography'] = $themeEditorSettings['typography'];
            $info['themeFontScheme'] = $themeEditorSettings['fontScheme'];
            $info['themeColorScheme'] = $themeEditorSettings['colorScheme'];
        }

        $pageHtml = $this->getSectionHtml();
        $pageHtml = str_replace('[[site_path_editor]]', dirname($this->getAdminUrl()), $pageHtml);
        $pageHtml = $this->_restoreSeoOptions($pageHtml);
        $pageHtml = $this->_restorePageType($pageHtml);
        $pageHtml = call_user_func('base' . '64_encode', $pageHtml);

        $data = json_encode(
            array (
                'site' => $site,
                'pageHtml' => $pageHtml,
                'startTerm' => $this->_isConvertRequired ? 'site:joomla:' . $aid : '',
                'defaultPageType' => $this->getDefaultPageType(true),
                'info' => $info,
                'nicePageCss' => $this->getDynamicNicepageCss(),
                'downloadedFonts' => $this->getDownloadedFonts(),
            ),
            JSON_PRETTY_PRINT
        );

        $this->_dataBridgeScripts .= <<<EOF
var dataBridgeData = $data;
window.dataBridge = {
    getSite: function () {
        return dataBridgeData.site;
    },
    setSite: function (site) {
        dataBridgeData.site = site;
    },
    getPageHtml: function () {
        return decodeURIComponent(Array.prototype.map.call(atob(dataBridgeData.pageHtml), function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
        }).join(''))
    },
    getStartTerm: function () {
        return dataBridgeData.startTerm;
    },
    getDefaultPageType: function () {
        return dataBridgeData.defaultPageType;
    },
    getInfo: function getInfo() {
        return dataBridgeData.info;
    },
    getNPCss: function getNPCss() {
        return dataBridgeData.nicePageCss;
    },
    getDownloadedFonts: function getDownloadedFonts() {
        return dataBridgeData.downloadedFonts;
    },
    setDownloadedFonts: function setDownloadedFonts(downloadedFonts) {
        dataBridgeData.downloadedFonts = downloadedFonts;
    },
    settings: $editorSettingsJson,
    cmsSettings: $cmsSettingsJson
};
EOF;
    }

    /**
     * Get raw html
     *
     * @return mixed|string
     */
    public function getSectionHtml()
    {
        $html = '';
        if ($this->_sections) {
            $props = $this->_sections->autosave_props ? $this->_sections->autosave_props : $this->_sections->props;
            $html = isset($props['html']) ? $props['html'] : '';
            $html = NicepageHelpersNicepage::processSectionsHtml($html, false);
        }
        return $html;
    }

    /**
     * Get fonts info
     *
     * @return array
     */
    public function getFontsInfo() {
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $info = array(
            'path' => '',
            'canSave' => true,
        );
        $assets = dirname(JPATH_ADMINISTRATOR) . '/components/com_nicepage/assets/css';
        if (JFolder::exists($assets)) {
            $error = $this->checkWritable($assets);
            if (count($error) > 0) {
                return array_merge($info, $error);
            }
            $fonts = $assets . '/fonts';
            if (!JFolder::exists($fonts)) {
                if (!JFolder::create($fonts)) {
                    return array_merge($info, array('path' => $fonts, 'canSave' => false));
                }
            } else {
                $error = $this->checkWritable($fonts);
                if (count($error) > 0) {
                    return array_merge($info, $error);
                }
            }
        }
        return $info;
    }

    /**
     * Check path writable
     *
     * @param string $path Path
     *
     * @return string
     */
    public function checkWritable($path) {
        $user = get_current_user();
        chown($path, $user);
        JPath::setPermissions($path, '0777');
        $result = array();
        if (!is_writable($path)) {
            $result = array(
                'path' => $path,
                'canSave' => false,
            );
        }
        return $result;
    }

    /**
     * Add main script
     */
    public function addMainScript()
    {
        $input = JFactory::getApplication()->input;
        $cookie = $input->cookie;
        $start = $input->get('start', '0');
        $autostart = $input->get('autostart', '0');
        $theme = $input->get('view', '');

        $themeTypographyCacheForceRefresh = '0';
        $cachedDefaultTheme = $cookie ? $cookie->get('DEFAULT_THEME', '') : '';
        if (!$cachedDefaultTheme || $cachedDefaultTheme !== $this->getDefaultTemplate()) {
            setcookie('DEFAULT_THEME', $this->getDefaultTemplate(), time() + 31536000); // will expire after year
            $themeTypographyCacheForceRefresh = '1';
        }

        $pageId = $this->_article->id ? $this->_article->id : -1;
        $getInfoDataUrl = dirname($this->getAdminUrl()) . '/index.php?option=com_nicepage&task=getInfoData';

        $this->_scripts .= <<<EOF
jQuery(function($) {
    var dataInfo = window.dataBridge && window.dataBridge.getInfo();
    if (dataInfo && !dataInfo['themeColorScheme']) {
        var loadCallback;
        var forceRefresh = '$themeTypographyCacheForceRefresh';
        var needResetCache = !localStorage.np_theme_typography_cache ||
            forceRefresh === '1';
    
        if (needResetCache) {
            delete localStorage.np_theme_typography_cache;
        }
        
        window.loadAppHook = function (load) {
            if (localStorage.np_theme_typography_cache) {
                jQuery.extend(dataBridgeData.info, JSON.parse(localStorage.np_theme_typography_cache));
                console.log('Regular load app.js');
                load();
                return;
            }
            loadCallback = load;
        };
        var iframe = $('<iframe>', { id: 'np-loader', 'style': 'position:absolute, visibility: hidden, width: 1800px; height:0; border:0;' }),
            nav = $('nav');
        
        if (!nav.length) {
            nav = $('div[id="nav"]');
        }
        
        nav.before(iframe);
        
        var loaderIframe = document.getElementById('np-loader');
        loaderIframe.addEventListener("load", function() {
            localStorage.np_theme_typography_cache = JSON.stringify(NpTypographyParser.parse(loaderIframe));
            $(loaderIframe).remove();
            console.log('Typography cache updated');
            $.extend(dataBridgeData.info, JSON.parse(localStorage.np_theme_typography_cache));
            if (loadCallback) {
                console.log('Deferred load app.js');
                loadCallback();
            }
        });
        
        if (location.protocol === "https:" && dataBridgeData.info.typographyPageHtmlUrl.indexOf('http://') !== -1) {
            console.log('Regular load app.js due to CORS');
            delete window.loadAppHook;
        } else {
            loaderIframe.src = dataBridgeData.info.typographyPageHtmlUrl;
        }
    }
    $.post('$getInfoDataUrl', { id: $pageId}).done(function(json) {
        var result = null;
        try {
            result = JSON.parse(json);
        } catch(e) {}
        if (result && result.result && dataBridgeData.info) {
            var infoData = result.result;
            $.extend(dataBridgeData.info, infoData);
        }
    }, 'json');
    
    // autostart nicepage from cms admin main menu
    if ('$start' == '1' || '$autostart' == '1' || '$theme' == 'theme') {
        runNicepage(true);
    }
});
EOF;
        $this->editorButtons();
    }

    /**
     * Display start, edit, preview buttons
     */
    public function editorButtons()
    {
        $aid = JFactory::getApplication()->input->get('id', '');
        if (!$aid) {
            return;
        }

        $savePageTypeUrl = $this->getAdminUrl() . '/index.php?option=com_nicepage&task=actions.savePageType';
        $duplicatePageUrl = $this->getAdminUrl() . '/index.php?option=com_nicepage&task=actions.duplicatePage';
        $previewPageUrl = dirname($this->getAdminUrl()) . '/index.php?option=com_content&view=article&id=' . $aid;
        $userId = JFactory::getUser()->id;
        $frontUrl = dirname($this->getAdminUrl()) . '/index.php?option=com_nicepage';

        $pageView = $this->getDefaultPageType();
        if ($this->_sections) {
            $props = $this->_getPageProps($this->_sections, true);
            $pageView = isset($props['pageView']) ? $props['pageView'] : $pageView;
        }

        $autoSaveMsg = $this->_autoSaveChangesExists($this->_sections) ? JText::sprintf('PLG_EDITORS-XTD_AUTOSAVE_CHANGES') : '';

        switch($pageView) {
        case 'landing':
            $templateOptions = JText::sprintf('PLG_EDITORS-XTD_TEMPLATE_OPTIONS', '', 'selected', '');
            break;
        case 'landing_with_header_footer':
            $templateOptions = JText::sprintf('PLG_EDITORS-XTD_TEMPLATE_OPTIONS', '', '', 'selected');
            break;
        default:
            $templateOptions = JText::sprintf('PLG_EDITORS-XTD_TEMPLATE_OPTIONS', 'selected', '', '');
        }
        $buttonText = $this->_isConvertRequired ? JText::_('PLG_EDITORS-XTD_TURN_TO_NICEPAGE_BUTTON_TEXT') : JText::_('PLG_EDITORS-XTD_EDIT_WITH_NICEPAGE_BUTTON_TEXT');
        $buttonAreaClass = $this->_isConvertRequired ? '' : 'nicepage-select-template-area';

        $this->_scripts .= <<<EOF
jQuery(function($) {
    var nicepageButton = $('<a href="#" class="btn nicepage-button">$buttonText</a>'),
        nicepageArea = $('<div class="$buttonAreaClass"></div>');
    
    nicepageArea.append(nicepageButton);
    
    if ($('form').length) {
        $('form').eq(0).before(nicepageArea);
    } else {
        $('section[id="content"]').prepend(nicepageArea);
    }
    
    nicepageButton.click(function(e) {
        e.preventDefault();
        runNicepage();
    });
    if ('$buttonAreaClass' !== '') {
        Joomla.originalsubmitbutton = Joomla.submitbutton; 
        Joomla.submitbutton = function npsubmitbutton(action) {
            if (action && action == 'article.save2copy') {
                $.post('$duplicatePageUrl', { postId: '$aid' }).done(function(data) {
                    if (data && data.indexOf('ok') !== -1) {
                        Joomla.originalsubmitbutton(action);
                    }
                }, 'json');
            } else {
                Joomla.originalsubmitbutton(action);
            }
            
        }
       
        var selectObj = $('$templateOptions');
        nicepageButton.after(selectObj);
        $('#toolbar-apply button, #toolbar-save button').click(function() {
            var pageType = $('.nicepage-select-template').val();
            sendRequest({
                url: '$savePageTypeUrl',
                method: 'POST',
                data: {
                    pageType: pageType,
                    pageId: $aid
                }
            }, function (error, response) {
                if (error) {
                    console.error(e);
                    alert('Save page type error.');
                }
            });
        });
    }
    
    if ('$autoSaveMsg' !== '') {
        nicepageButton.after($('$autoSaveMsg'));
    }
    
    
    $.post('$frontUrl', { uid: '$userId' }).done(function( data ) {
        if (data && data.indexOf('ok') !== -1) {
            var nicepagePreviewButton = $('<a href="$previewPageUrl" target="_blank" class="btn nicepage-preview-button">Preview page</a>');
            nicepageButton.after(nicepagePreviewButton);
        }
    }, 'json');
});
EOF;
    }

    /**
     * Get name of default template style
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__template_styles');
        $query->where('client_id = 0');
        $query->where('home=\'1\'');
        $db->setQuery($query);
        $ret = $db->loadObject();
        return $ret ? $ret->template : '';
    }

    /**
     * Include all scripts to page document
     */
    public function includeScripts()
    {
        $doc = JFactory::getDocument();

        $parserUrl = $this->getAdminUrl() . '/components/com_nicepage/assets/js/typography-parser.js';
        $doc->addScript($parserUrl);

        $doc->addCustomTag('<script>' . $this->_scripts . '</script>');

        $doc->addCustomTag('<!--np_databridge_script--><script>' . $this->_dataBridgeScripts . '</script><!--/np_databridge_script-->');
    }

    /**
     * Get default page type
     *
     * @param bool $forEditor
     *
     * @return mixed|string
     */
    public function getDefaultPageType($forEditor = false) {
        $type = isset($this->_componentConfig['pageType']) ? $this->_componentConfig['pageType'] : 'landing';
        if ($forEditor) {
            $type = $this->_editorPageTypes[$type];
        }
        return $type;
    }

    /**
     * Get downloaded fonts
     *
     * @return false|string
     */
    public function getDownloadedFonts() {
        $downloadedFontsFile = dirname(JPATH_ADMINISTRATOR) . '/components/com_nicepage/assets/css/fonts/downloadedFonts.json';
        return file_exists($downloadedFontsFile) ? file_get_contents($downloadedFontsFile) : '';
    }

    /**
     * Get disable auto save value
     *
     * @return string
     */
    public function getDisableAutoSave() {
        $disableAutosave = isset($this->_componentConfig['siteStyleCssParts']) ? true : false; // autosave disable for new user
        if (isset($this->_componentConfig['disableAutosave'])) {
            $disableAutosave = $this->_componentConfig['disableAutosave'] == '1' ? true : false;
        }
        return $disableAutosave;
    }

    /**
     * Restore seo props for page from joomla original props
     *
     * @param string $pageHtml Html of page
     *
     * @return mixed
     */
    private function _restoreSeoOptions($pageHtml) {
        $titleInBrowser = '';
        $keywords = '';
        $description = '';
        if ($this->_sections) {
            $props = $this->_getPageProps($this->_sections);
            $titleInBrowser = isset($props['titleInBrowser']) ? $props['titleInBrowser'] : '';
            $keywords = isset($props['keywords']) ? $props['keywords'] : '';
            $description = isset($props['description']) ? $props['description'] : '';
        }

        if ($this->_article->metakey && $keywords) {
            $pageHtml = str_replace('<meta name="keywords" content="' . $keywords . '">', '<meta name="keywords" content="' . $this->_article->metakey . '">', $pageHtml);
        }
        if ($this->_article->metadesc && $description) {
            $pageHtml = str_replace('<meta name="description" content="' . $description . '">', '<meta name="description" content="' . $this->_article->metadesc . '">', $pageHtml);
        }
        if ($this->_article->attribs) {
            $registry = new JRegistry();
            $registry->loadString($this->_article->attribs);
            $attribs = $registry->toArray();
            if (isset($attribs['article_page_title']) && $attribs['article_page_title'] && $titleInBrowser) {
                $pageHtml = str_replace('<title>' . $titleInBrowser . '</title>', '<title>' . $attribs['article_page_title'] . '</title>', $pageHtml);
            }
        }
        return $pageHtml;
    }

    /**
     * Restore page type for editor
     *
     * @param string $pageHtml Page html
     *
     * @return mixed
     */
    private function _restorePageType($pageHtml) {
        if ($this->_sections) {
            $props = $this->_getPageProps($this->_sections);
            $pageView = isset($props['pageView']) ? $props['pageView'] : $this->getDefaultPageType();
            $rePageType = '/<meta name="page_type" content="[^"]+?">/';
            if (preg_match($rePageType, $pageHtml)) {
                $pageHtml = preg_replace($rePageType, '<meta name="page_type" content="' . $this->_editorPageTypes[$pageView] . '">', $pageHtml);
            } else {
                $pageHtml = str_replace('<head>', '<head><meta name="page_type" content="' . $this->_editorPageTypes[$pageView] . '">', $pageHtml);
            }
        }
        return $pageHtml;
    }

    /**
     * Get page properties
     *
     * @param object $page     Page entity
     * @param bool   $allProps Get all props
     *
     * @return mixed
     */
    private function _getPageProps($page, $allProps = false)
    {
        return (!$allProps && $page->autosave_props) ? $page->autosave_props : $page->props;
    }

    /**
     * Autosave changes exists
     *
     * @param object $page Page entity
     *
     * @return bool
     */
    private function _autoSaveChangesExists($page) {
        if (!$page) {
            return false;
        }
        return !!$page->autosave_props;
    }

    /**
     * Check the existence of Virtuemart
     *
     * @return bool
     */
    public function vmEnabled()
    {
        if (!file_exists(dirname(JPATH_ADMINISTRATOR) . '/components/com_virtuemart/')) {
            return false;
        }

        if (!JComponentHelper::getComponent('com_virtuemart', true)->enabled) {
            return false;
        }
        return true;
    }

    /**
     * Check force saving or not
     */
    public function forceModified()
    {
        if ($this->_sections) {
            $props = $this->_getPageProps($this->_sections);
            return isset($props['pageCssUsedIds']) ? false : true;
        }
        return true;
    }

    /**
     * Get frontend site url
     *
     * @return string
     */
    public function getFrontendUrl()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('state') . ' = 1');
        $db->setQuery($query);
        $ret = $db->loadObject();

        if ($ret !== null) {
            return dirname($this->getAdminUrl()) . '/' . 'index.php?option=com_content&view=article&id=' . $ret->id . '&toEdit=1';
        } else {
            $frontEndUri = new JUri(dirname(dirname((JURI::current()))) . '/');
            $frontEndUri->setVar('toEdit', '1');
            return $frontEndUri->toString();
        }
    }

    /**
     * Defines site is https and localhost
     *
     * @return bool
     */
    public function siteIsSecureAndLocalhost() {
        return $this->isSSL() && $this->isLocalhost();
    }

    /**
     * Defines site is ssl
     *
     * @return bool
     */
    public function isSSL()
    {
        $isSSL = false;

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                $isSSL = true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                $isSSL = true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            $isSSL = true;
        }
        return $isSSL;
    }

    /**
     * Defines site is localhost
     *
     * @return bool
     */
    public function isLocalhost()
    {
        $whitelist = array(
            // IPv4 address
            '127.0.0.1',
            // IPv6 address
            '::1'
        );

        if (filter_has_var(INPUT_SERVER, 'REMOTE_ADDR')) {
            $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        } else if (filter_has_var(INPUT_ENV, 'REMOTE_ADDR')) {
            $ip = filter_input(INPUT_ENV, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        } else {
            $ip = null;
        }
        return $ip && in_array($ip, $whitelist);
    }

    /**
     * Get editor settings from default theme
     *
     * @return mixed|null
     */
    public function getEditorSettingsFromDefaultTheme()
    {
        $template = $this->getDefaultTemplate();
        if ($template) {
            $funcsFilePath = dirname(dirname(JPATH_THEMES)) . '/templates/' . $template . '/template.json';
            if (file_exists($funcsFilePath)) {
                ob_start();
                include_once $funcsFilePath;
                return json_decode(ob_get_clean(), true);
            }
        }
        return null;
    }

    /**
     * Get content from nicepage-dynamic.css
     *
     * @return string
     */
    public function getDynamicNicepageCss()
    {
        $assets = dirname(JPATH_PLUGINS) . '/administrator/components/com_nicepage/assets';
        ob_start();
        include $assets . '/css/nicepage-dynamic.css';
        return ob_get_clean();
    }

    /**
     * Set domain property
     */
    public function setDomain()
    {
        $this->_domain = JFactory::getApplication()->input->get('domain', (defined('NICEPAGE_DOMAIN') ? NICEPAGE_DOMAIN : ''), 'RAW');
    }

    /**
     * Get domain property
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * Set admin url
     */
    public function setAdminUrl()
    {
        $current = dirname(dirname((JURI::current())));
        $this->_adminUrl = $current . '/administrator';
    }

    /**
     * Get admin url
     *
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->_adminUrl;
    }

    /**
     * Get sections for page
     *
     * @return mixed|null
     */
    public function getSections()
    {
        return $this->_sections;
    }
}