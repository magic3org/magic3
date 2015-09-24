<?php

class Config
{
    protected static $_instance;

    private function __construct(){}

    private function __clone(){}

    /**
     * @return Config
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $config
     * @return mixed
     */
    public static function injectionDesigner($config)
    {
        $editor = JRequest::getVar('editor', '');

        $themeName    = $config['themeObject']->template;
        $themeDir     = JPATH_SITE . '/templates/' . $themeName;

        if ('1' === $editor) {

            define('EXPORT_APP', __DIR__);
            require_once EXPORT_APP . '/PermissionsException.php';
            require_once EXPORT_APP . '/Helper.php';

            $app = JFactory::getApplication('administrator');
            $adminThemeDir = JPATH_ADMINISTRATOR . '/templates/' . $app->getTemplate();
            try {
                Config::buildAppManifestVersion($themeName);
                // copy new exported template
                Helper::copyFile($themeDir . '/app/start/themler.php', $adminThemeDir . '/themler.php');
                // change admin template
                $app->input->set('tmpl', 'themler');
            } catch (PermissionsException $e) {
                $msg = $e->getMessage();
                $content = <<<EOF
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>$msg</body></html>
EOF;
                Helper::writeFile($themeDir . '/app/tmpl/_warning.php', $content);
                $app->redirect(JURI::root() . 'templates/' . $themeName . '/app/tmpl/_warning.php');
            }
        } else {
            ob_start();
            ?>
            <script>if ('undefined' != typeof jQuery) document._jQuery = jQuery;</script>
            <script src="<?php echo JURI::root() . 'templates/' . $themeName . '/jquery.js' ?>" type="text/javascript"></script>
            <script>jQuery.noConflict();</script>
            <script>
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
                    var btnSelector = '#<?php echo $config['parameterObject']->id; ?>',
                        appPath = '<?php echo JURI::root() . 'templates/' . $themeName . '/app'; ?>',
                        params = {};

                    $(btnSelector).removeAttr("disabled");

                    function check(warningCallback) {
                        $.ajax({
                            type : 'post',
                            url : appPath + '/index.php',
                            dataType : 'json',
                            data : {
                                action : 'runUp',
                                template : '<?php echo $config['themeObject']->template; ?>',
                                params : null
                            },
                            success : function(data) {
                                var phpCompatibilityResult = '<?php echo Config::checkPhpCompatibility(); ?>',
                                    permissionsResult = '<?php echo Config::checkPermissions(); ?>',
                                    memoryWarning = '<?php echo Config::getMemoryWarning(); ?>',
                                    offlineWarning = '<?php echo Config::getSiteOfflineWarning(); ?>',
                                    query = '&editor=1&theme=<?php echo $themeName; ?>';

                                if (data.error) {
                                    switch(data.error) {
                                        case 'permissions':
                                            return warningCallback(btoa(data.message),  710, 600);
                                        case 'memtest':
                                            memoryWarning = btoa(atob(memoryWarning).replace(/\[\[[\s\S]+\]\]/, ''));
                                            return warningCallback(memoryWarning,  410, 155);
                                        case 'memdata':
                                            memoryWarning = btoa(atob(memoryWarning).replace(/\{amount\}/, data.amount).replace(/\[\[([\s\S]+)\]\]/, '$1'));
                                            return warningCallback(memoryWarning,  410, 155);

                                    }
                                } else {
                                    if (phpCompatibilityResult) {
                                        return warningCallback(phpCompatibilityResult, 500, 120);
                                    }
                                    if (permissionsResult) {
                                        return warningCallback(permissionsResult, 555, 460);
                                    }
                                    query = query + '&ver=' + parseInt(data.version ? data.version : 0);
                                    if (offlineWarning)
                                        return warningCallback(offlineWarning, 400, 140, function() {
                                            document.location.href += query;
                                        });
                                    document.location.href += query;
                                }
                            },
                            error: function (xhr, status) {
                                warningCallback(btoa(xhr.responseText), 500, 600);
                            }
                        });
                    }

                    $('input[type=\'text\'][id*=\'jform_params_\'], select[id*=\'jform_params_\']').each(function () {
                        var option = $(this), name = option.attr('id').substring(13), value;
                        if ('select' == option.prop('tagName').toLowerCase())
                            value  = option.val();
                        else
                            value = option.attr('value');
                        params[name] = value;
                    });

                    $(btnSelector).click(function (e) {
                        $(btnSelector).attr("disabled", true);
                        e.preventDefault();
                        $.ajax({
                            type: 'post',
                            url: appPath + '/index.php',
                            dataType: "json",
                            data: {
                                action: 'setParameters',
                                styleId  : '<?php echo $config['themeObject']->id; ?>',
                                params      : params
                            },
                            success: function () {},
                            error: function (xhr, status) {}
                        });

                        check(function (content, windowX, windowY, callbackEdit) {
                            var uniqueId = new Date().getTime();
                            SqueezeBox.fromElement(appPath + '/tmpl/warning.html?id=' + uniqueId, {
                                size : {x : windowX, y : windowY},
                                iframePreload: true,
                                handler : 'iframe',
                                onOpen : function (container, showContent) {
                                    var ifrDoc = container.firstChild.contentDocument;
                                    $('#warning', ifrDoc).replaceWith(atob(content));
                                    $('#edit', ifrDoc).bind('click', callbackEdit);
                                    $('#cancel', ifrDoc).bind('click', function () {
                                        SqueezeBox.close();
                                    });
                                    container.setStyle('display', showContent ? '' : 'none');
                                },
                                onClose : function () {
                                    $(btnSelector).removeAttr("disabled");
                                }
                            });
                            window.setTimeout(function () {
                                SqueezeBox.fireEvent('onOpen', [SqueezeBox.content, true]);
                            }, 1000);
                        });
                    });
                });

