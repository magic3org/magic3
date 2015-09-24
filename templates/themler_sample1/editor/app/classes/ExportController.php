<?php

require_once EXPORT_APP . '/Storage.php';
require_once EXPORT_APP . '/ThemeBuilder.php';
require_once EXPORT_APP . '/Chunk.php';
require_once EXPORT_APP . '/Config.php';
require_once EXPORT_APP . '/Archive.php';

class ExportController
{
    public function __construct() {}

    /**
     * @param $data
     */
    public function execute($data)
    {
        try {
            $app = JFactory::getApplication();
            $styleId = Config::getStyleObject()->id;
            if (isset($data['auto_authorization']) && 1 === (int)$data['auto_authorization']) {
                $params = array();
                $username = '';
                if (isset($data['username'])) {
                    $params[] = 'login=' . $data['username'];
                    $username = $data['username'];
                }
                $password = '';
                if (isset($data['password'])) {
                    $params[] = 'password=' . $data['password'];
                    $password = $data['password'];
                }
                if (isset($data['domain']))   $params[] = 'domain=' . urlencode($data['domain']);
                if (isset($data['ver']))      $params[] = 'ver=' . $data['ver'];
                if (isset($data['startup']))  $params[] = 'startup=' . $data['startup'];
                if (isset($data['desktop']))  $params[] = 'desktop=' . $data['desktop'];
                if (count($params) > 0)       $params = '&' . implode('&', $params);

                if ('' !== $username) {
                    $credentials = array( 'username' => $username, 'password' => $password);
                    $app->login($credentials, array('action' => 'core.login.admin'));
                }

                $current = dirname(JURI::current()) . '/';
                $return = dirname(dirname(dirname($current))) . '/administrator/';

                $session = JFactory::getSession();
                $registry = $session->get('registry');
                if (null !== $registry)
                    $registry->set('com_templates.edit.style.id', $styleId);

                if ($styleId) {
                    $return .= 'index.php?option=com_templates&view=style&layout=edit&id=' .
                        $styleId . '&editor=1&theme=' . Config::getStyleObject()->template .  $params;
                }
                $app->redirect($return);
            }

            Helper::tryAllocateMemory();

            // checking user privileges
            $user = JFactory::getUser();
            $session = JFactory::getSession();
            if (!(1 !== (integer)$user->guest && 'active' === $session->getState())) {
                $registry = $session->get('registry');
                if (null !== $registry)
                    $registry->set('com_templates.edit.style.id', $styleId);
                echo $this->_response(array('error' => 'sessions'));
            } else {
                echo $this->{$data['action']}($data);
            }
        } catch (PermissionsException $e) {
            echo $this->_response(array('error' => 'permissions', 'message' => $e->getMessage()));
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function doExport($data)
    {
        return $this->_export($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function saveProject($data)
    {
        return $this->_export($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function _export($data)
    {
        $timeLogging = LoggingTime::getInstance();

        $timeLogging->start('[PHP] Get chunk info');

        $info = isset($data['info']) ?
            json_decode($data['info'], true) :
            $this->_getChunkInfo($data);

        $timeLogging->end('[PHP] Get chunk info');

        if (null === $info) {
            trigger_error('Chunk is failed - "' . $data['info'] . '"', E_USER_ERROR);
        }

        $timeLogging->start('[PHP] Chunk save');

        $templateName = $data['template'];
        $chunk = new Chunk();
        $chunk->save($info);

        $timeLogging->end('[PHP] Chunk save');

        if ($chunk->last()) {
            $timeLogging->start('[PHP] Decode json result');
            $result = json_decode($chunk->complete(), true);
            $timeLogging->end('[PHP] Decode json result');
            // icon fonts collection
            $icons = array_key_exists('iconSetFiles', $result) > 0 ? $result['iconSetFiles'] : '';
            // thumbnails collection
            $thumbnails = array_key_exists('thumbnails', $result) > 0 ? $result['thumbnails'] : '';
            // custom images collection
            $images = array_key_exists('images', $result) > 0 ? $result['images'] : '';
            // storage for media files
            $media = array('icons' => $icons, 'thumbnails' => $thumbnails, 'images' => $images);
            $themeDir = JPATH_SITE . '/templates/' . $templateName;

            $timeLogging->start('[PHP] Build Theme');
            $timeLogging->start('[PHP] Initializing theme builder');
            $themeBuilder = new ThemeBuilder($templateName, $media);
            $timeLogging->end('[PHP] Initializing theme builder');
            $storage = array();
            if (array_key_exists('themeFso', $result)) {
                $timeLogging->start('[PHP] Build file storage from themeFso');
                $storage = Helper::buildThemeStorage($result['themeFso']);
                $timeLogging->end('[PHP] Build file storage from themeFso');
            }
            $timeLogging->start('[PHP] Export doing');
            $themeBuilder->export($storage);
            $timeLogging->end('[PHP] Export doing');

            if ('saveProject' === $data['action']) {

                $timeLogging->start('[PHP] Update original theme');
                $themeBuilder->updateOriginalTheme();
                $timeLogging->end('[PHP] Update original theme');

                if (array_key_exists('projectData', $result)) {
                    $timeLogging->start('[PHP] Save project json');
                    $projectFile = $themeDir . '/app/project.json';
                    $project = new Project($projectFile);
                    $project->refresh(array('projectdata' => $result['projectData']));
                    $project->save();
                    $timeLogging->end('[PHP] Save project json');
                }

                if (array_key_exists('cssJsSources', $result)) {
                    $timeLogging->start('[PHP] Save css/js sources');
                    $cacheFile = $themeDir . '/app/cache.json';
                    $cache = new Cache($cacheFile);
                    $cache->refresh($result['cssJsSources']);
                    $cache->save();
                    $timeLogging->end('[PHP] Save css/js sources');
                }

                if (array_key_exists('md5Hashes', $result)) {
                    $timeLogging->start('[PHP] Save md5 file hashes');
                    $hashesFile = $themeDir . '/app/hashes.json';
                    $haches = new Hash($hashesFile);
                    $haches->refresh($result['md5Hashes']);
                    $haches->save();
                    $timeLogging->end('[PHP] Save md5 file hashes');
                }

                if (file_exists($themeDir . '/data/converter.data'))
                    Helper::deleteFile($themeDir . '/data/converter.data');
            }
            $timeLogging->end('[PHP] Build Theme');
            $timeLogging->end('[PHP] Joomla start of work');
            return $this->_response(array('result' => 'done', 'log' => $timeLogging->getLog()));
        } else {
            $timeLogging->end('[PHP] Joomla start of work');
            return $this->_response(array('result' => 'processed', 'log' => $timeLogging->getLog()));
        }    
    }

    /**
     * @param $data
     * @return mixed
     */
    public function clearChunks($data)
    {
        $chunk = new Chunk();
        $uploadPath = $chunk->UPLOAD_PATH;
        if (($id = $data['id']) && $id && is_dir($uploadPath . $id)) {
            Helper::removeDir($uploadPath . $id);
            return $this->_response('ok');
        } else {
            return $this->_response('fail');
        }    
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updatePreview($data)
    {
        return $this->_response('updated');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function setParameters($data)
    {
        $id = isset($data['styleId']) && is_string($data['styleId'])
                && ctype_digit($data['styleId']) ? intval($data['styleId'], 10) : -1;
        if (-1 !== $id)
            $this->_setPatameters($id, $data['params']);
        return $this->_response('parameters setted');
    }

    public function runUp($data)
    {
        // testing 16M of memory
        $func = create_function("", "echo json_encode(array(error => 'memtest'));");
        $callback = new UnregisterableCallback($func);
        register_shutdown_function(array($callback, "call"));
        @str_repeat('.', 16 * 1024 * 1024);
        $callback->unregister();

        // check memory allocating
        if(!Helper::tryAllocateMemory()) {
            return $this->_response(array(
                'error' => 'memdata',
                'amount' => Helper::getMemoryLimit()
            ));
        }

        return $this->_response(array(
            'result' => 'done',
            'version' => Config::buildAppManifestVersion($data['template'])
        ));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function renameTheme($data)
    {
        $oldThemeName = $data['oldThemeName'];
        $lowerOldThemeName = strtolower($oldThemeName);
        $newThemeName = $data['newThemeName'];
        $lowerNewThemeName = strtolower($newThemeName);

        $oldThemeDir = JPATH_SITE . '/templates/' . $lowerOldThemeName;
        $newThemeDir = JPATH_SITE . '/templates/' . $lowerNewThemeName;

        Helper::moveDir($oldThemeDir, $newThemeDir);
        // manifest correction
        $manifest = $newThemeDir . '/templateDetails.xml';
        $content = Helper::readFile($manifest);
        $xml = simplexml_load_string($content);
        $xml->name = $newThemeName;
        $path = $xml->config->fields['addfieldpath'];
        $xml->config->fields['addfieldpath'] = str_replace($lowerOldThemeName, $lowerNewThemeName, $path);
        foreach($xml->languages->language as $node) {
            $language = $node[0];
            $node[0] = str_replace($lowerOldThemeName, $lowerNewThemeName, $language);
        }
        // Save dom to xml file
        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            $data = $dom->saveXML();
        } else {
            $data = $xml->asXML();
        }
        Helper::writeFile($manifest, $data);
        // translation file correction
        Helper::renameFile($newThemeDir . '/language/en-GB/en-GB.tpl_' . $lowerOldThemeName . '.ini',
            $newThemeDir . '/language/en-GB/en-GB.tpl_' . $lowerNewThemeName . '.ini');

        //Changes the theme in database
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('title');
        $query->from('#__template_styles');
        $query->where('template=' . $db->quote($lowerOldThemeName));
        $db->setQuery($query);
        $title = $db->loadResult();

        $query = $db->getQuery(true);
        $query->select('manifest_cache, name');
        $query->from('#__extensions');
        $query->where('type=' . $db->quote('template')  . ' and element=' . $db->quote($lowerOldThemeName));
        $db->setQuery($query);
        $ret = $db->loadAssoc();
        $manifest_cache = $ret['manifest_cache'];
        $originalOldThemeName = $ret['name'];

        $query = $db->getQuery(true);
        $query->update('#__template_styles');
        $query->set('template=' . $db->quote($lowerNewThemeName));
        $query->set('title=' . $db->quote(str_replace($originalOldThemeName, $newThemeName, $title)));
        $query->where('template=' . $db->quote($lowerOldThemeName));
        $db->setQuery($query);
        $db->query();

        $query = $db->getQuery(true);
        $query->update('#__extensions');
        $query->set('name=' . $db->quote($newThemeName));
        $query->set('element=' . $db->quote($lowerNewThemeName));
        $query->set('manifest_cache=' . $db->quote(str_replace($originalOldThemeName, $newThemeName, $manifest_cache)));
        $query->where('type=' . $db->quote('template')  . ' and element=' . $db->quote($lowerOldThemeName));
        $db->setQuery($query);
        $db->query();
        if ($oldThemeDir !== $newThemeDir)
            Helper::removeDir($oldThemeDir);
        return $this->_response('renamed');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getTemplates($data)
    {
        return $this->_response(array(
            'templates' => Config::getThemeTemplates(true),
            'contentIsImported' => Config::contentIsImported(),
            'startPage' => Config::getStartPage()
            ));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getThemes($data = array())
    {
        $current = dirname(JURI::current()) . '/';
        $root = dirname(dirname(dirname($current)));
        $result = array();
        $items = $this->_getThemesList();
        foreach($items as $item) {
            $themeDir = JPATH_SITE . '/templates/' . $item->element;
            if (!file_exists($themeDir . '/app/project.json'))
                continue;
            $thumbnailDir = file_exists($themeDir . '/template_thumbnail.png') ?
                $root . '/templates/' . $item->element . '/template_thumbnail.png' : '';
            $versionPath = $themeDir . '/app/themler.version';
            $version = file_exists($versionPath) ? '&ver=' . Helper::readFile($versionPath) : '';
            $openUrl = $root . '/templates/' . $item->element .
                '/app/index.php?auto_authorization=1&username=&password=&domain=' . $version;
            $result[$item->id] = array(
                'themeName' => $item->element,
                'thumbnailUrl' => $thumbnailDir,
                'openUrl' => $openUrl,
                'isActive' => $this->themeIsActive($item->element)
            );
        }
        return $this->_response($result);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getFiles($data)
    {
        $mask = $data['mask'];
        $filter = $data['filter'];
        $template = $data['template'];

        $templateDir = JPATH_SITE . '/templates/' . $template;
        
        $flags = 0;
        if (defined('GLOB_BRACE'))
            $flags = GLOB_BRACE;
            
        $files = $this->_getFiles($templateDir . '/{' . $mask . '}', $flags);

        $out_files = array();
        foreach ($files as $file) {
            $name = str_replace($templateDir, '', $file);
            $name = preg_replace('#[\/]+#', '/', $name);
            $info = pathinfo($file);
            $filename = $info['basename'];
            if (is_dir($file) || preg_match("#editor\.css|print\.css|ie\.css#", $filename) || ($filter && preg_match("#$filter#", $filename))
                || preg_match("#^\/editor\/#", $name)) {
                continue;
            }

            $out_files[$name] = Helper::readFile($file);
        }

        return $this->_response(array('files' => $out_files));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function setFiles($data)
    {
        $files = array();

        if (isset($data['files'])) {
            $files = json_decode($data['files'], true);
            $response = 'ok';
        } else {
            $chunk = new Chunk();
            $chunk->save($this->_getChunkInfo($data));

            if ($chunk->last()) {
                $files = json_decode($chunk->complete(), true);
                $response = 'done';
            } else {
                return $this->_response('processed');
            }
        }

        if ($files && count($files)) {
            $template = $data['template'];
            $templateDir = JPATH_SITE . '/templates/' . $template;
            foreach ($files as $filename => $content) {
                Helper::writeFile($templateDir . $filename, $content, LOCK_EX);
            }
        }

        return $this->_response($response);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function zip($data)
    {
        $templateName = $data['template'];
        $info = $this->_getChunkInfo($data);

        $chunk = new Chunk();
        $chunk->save($info);

        if (!$chunk->last()) {
            return $this->_response(array('result' => 'processed'));
        }

        $data = json_decode($chunk->complete(), true);
        if (!isset($data['fso']))
            trigger_error('Fso not found' . print_r($data, true), E_USER_ERROR);

        $zipFiles = $this->_convertFsoToZipFiles($data['fso']);
        if (null === $zipFiles) {
            trigger_error('Zip files not found' . print_r($zipFiles, true), E_USER_ERROR);
        }

        $tmp = JPATH_SITE . '/templates/' . $templateName . '/tmp';
        Helper::createDir($tmp);

        jimport('joomla.filesystem.archive');
        jimport('joomla.filesystem.file');
        $archivePath = $tmp . '/' . 'zip-data.zip';
        $zip = JArchive::getAdapter('zip');
        $zip->create($archivePath, $zipFiles);
        $result = array('result' => 'done', 'data' => base64_encode(Helper::readFile($archivePath)));
        Helper::removeDir($tmp);
        return $this->_response($result);
    }

    /**
     * @param $data
     * @return array|mixed
     */
    public function unZip($data)
    {
        $templateName = $data['template'];
        $tmp = JPATH_SITE . '/templates/' . $templateName . '/tmp';
        Helper::createDir($tmp);

        $filename = isset($data['filename']) ? $data['filename'] : '';

        if ('' === $filename) {
            $result = array(
                'status' => 'error',
                'message' => 'Empty file name'
            );
        } else {
            $uploadPath = $tmp . '/' . $filename;
            $isLast = isset($data['last']) ? $data['last'] : '';
            $result = $this->_uploadFileChunk($uploadPath, $isLast);

            if ($result['status'] === 'done') {
                $info = pathinfo($uploadPath);
                $suffix = isset($info['extension']) ? '.'.$info['extension'] : '';
                $fileName =  basename($uploadPath, $suffix);
                $extractDir = dirname($uploadPath) . '/' . $fileName;
                Helper::createDir($extractDir);

                if (version_compare(JVERSION, '3.0', '<')) {
                    jimport('joomla.filesystem.archive');
                    $result = JArchive::extract($uploadPath, $extractDir);
                    if ($result === false) {
                        return array(
                            'status' => 'error',
                            'message' => 'Invalid type.'
                        );
                    }
                } else {
                    try {
                        JArchive::extract($uploadPath, $extractDir);
                    } catch (Exception $e) {
                        return array(
                            'status' => 'error',
                            'message' => $e->getMessage()
                        );
                    }
                }
                $fso = $this->_convertZipFilesToFso($extractDir);
                Helper::removeDir($tmp);
                $result['fso'] = $fso;
            }
        }
        return $this->_response($result);
    }

    /**
     * @param $path
     * @return array
     */
    public function _convertZipFilesToFso($path) {
        $result = array();
        if (is_file($path)) {
            $type = 'text';
            $content = Helper::readFile($path);
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, array('jpg', 'jpeg', 'bmp', 'png', 'gif'))) {
                $type = 'data';
                $content = base64_encode($content);
            }
            return array('type' => $type, 'content' => $content);
        }

        if (is_dir($path)) {
            $result = array('type' => 'dir', 'items' => array());
            if ($dh = opendir($path)) {
                while (($name = readdir($dh)) !== false) {
                    if (in_array($name, array('.', '..'))) {
                        continue;
                    }
                    $result['items'][$name] = $this->_convertZipFilesToFso($path . '/' . $name);
                }
                closedir($dh);
            }
        }

        return $result;
    }

    /**
     * @param $fso
     * @param string $relative
     * @return array|null
     */
    private function _convertFsoToZipFiles($fso, $relative = '')
    {
        static $zipFiles = array();

        if(!array_key_exists('items', $fso) || !is_array($fso['items'])) {
            return null;
        }
        foreach ($fso['items'] as $name => $file) {
            if(isset($file['content']) && isset($file['type'])) {
                switch ($file['type']) {
                    case 'text':
                        $zipFiles[] = array('name' => $relative . $name, 'data' => $file['content']);
                        break;
                    case 'data':
                        $zipFiles[] = array('name' => $relative . $name, 'data' => base64_decode($file['content']));
                        break;
                }
            } elseif(isset($file['items']) && isset($file['type'])) {
                $this->_convertFsoToZipFiles($file, $relative . $name . '/');
            }
        }
        return $zipFiles;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function canRename($data)
    {
        $templatesDir = JPATH_SITE . '/templates/';
        $themeName = strtolower($data['themeName']);
        if (file_exists($templatesDir . $themeName)) {
            return $this->_response(false);
        } else {
            return $this->_response(true);
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getTheme($data)
    {
        $userThemeName  = $data['themeName'];
        $templateName = $data['template'];
        $includeEditor = $data['includeEditor'] == 'false' ? false : true;

        $originalDir = JPATH_SITE . '/templates/' . $templateName;

        $archive = new Archive();

        return $archive->getArchive($originalDir, $templateName, $userThemeName, $includeEditor);
    }

    /**
     * @param $data
     * @return string
     */
    public function downloadTheme($data) {
        $templateObject = $this->getTemplateObject($data['templateId']);
        $originalDir = JPATH_SITE . '/templates/' . $templateObject->element;
        $archive = new Archive();
        return $archive->getArchive($originalDir, $templateObject->name, $templateObject->name, true);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function uploadImage($data)
    {
        $filename = isset($data['filename']) ? $data['filename'] : '';
        $desImagesFolder = 'images/designer/';

        if ('' === $filename) {
            $result = array(
                'status' => 'error',
                'message' => 'Empty file name'
            );
        } else {
            $templateName = $data['template'];
            $themeDir = JPATH_SITE . '/templates/' . $templateName;
            $uploadPath = $themeDir . '/editor/' .  $desImagesFolder . $filename;

            $isLast = isset($data['last']) ? $data['last'] : '';
            $result = $this->_uploadFileChunk($uploadPath, $isLast);

            if ($result['status'] === 'done') {
                $current = dirname(JURI::current()) . '/';
                $root = dirname(dirname(dirname($current)));
                $result['url'] = $root . '/templates/' . $templateName . '/editor/' . $desImagesFolder . $filename;
            }
        }

        return $this->_response($result);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function uploadTheme($data)
    {
        $filename = isset($data['filename']) ? $data['filename'] : '';
        $desThemesFolder = 'themes/';

        if ('' === $filename) {
            $result = array(
                'status' => 'error',
                'message' => 'Empty file name'
            );
        } else {
            $templateName = $data['template'];
            $themeDir = JPATH_SITE . '/templates/' . $templateName;
            $uploadPath = $themeDir . '/editor/' .  $desThemesFolder . $filename;

            $isLast = isset($data['last']) ? $data['last'] : '';
            $result = $this->_uploadFileChunk($uploadPath, $isLast);

            if ($result['status'] === 'done') {
                try {
                    $result = $this->_installTheme($uploadPath);
                } catch(Exception $e) {
                    $result = array(
                        'status' => 'error',
                        'message' => $e->getMessage()
                    );
                }
                Helper::removeDir($themeDir . '/editor/' .  $desThemesFolder);
            }
        }

        return $this->_response($result);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function copyTheme($data)
    {
        $id = $data['templateId'];
        $sourceThemeName = '';
        $themeNames = array();
        $items = $this->_getThemesList();
        foreach($items as $item) {
            if ($id == $item->id) {
                $sourceThemeName = $item->name;
            }
            $themeNames[] = $item->element;
        }
        if ('' === $sourceThemeName)
            trigger_error('Source theme not found', E_USER_ERROR);

        $newThemeName = $data['newThemeName'];
        if ('' === $newThemeName)
            $newThemeName = $sourceThemeName;
        $newThemeName = $this->_getNewName($newThemeName, $themeNames);

        $lowerOldThemeName = strtolower($sourceThemeName);
        $lowerNewThemeName = strtolower($newThemeName);

        $sourceThemeDir = JPATH_SITE . '/templates/' . $lowerOldThemeName;
        $newThemeDir = JPATH_SITE . '/templates/' . $lowerNewThemeName;
        Helper::copyDir($sourceThemeDir, $newThemeDir);

        // manifest correction
        $manifest = $newThemeDir . '/templateDetails.xml';
        $content = Helper::readFile($manifest);
        $xml = simplexml_load_string($content);
        $xml->name = $newThemeName;
        $path = $xml->config->fields['addfieldpath'];
        $xml->config->fields['addfieldpath'] = str_replace($lowerOldThemeName, $lowerNewThemeName, $path);
        foreach($xml->languages->language as $node) {
            $language = $node[0];
            $node[0] = str_replace($lowerOldThemeName, $lowerNewThemeName, $language);
        }
        // Save dom to xml file
        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            $data = $dom->saveXML();
        } else {
            $data = $xml->asXML();
        }
        Helper::writeFile($manifest, $data);
        // translation file correction
        Helper::renameFile($newThemeDir . '/language/en-GB/en-GB.tpl_' . $lowerOldThemeName . '.ini',
            $newThemeDir . '/language/en-GB/en-GB.tpl_' . $lowerNewThemeName . '.ini');

        //Changes the theme in database
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('params');
        $query->from('#__template_styles');
        $query->where('template=' . $db->quote($lowerOldThemeName));
        $db->setQuery($query);
        $params = $db->loadResult();

        $query = $db->getQuery(true);
        $query->insert('#__template_styles');
        $query->set('template=' . $db->quote($lowerNewThemeName));
        $query->set('client_id=0');
        $query->set('home=0');
        $query->set('title=' . $db->quote($newThemeName . ' - Default'));
        $query->set('params=' . $db->quote($params));
        $db->setQuery($query);
        $db->query();

        $query = $db->getQuery(true);
        $query->select('manifest_cache, params, name');
        $query->from('#__extensions');
        $query->where('type=' . $db->quote('template')  . ' and element=' . $db->quote($lowerOldThemeName));
        $db->setQuery($query);
        $ret = $db->loadAssoc();
        $ext_cache = $ret['manifest_cache'];
        $ext_params = $ret['params'];
        $ext_name = $ret['name'];

        $query = $db->getQuery(true);
        $query->insert('#__extensions');
        $query->set('name=' . $db->quote($newThemeName));
        $query->set('type=' . $db->quote('template'));
        $query->set('element=' . $db->quote($lowerNewThemeName));
        $query->set('client_id=0');
        $query->set('enabled=1');
        $query->set('access=1');
        $query->set('protected=0');
        $query->set('manifest_cache=' . $db->quote(str_replace($ext_name, $newThemeName, $ext_cache)));
        $query->set('params=' . $db->quote($ext_params));
        $db->setQuery($query);
        $db->query();

        return $this->_response('copied');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function makeThemeAsActive($data)
    {
        $themeId = $data['themeId'];
        if ($themeId) {
            $templateObject = $this->getTemplateObject($themeId);
            $styleId = Config::getStyleObject($templateObject->element)->id;
        } else {
            $styleId = Config::getStyleObject()->id;
        }
        // Include dependancies
        jimport('joomla.application.component.controller');
        // Declaration contstants
        define('JPATH_COMPONENT', JPATH_BASE . '/components/com_templates');
        define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/com_templates');
        define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_templates');
        // Set of variables for controller
        JRequest::setVar('task', 'styles.setDefault');
        JRequest::setVar('cid', $styleId);
        JRequest::setVar(JSession::getFormToken(), '1');
        // Theme activation
        $controller = JControllerLegacy::getInstance('Templates', array('base_path' => JPATH_COMPONENT));
        $controller->execute(JRequest::getCmd('task'));
        return $this->_response('activated');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function removeTheme($data)
    {
        $app = JFactory::getApplication('administrator');
        define('JPATH_COMPONENT', JPATH_BASE . '/components/com_installer');
        define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/com_installer');
        define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_installer');

        // Create token
        $session = JFactory::getSession();
        $token = $session::getFormToken();

        // Load translations
        $lang = JLanguage::getInstance('en-GB');
        $lang->load('lib_joomla', JPATH_ADMINISTRATOR);
        $lang->load('com_installer', JPATH_BASE, null, false, true) ||
        $lang->load('com_installer', JPATH_COMPONENT, null, false, true);

        if (version_compare(JVERSION, '3.0', '<')) {
            JFactory::$language = $lang;
        } else {
            $app->loadLanguage($lang);
            JFactory::$language = $app->getLanguage();
        }

        JRequest::setVar('task', 'manage.remove');
        JRequest::setVar('cid', $data['templateId']);
        JRequest::setVar($token, '1');

        $controller	= JControllerLegacy::getInstance('Installer');
        $controller->execute(JRequest::getCmd('task'));

        $successMessage = JText::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_TEMPLATE'));
        $errors = array();
        $messages = $app->getMessageQueue();
        foreach($messages as $msg) {
            if ($msg['message'] === $successMessage){
                return $this->_response(array(
                    'status' => 'done',
                    'message' => 'theme removed'
                ));
            }
            if ($msg['message'])
                $errors[] = $msg['message'];
        }
        if (count($errors) > 0)
            $errorText = implode("<br />", $errors);
        else
            $errorText = 'Uninstalling template failed';
        return $this->_response(array(
            'status' => 'error',
            'message' => $errorText
        ));
    }

    /**
     * @param $data
     */
    public function importContent($data)
    {
        $id = $data['id'];
        $templateName = $data['template'];
        $themeDir = JPATH_SITE . '/templates/' . $templateName;

        require_once $themeDir . '/library/' . 'Designer.php';
        Designer::load('Designer_Data_Loader');

        $loader = new Designer_Data_Loader();
        $loader->load($themeDir . '/data/data.xml');
        $result = $loader->execute(array('action' => 'run', 'id' => $id));

        return $this->_response('imported');
    }

    /**
     * @param $path
     * @return mixed
     */
    private function _installTheme($zipFile)
    {
        if (!file_exists($zipFile)) {
            return array(
                'status' => 'error',
                'message' => 'File ' . $zipFile  . ' not found.'
            );
        }

        $info = pathinfo($zipFile);
        $suffix = isset($info['extension']) ? '.'.$info['extension'] : '';
        $fileName =  basename($zipFile, $suffix);
        $extractDir = dirname($zipFile) . '/' . $fileName;
        Helper::createDir($extractDir);

        $app = JFactory::getApplication('administrator');

        define('JPATH_COMPONENT', JPATH_BASE . '/components/com_installer');
        define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/com_installer');
        define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_installer');

        // Create token
        $session = JFactory::getSession();
        $token = $session::getFormToken();

        // Load translations
        $lang = JLanguage::getInstance('en-GB');
        $lang->load('lib_joomla', JPATH_ADMINISTRATOR);
        $lang->load('com_installer', JPATH_BASE, null, false, true) ||
        $lang->load('com_installer', JPATH_COMPONENT, null, false, true);

        if (version_compare(JVERSION, '3.0', '<')) {
            jimport('joomla.filesystem.archive');
            $result = JArchive::extract($zipFile, $extractDir);
            if ($result === false) {
                return array(
                    'status' => 'error',
                    'message' => 'Invalid type.'
                );
            }
            JRequest::setVar('installtype', 'folder');
            JRequest::setVar('task', 'install.install');
            JRequest::setVar('install_directory', $extractDir);
            // Register the language object with JFactory
            JFactory::$language = $lang;
            JRequest::setVar($token, 1, 'post');
        } else {
            try {
                JArchive::extract($zipFile, $extractDir);
            } catch (Exception $e) {
                return array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
            }
            $app->input->set('installtype', 'folder');
            $app->input->set('task', 'install.install');
            $app->input->set('install_directory', $extractDir);
            // Register the language object with JFactory
            $app->loadLanguage($lang);
            JFactory::$language = $app->getLanguage();
            $app->input->post->set($token, 1);
        }
        $pathManifest = $extractDir . '/templateDetails.xml';
        if (!file_exists($pathManifest)) {
            return array(
                'status' => 'error',
                'message' => 'Only Joomla templates are allowed.'
            );
        }
        $items = $this->_getThemesList();
        $themeNames = array();
        foreach($items as $item) {
            $themeNames[] = $item->element;
        }
        $xml = simplexml_load_string(Helper::readFile($pathManifest));
        $currentThemeName = (string)$xml->name;

        $newThemeName = $this->_getNewName($currentThemeName, $themeNames);

        if ($currentThemeName !== $newThemeName) {
            Helper::renameFile($extractDir . '/language/en-GB/en-GB.tpl_' . $currentThemeName . '.ini',
                $extractDir . '/language/en-GB/en-GB.tpl_' . $newThemeName . '.ini');
            $xml->name = $newThemeName;
            $path = $xml->config->fields['addfieldpath'];
            $xml->config->fields['addfieldpath'] = str_replace($currentThemeName, $newThemeName, $path);
            foreach($xml->languages->language as $node) {
                $language = $node[0];
                $node[0] = str_replace($currentThemeName, $newThemeName, $language);
            }
            if (class_exists('DOMDocument')) {
                $dom = new DOMDocument('1.0', 'utf-8');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($xml->asXML());
                Helper::writeFile($pathManifest, $dom->saveXML());
            } else {
                Helper::writeFile($pathManifest, $xml->asXML());
            }
        }
        // Execute installing
        $controller	= JControllerLegacy::getInstance('Installer');
        $controller->execute(JRequest::getCmd('task'));

        $messages = $app->getMessageQueue();

        $successMessage = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_TEMPLATE'));

        $errors = array();
        foreach($messages as $msg) {
            if ($msg['message'] === $successMessage) {
                return array(
                    'status' => 'done',
                    'message' => $successMessage
                );
            }
            $errors[] = $msg['message'];
        }
        if (count($errors) > 0)
            $errorText = implode("<br />", $errors);
        else
            $errorText = 'Installing template failed';

        return array(
            'status' => 'error',
            'message' => $errorText
        );
    }

    /**
     * @param $name
     * @param $existsNames
     * @return mixed
     */
    private function _getNewName($name, $existsNames)
    {
        $i = 1;
        if (preg_match('/[0-9]+$/', $name, $matches)) {
            $i = ++$matches[0];
        };
        $newName = $name;
        while(in_array(strtolower($newName), $existsNames)) {
            $newName = preg_replace('/[0-9]*$/', $i, $newName, 1);
            $i++;
        }
        return $newName;
    }

    /**
     * @return mixed
     */
    private function _getThemesList()
    {
        $db = JFactory::getDBO();
        $query	= $db->getQuery(true);
        $query->from($db->quoteName('#__extensions'));
        $query->select(array('extension_id AS id', 'name', 'element', 'client_id'));
        $query->where('type = \'template\'');
        $query->where('client_id = \'0\'');
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getTemplateObject($id)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions');
        $query->where('extension_id=\'' . $id . '\'');
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * @param $name
     * @return bool
     */
    public function themeIsActive($template)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('home');
        $query->from('#__template_styles');
        $query->where('template=\'' . $template . '\'');
        $query->where('home=1');
        $db->setQuery($query);
        $list = $db->loadAssocList();
        return count($list) > 0;
    }

    /**
     * @param $uploadPath
     * @param $isLast
     * @return array
     */
    private function _uploadFileChunk($uploadPath, $isLast)
    {
        if (!isset($_FILES['chunk']) || !file_exists($_FILES['chunk']['tmp_name'])) {
            return array(
                'status' => 'error',
                'message' => 'Empty chunk data'
            );
        }

        $contentRange = $_SERVER['HTTP_CONTENT_RANGE'];
        if ('' === $contentRange && '' === $isLast) {
            return array(
                'status' => 'error',
                'message' => 'Empty Content-Range header'
            );
        }

        $rangeBegin = 0;

        if ($contentRange) {
            $contentRange = str_replace('bytes ', '', $contentRange);
            list($range, $total) = explode('/', $contentRange);
            list($rangeBegin, $rangeEnd) = explode('-', $range);
        }

        $tmpPath = JPATH_SITE . '/tmp/' . basename($uploadPath);
        Helper::createDir(dirname($tmpPath));

        $f = fopen($tmpPath, 'c');

        if (flock($f, LOCK_EX)) {
            fseek($f, (int) $rangeBegin);
            fwrite($f, Helper::readFile($_FILES['chunk']['tmp_name']));

            flock($f, LOCK_UN);
            fclose($f);
        }

        if ($isLast) {
            if (file_exists($uploadPath)) {
                Helper::deleteFile($uploadPath);
            }
            Helper::createDir(dirname($uploadPath));
            Helper::renameFile($tmpPath, $uploadPath);

            return array(
                'status' => 'done'
            );
        } else {
            return array(
                'status' => 'processed'
            );
        }
    }

    /**
     * @param $mask
     * @param $flags
     * @return array
     */
    private function _getFiles($mask, $flags)
    {
        $files = glob($mask, $flags);
        if (!is_array($files)) {
            $files = array();
        }
        
        $bitwiseOrFlags = 0;
        if (defined('GLOB_ONLYDIR') && defined('GLOB_NOSORT'))
            $bitwiseOrFlags = GLOB_ONLYDIR | GLOB_NOSORT;
            
        $subdirs = glob(dirname($mask) . '/*', $bitwiseOrFlags);
        if (is_array($subdirs)) {
            foreach ($subdirs as $dir)
            {
                $files = array_merge($files, $this->_getFiles($dir . '/' . basename($mask), $flags));
            }
        }

        return $files;
    }

    /**
     * @param $data
     * @return array
     */
    private function _getChunkInfo($data)
    {
        return array(
            'id' => isset($data['id']) ? $data['id'] : '',
            'content' =>  isset($data['content']) ? $data['content'] : '',
            'current' =>  isset($data['current']) ? $data['current'] : '',
            'total' =>  isset($data['total']) ? $data['total'] : '',
            'encode' => !empty($data['encode']),
            'blob' => !empty($data['blob'])
        );
    }

    /**
     * @param $themeId
     * @param $params
     */
    private function _setPatameters($themeId, $params)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('params')->from('#__template_styles')->where('id=' . $query->escape($themeId));
        $db->setQuery($query);
        $parameters = $this->_stringToParams($db->loadResult());

        foreach ($params as $key => $value)
            $parameters[$key] = $value;

        $query = $db->getQuery(true);
        $query->update('#__template_styles')->set(
            $db->quoteName('params') . '=' .
                $db->quote($this->_paramsToString($parameters))
        )->where('id=' . $query->escape($themeId));

        $db->setQuery($query);
        $db->query();
    }

    /**
     * @param $params
     * @return mixed
     */
    private function _paramsToString($params)
    {
        $registry = new JRegistry();
        $registry->loadArray($params);
        return $registry->toString();
    }

    /**
     * @param $string
     * @return mixed
     */
    private function _stringToParams($string)
    {
        $registry = new JRegistry();
        $registry->loadString($string);
        return $registry->toArray();
    }

    /**
     * @param $result
     * @return mixed
     */
    private function _response($result)
    {
        if (is_string($result)) {
            $result = array('result' => $result);
        }
        return json_encode($result);
    }
}