            </script>
            <script>if (document._jQuery) jQuery = document._jQuery;</script>
            <button name="<?php echo $config['parameterObject']->name; ?>"
                    id="<?php echo $config['parameterObject']->id; ?>" disabled>
                Edit Template
            </button>
            <?php
            return ob_get_clean();
        }
    }

    public static function buildAppManifestVersion($templateName) {
        $themeDir     = JPATH_SITE . '/templates/' . $templateName;
        //create manifests folder
        $manifestsDir = JPATH_SITE . '/templates/manifests';
        if (!file_exists($manifestsDir))
            Helper::createDir($manifestsDir);
        Helper::writeFile($manifestsDir . '/manifest.php',
            Helper::readFile($themeDir . '/app/start/manifest.php'));

        $manifestPath = $themeDir . '/app/themler.manifest';
        $versionPath = $themeDir . '/app/themler.version';
        $themeManifestsDir = $themeDir . '/app/manifests';
        if (file_exists($manifestPath)) {
            $content = Helper::readFile($manifestPath);
            if (preg_match('#\#ver:(\d+)#i', $content, $matches)) {
                $v = trim($matches[1]);
                $newManifestName = 'themler-' . $v . '.manifest';
                Helper::writeFile($manifestsDir . '/' . $newManifestName, $content);
                Helper::createDir($themeManifestsDir);
                Helper::writeFile($themeManifestsDir . '/' . $newManifestName, $content);
                Helper::writeFile($versionPath, $v);
                Helper::deleteFile($manifestPath);
            }
        }

        $version = '';
        if (file_exists($versionPath)) {
            $version = Helper::readFile($versionPath);
            $fileName = 'themler-' . $version . '.manifest';
            if (!file_exists($manifestsDir . '/' . $fileName) &&
                file_exists($themeManifestsDir . '/' . $fileName)) {
                Helper::copyFile($themeManifestsDir . '/' . $fileName, $manifestsDir . '/' . $fileName);
            }
        }
        return $version;
    }

    public static function getSiteOfflineWarning() {
        if ('1' == JFactory::getConfig()->get('offline')) {
            $content = <<<EOF
<style>
    .msgbox button { width: 60px; height: 25px; font-size: 13px; margin-left: 5px; }
    .msgbox { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 20px; }
    .msgbox h3 { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 0 0 10px 0; }
    .msgbox p { margin: 15px 0 0 0; text-align: justify;}
    .msgbox .buttons { text-align: center; }
    .msgbox button { height: 25px; font-size: 14px; margin-left: 5px; }
</style>
<div class="msgbox">
    <p>
        <p>Your website is offline.</p>
        <p>Please make sure that you are logged in to your front-end before opening your template in Themler.</p>
    </p>
    <p class="buttons">
        <button id="edit">Edit</button>
        <button id="cancel">Cancel</button>
    </p>
</div>
EOF;
            return base64_encode($content);
        } else {
            return '';
        }
    }

    public static function checkPhpCompatibility() {
        $currentVersion = PHP_VERSION;
        if (version_compare($currentVersion, '5.3.0', '<')) {
            $content = <<<EOF
<style>
    .msgbox button { width: 60px; height: 25px; font-size: 13px; margin-left: 5px; }
    .msgbox { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 20px; }
    .msgbox h3 { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 0 0 10px 0; }
    .msgbox p { margin: 15px 0 0 0; text-align: justify; }
    .msgbox .buttons { text-align: center; }
    .msgbox button { width: 70px; height: 30px; font-size: 14px; margin-left: 5px; }
</style>
<div class="msgbox">
    <p>
        Your server is running php version $currentVersion, but Themler requires 5.3 or higher
    </p>
    <p class="buttons">
        <button id="cancel">Cancel</button>
    </p>
</div>
EOF;
            return base64_encode($content);
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public static function checkPermissions() {
        $app =  JFactory::getApplication('administrator');
        $permissionsResult = '';
        $folders = array(
            JPATH_SITE . '/administrator/templates/' . $app->getTemplate(),
            JPATH_SITE . '/templates',
            JPATH_SITE . '/language',
            JFactory::getConfig()->get('tmp_path')
        );
        foreach($folders as $value) {
            if (!is_writable($value)) {
                $permissionsResult = implode("<br />", $folders);
                break;
            }
        }
        if ('' !== $permissionsResult) {
            $content = <<<EOF
<style>
    .msgbox button { width: 60px; height: 25px; font-size: 13px; margin-left: 5px; }
    .msgbox { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 20px; }
    .msgbox h3 { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 0 0 10px 0; }
    .msgbox p { margin: 15px 0 0 0; text-align: justify; }
    .msgbox .buttons { text-align: center; }
    .msgbox button { width: 70px; height: 30px; font-size: 14px; margin-left: 5px; }
</style>
<div class="msgbox">
    <h2>Insufficient permissions.</h2>
    <p>
        The theme cannot be edited. Please make sure that the user and group running web server is granted the appropriate
        read, write and execute(linux only) permissions on the following folders. As well as read and write permission on
        the files in these folders:
    </p>
    <p class="folders">
        $permissionsResult
    </p>
    <p>How to do this for MacOS or Linux systems:</p>
    <ol>
        <li>login ssh/terminal under privileged user, get sufficient access rights if need using sudo or su to make next changes</li>
        <li>cd {root}</li>
        <li>
            <div>chmod -R u=rwX,g=rX folder_name</div>
            <div><i>For example: chmod -R u=rwX,g=rX app/code/local</i></div>
        </li>
        <li>
            <div>chown -R &#60;user>:&#60;group> folder_name</div>
            <div><i>For example: chown -R apache:apache app/code/local</i></div>
        </li>
    </ol>
    <p>
        <b>Note</b>: It is general approach. We would recommend that you ask your hosting administrator to grant access
        permissions for listed folders and files.
    </p>
    <p class="buttons">
        <button id="cancel">Cancel</button>
    </p>
</div>
EOF;
            return base64_encode($content);
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public static function getMemoryWarning() {
        $content = <<<EOL
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warning</title>
    <style type="text/css">
        .msgbox { font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 20px; }
        .msgbox h3 { font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0 0 10px 0; }
        .msgbox p { margin: 15px 0 0 0; text-align: justify; }
        .msgbox .buttons { text-align: right; }
        .msgbox button { height: 25px; font-size: 12px; margin-left: 5px; }
    </style>
</head>
<body>
<div class="msgbox">
    <h3>PHP Memory Configuration Error</h3>
    <p>Themler requires at least 64Mb of PHP memory[[(you have {amount})]]. Please increase your PHP memory to continue.
        For more information, please check this <a href="http://answers.billiondigital.com/articles/5826/out-of-memory" target="_blank">link</a>.</p>
    <p class="buttons">
        <button id="cancel">Close</button>
    </p>
</div>
</body>
</html>
EOL;
        return base64_encode($content);
    }

    /**
     * @param $themeObject
     */
    public static function buildPreview()
    {
        $themeObject = Config::getStyleObject();
        $themeName    = $themeObject->template;
        $themeDir     = JPATH_SITE . '/templates/' . $themeName;
        $editorDir = $themeDir . '/editor';
        $tmpDir = $themeDir . '/tmp';

        // removing editor files
        if (file_exists($editorDir))
            Helper::removeDir($editorDir);

        Helper::createDir($editorDir);

        // removing temporary files
        if (file_exists($tmpDir))
            Helper::removeDir($tmpDir);

        require_once JPATH_SITE . '/templates/' . $themeName . '/app/classes/' . 'Preview.php';

        $placeholders = new PlaceHoldersStorage();
        $fragments = Helper::enumerate($themeDir . '/app/fragments');
        $placeholders->set(Helper::loadFragments($fragments));
        $editorCondOpen = $placeholders->get('include_editor_prepend');
        $editorCondOpenLen = strlen($editorCondOpen);
        $editorCondClose = $placeholders->get('include_editor_append');
        $editorCondCloseLen = strlen($editorCondClose);
        $newfiles = new FileDiffs($themeDir . '/app/newfiles.json');
        $newfiles->clean();
        $preview = new Preview($themeName);
        // copy theme to editor
        $flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($themeDir, $flags));
        foreach ($iterator as $fileInfo) {
            $path = $iterator->getSubPathname();
            $content = Helper::readFile($fileInfo->getPathName());
            $ext = strtolower($fileInfo->getExtension());

            $info = pathinfo($path);
            $dirNameParts = explode('/', $info['dirname']);
            $type = '';
            if (count($dirNameParts) > 2 && 'html' == $dirNameParts[0]) {
                $componentParts = explode('_', $dirNameParts[1]);
                $type = array_shift($componentParts);
            }
            $parts      = explode('.', $info['basename']);
            $fname      = $parts[0];

            if (preg_match('/^editor/', $path) || preg_match('/^css\/editor\.css$/', $path)
                || preg_match('/^css\/print\.css$/', $path) || preg_match('/^images\/designer\/.*/', $path)
                || preg_match('/^app\/(cache|hashes|diffs|project)\.json$/', $path))
                continue;
            if (preg_match('/functions\.php$/', $path))
                $content = str_replace('\'is_preview\' => false', '\'is_preview\' => true', $content);
            if (preg_match('/script\.js$/', $path))
                $content = str_replace('var PREVIEW = false', 'var PREVIEW = true', $content);
            if (preg_match('/^css\/template\.css/', $path)) {
                $content = str_replace('url(../images/designer/', 'url(../../images/designer/', $content);
                $content = preg_replace('|url\(([\"\']{0,1})\.\./\.\./\.\./images/|', 'url($1../../../../images/', $content);
            }
            if (!preg_match('/^app\//', $path) && 'php' === $ext) {
                $content = $preview->restoreDataId($path, $content);
                $old = array('<?php $document = JFactory::getDocument(); echo $document->templateUrl; ?>/images',
                    '<?php echo JFactory::getDocument()->baseurl . \'/templates/\' . JFactory::getApplication()->getTemplate(); ?>/images');
                $new = '<?php echo JURI::base() . \'templates/\' . JFactory::getApplication()->getTemplate(); ?>/images';
                $content = str_replace($old[0], $new, $content);
                $content = str_replace($old[1], $new, $content);
                if ('com' === $type || 'mod' === $type || 'pagination' === $fname || 'error' === $fname
                    || 'modules' === $fname || 'index.php' === $path || 'modrender.php' === $path) {
                    $posOpen = strpos($content, $editorCondOpen);
                    if (false !== $posOpen) {
                        $content = substr_replace($content, '', $posOpen, $editorCondOpenLen);
                        $content = substr_replace($content, '', strrpos($content, $editorCondClose), $editorCondCloseLen);
                        $withSlash = '/' . $path;
                        $newfiles->refresh(array($withSlash => '[NEW]'));
                    }
                }
            }
            $pos_last_slash = strrpos($path, "/");
            $fullPath = $editorDir . '/' . $path;
            if (false !== $pos_last_slash) {
                $currentDir = $editorDir . '/' . substr($path, 0, $pos_last_slash + 1);
                Helper::createDir($currentDir);
            }
            Helper::writeFile($fullPath, $content);
        }
        $newfiles->save();
    }

    /**
     * @param $themeObject
     */
    public static function buildStartFiles()
    {
        $startFiles = array();
        $base = JURI::root();
        $themeObject = Config::getStyleObject();
        $themeName    = $themeObject->template;
        $themeDir     = JPATH_SITE . '/templates/' . $themeName;

        $versionPath = $themeDir . '/app/themler.version';
        $version = file_exists($versionPath) ? file_get_contents($versionPath) : '';
        $startFiles['manifest'] = $base . 'templates/manifests/manifest.php?ver=' . $version;

        $hash = md5(round(microtime(true)));
        $startFiles['project'] = $base . 'templates/' . $themeName . '/app/start/data.php?version=' . $hash;
        $startFiles['templates'] = $base . 'templates/' . $themeName . '/app/start/templates.php?version=' . $hash;
        $startFiles['dataProvider'] =  $base . 'templates/' . $themeName . '/app/start/DataProvider.js?version=' . $hash;
        $startFiles['loader'] = $base . 'templates/' . $themeName . '/app/start/loader.js?version=' . $hash;
        return $startFiles;
    }

    /**
     * @param $themeObject
     */
    public static function getConfigObject()
    {
        $themeObject = Config::getStyleObject();
        $current = dirname(JURI::current()) . '/';
        $base = dirname(dirname(dirname(dirname($current)))) . '/';
        $themeName    = $themeObject->template;
        $themeDir     = JPATH_SITE . '/templates/' . $themeName;

        $infoData['isThemeActive'] = 1 == $themeObject->home ? true : false;

        $infoData['cmsVersion'] = Config::getVersions();
        $infoData['maxRequestSize'] = Config::getMaxRequestSize();
        $infoData['canDuplicateTemplatesConstructors'] = Config::getDuplicateTemplatesConstructors($themeDir);

        $infoData['startPage'] = Config::getStartPage();
        $infoData['adminPage'] = $base . 'administrator';

        $infoData['contentIsImported'] = self::contentIsImported();

        $cache  = new Cache($themeDir . '/app/cache.json');
        $hashes = new Hash($themeDir . '/app/hashes.json');

        $projectFile = $themeDir . '/app/project.json';
        $project = new Project($projectFile);

        return json_encode(array(
            'index'         => $base  . 'templates/' . $themeName . '/app/index.php',
            'styleId'       => $themeObject->id,
            'templateName'  => $themeName,
            'templateId'    => self::getTemplateId($themeName),
            'revision'      => $project->getValue('revision'),
            'projectData'   => base64_encode(json_encode($project->getValue('projectdata'))),
            'cssJsSources'  => $cache->get()->toArray(),
            'md5Hashes'     => $hashes->get()->toArray(),
            'infoData'      => $infoData,
        ));
    }

    public static function getStartPage($reload = false)
    {
        $current = dirname(JURI::current()) . '/';
        $root = dirname(dirname(dirname(dirname($current)))) . '/';
        if ($reload)
            $root = dirname(dirname(dirname($current))) . '/';

        $themeObject = Config::getStyleObject();
        $themeName    = $themeObject->template;

        $uid = JFactory::getUser()->id;
        $menu = Config::getSiteMenu();
        $language = JFactory::getLanguage();
        $defaultMenu = $menu->getDefault($language->getTag());
        $homeLink = $root;
        if (null !== $defaultMenu) {
            $homeLink = $defaultMenu->link . (isset($defaultMenu->id) ? '&Itemid=' . $defaultMenu->id : '');
            $parts = explode('-', JComponentHelper::getParams('com_languages')->get('site', 'en-GB'));
            if ($parts > 1)
                $homeLink .= '&lang=' . array_shift($parts);
        }
        return $root . ($homeLink ? $homeLink . '&' : '?') . 'template=' . $themeName . '&is_preview=on&uid='. $uid;
    }

    /**
     * @param $themePath
     * @return bool
     */
    public static function contentIsImported()
    {
        $themeName = Config::getStyleObject()->template;
        $themeDir = JPATH_SITE . '/templates/' . $themeName;
        if (file_exists($themeDir . '/data/data.xml') &&
            file_exists($themeDir . '/data/converter.data'))
            return false;
        else
            return true;
    }

    /**
     * @param string $template
     * @return mixed
     */
    public static function getStyleObject($template = '')
    {
        if (!$template) {
            $parts = pathinfo(dirname(dirname(dirname(__FILE__))));
            $template = $parts['filename'];
        }

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__template_styles');
        $query->where('template=\'' . $template . '\'');
        $query->where('client_id=0');
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * @return bool
     */
    public static function isSSL() {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * @param $dir
     * @return array
     */
    public static function getDuplicateTemplatesConstructors($dir)
    {
        $constructors = array();
        include($dir . '/templates/list.php');
        return $constructors;
    }

    /**
     * @return array
     */
    public static function getVersions()
    {
        $versions = array('joomla' => JVERSION, 'virtuemart' => '');

        $vmEnabled = false;
        if (JComponentHelper::getComponent('com_virtuemart', true)->enabled) {
            $vmEnabled = true;
        }

        if ($vmEnabled) {
            if (!class_exists('VmConfig')) {
                $configFile = JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php';
                if (file_exists($configFile)) {
                    require($configFile);
                    $versions['virtuemart'] = VmConfig::getInstalledVersion();
                }
            } else {
                $versions['virtuemart'] = VmConfig::getInstalledVersion();
            }
        }
        return $versions;
    }

    /**
     * @return float|int
     */
    public static function getMaxRequestSize()
    {
        $postSize = self::toBytes(ini_get('post_max_size'));
        $uploadSize = self::toBytes(ini_get('upload_max_filesize'));
        $memorySize = self::toBytes(ini_get('memory_limit'));

        return min($postSize, $uploadSize, $memorySize);
    }

    /**
     * @param $str
     * @return int
     */
    public static function toBytes($str) {
        $str = strtolower(trim($str));

        if ($str) {
            switch ($str[strlen($str) - 1]) {
                case 'g':
                    $str *= 1024;
                case 'm':
                    $str *= 1024;
                case 'k':
                    $str *= 1024;
            }
        }

        return intval($str);
    }

    /**
     * @param $item
     * @return bool
     */
    public static function isPageCheckItem($item)
    {
        $itemParams = $item->params->toArray();

        $contentComponent = JComponentHelper::getParams('com_content');
        $globalParams = $contentComponent->toArray();

        foreach($globalParams as $key => $value) {
            if (!isset($itemParams[$key]) || (isset($itemParams[$key]) && '' == $itemParams[$key])) {
                $itemParams[$key] = $globalParams[$key];
            }
        }

        if ('0' !== $itemParams['show_category'])
            return false;
        if ('0' !== $itemParams['show_create_date'])
            return false;
        if ('0' !== $itemParams['show_modify_date'])
            return false;
        if ('0' !== $itemParams['show_publish_date'])
            return false;
        if ('0' !== $itemParams['show_author'])
            return false;
        if ('0' !== $itemParams['show_print_icon'])
            return false;
        if ('0' !== $itemParams['show_email_icon'])
            return false;

        return true;
    }

    /**
     * @return JMenu
     */
    public static function getSiteMenu()
    {
        // instantiate the frontend application.
        JFactory::$application = JApplication::getInstance('site');
        // create templates list
        $site   = JFactory::getApplication('site');
        $menu   = $site->getMenu('site');
        // instantiate the backend application.
        JFactory::$application = JApplication::getInstance('administrator');

        return $menu;
    }

    /**
     * @return array
     */
    public static function getThemeTemplates($fromPreview = false)
    {
        $themeName = Config::getStyleObject()->template;
        $current = dirname(JURI::current()) . '/';
        $root = dirname(dirname(dirname(dirname($current)))) . '/';
        if ($fromPreview)
            $root = dirname(dirname(dirname($current))) . '/';
        $themeDir = JPATH_SITE . '/templates/' . $themeName;
        $templatesInfo = array();
        $resultTemplatesList = array();
        $templatesListPath = $themeDir . ($fromPreview ? '/editor' : '') . '/templates/list.php';
        // including this file to create a variable - $resultTemplatesList, $templatesInfo
        include($templatesListPath);

        jimport('joomla.application.module.helper');
        require_once $themeDir . '/library/Designer/CustomModuleHelper.php';

        $allTemplatesList = array();
        $pathToManifest = JPATH_SITE . '/templates/' . $themeName . '/templateDetails.xml';
        if (file_exists($pathToManifest)) {
            // create templates list
            $site   = JFactory::getApplication('site');
            $menu   = $site->getMenu('site');
            $language = JFactory::getLanguage();
            $defaultMenu = $menu->getDefault($language->getTag());
            if (null !== $defaultMenu) {
                // home link
                $homeLink = $defaultMenu->link . (isset($defaultMenu->id) ? '&Itemid=' . $defaultMenu->id : '');

                if (0 == JRequest::setVar('Itemid'))
                    JRequest::setVar('Itemid', $defaultMenu->id);
                $menuModules = array();
                $xml = simplexml_load_file($pathToManifest);
                if (isset($xml->positions[0])) {
                    foreach ($xml->positions[0] as $position) {
                        $modules = CustomModuleHelper::getModules($position);
                        foreach ($modules as $mod) {
                            if('mod_menu' == $mod->module) {
                                $menuModules[] = $mod;
                            }
                        }
                    }
                }
                foreach ($menuModules as $module) {
                    $params = new JRegistry;
                    $params->loadString($module->params);
                    require_once JPATH_SITE . '/modules/mod_menu/helper.php';
                    $list = modMenuHelper::getList($params);
                    foreach($list as $item) {
                        if ($item->type !== 'component') continue;
                        if (isset($item->query['option'])) {
                            $value = $item->query['option'];
                            $link = $item->link . (isset($item->id) ? '&Itemid=' . $item->id : '');
                            if (isset($item->query['view'])) {
                                $view = $item->query['view'];
                                $value .= '@' . $view;
                                if (isset($item->query['id']) && 'article' === $view) {
                                    $value .= Config::isPageCheckItem($item) ? '@is_page' : '';
                                }
                            }

                            $parts = explode('-', $item->language);
                            $lang = $parts > 1 ? '&lang=' . array_shift($parts) : '';
                            if (!array_key_exists($value, $allTemplatesList) && $homeLink !== $link)
                                $allTemplatesList[$value] = array('url' => $link . $lang, 'selected' => 'false');
                        }
                    }
                }
                $parts = explode('-', JComponentHelper::getParams('com_languages')->get('site', 'en-GB'));
                if ($parts > 1)
                    $homeLink .= '&lang=' . array_shift($parts);
                $allTemplatesList['#'] = array('url' => $homeLink, 'selected' => 'false');
            }

            $uid = JFactory::getUser()->id;
            $previewParams = '&template=' . $themeName . '&is_preview=on&uid='. $uid;

            // set default urls for existing templates
            $templatesHtml  = $root . 'templates/' . $themeName . '/app/tmpl/templates.html';

            foreach($resultTemplatesList as $name => $info) {
                if ('yes' == $info['virtuemart'] && !JComponentHelper::getComponent('com_virtuemart', true)->enabled
                    && !isset($info['commonHelpLink'])) {
                    unset($resultTemplatesList[$name]);
                    unset($templatesInfo[$name]);
                } else {
                    $resultTemplatesList[$name] = $info['helpLink'];
                }
            }

            // set actual urls for found templates
            foreach($templatesInfo as $templateName => $item) {
                if ('error404' === $item['kind']) {
                    $resultTemplatesList[$templateName] = $root . 'index.php?option=com_error' .
                        $previewParams . '&file_template_name=' . $item['fileName'];
                    continue;
                }
                $url = $templatesHtml . $resultTemplatesList[$templateName] .
                    '&file_template_name=' . $item['fileName'];
                if ('products' === $item['kind'] && !JComponentHelper::getComponent('com_virtuemart', true)->enabled) {
                    $resultTemplatesList[$templateName] = $url;
                    continue;
                }
                foreach($allTemplatesList as $action => $info) {
                    if ($item['action'] === $action) {
                        $url = $root . $info['url'] .
                            $previewParams . '&file_template_name=' . $item['fileName'];
                        $allTemplatesList[$action]['selected'] = 'true';
                        break;
                    }
                }
                $resultTemplatesList[$templateName] = $url;
            }
            // add default template
            foreach($templatesInfo as $templateName => $item) {
                if ('default' !== $item['kind'])
                    continue;
                foreach($allTemplatesList as $info) {
                    if ('true' === $info['selected'])
                        continue;
                    $resultTemplatesList[$templateName] = $root . $info['url'] .
                        $previewParams . '&file_template_name=' . $item['fileName'];
                    break;
                }
            }
        }
        return $resultTemplatesList;
    }

    /**
     * @param $templateName
     * @return mixed
     */
    public static function getTemplateId($templateName)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('extension_id');
        $query->from('#__extensions');
        $query->where('element=\'' . $templateName . '\'');
        $db->setQuery($query);
        return $db->loadResult();
    }
}