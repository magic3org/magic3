<?php
require_once('PlaceHoldersStorage.php');
require_once('Preview.php');

class ThemeBuilder
{
    public $themeDir;
    public $editorDir;

    private $_result = array();

    private $_templateName;
    private $_customImagesInfo = array();
    private $_preview;
    private $_newFiles;
    /**
     * @param $templateName
     * @param $media
     */
    public function __construct($templateName, $media)
    {
        $this->themeDir     = JPATH_SITE . '/templates/' . $templateName;
        $this->editorDir    = $this->themeDir . '/editor';

        $this->_preview = new Preview($templateName);

        $this->_newFiles = new FileDiffs($this->themeDir . '/app/newfiles.json');

        // save icon fonts to result
        $this->addIconFonts($media['icons']);
        // save thumbnails to result
        $this->addThumbnails($media['thumbnails']);
        // save custom images to result
        $this->addCustomImages($media['images']);

        $this->_publish = false;

        $this->_templateName = $templateName;
    }

    /**
     * @param $storage
     */
    public function export($storage)
    {
        $this->buildTemplateFiles($storage);
        $this->updatePreviewTheme();
    }

    /**
     * @param $thumbnails
     */
    public function addThumbnails($thumbnails)
    {
        if (!$thumbnails) return;

        // template_preview.png
        $preview_base64 = str_replace('data:image/png;base64,', '', $thumbnails[0]['data']);
        $this->_result['/' . $thumbnails[0]['name']] = base64_decode($preview_base64);
        // template_thumbnail.png
        $thumbnail_base64 = str_replace('data:image/png;base64,', '', $thumbnails[1]['data']);
        $this->_result['/' . $thumbnails[1]['name']] = base64_decode($thumbnail_base64);
    }

    /**
     * @param $icons
     */
    private function addIconFonts($icons)
    {
        if (!$icons) return;

        foreach ($icons as $name => $content) {
            $this->_result['/css/' . $name] = base64_decode($content);
        }
    }

    /**
     * @param $images
     */
    private function addCustomImages($images)
    {
        if (!$images) return;

        foreach ($images as $name => $data) {
            $validName = preg_replace('/[^a-z0-9_\.]/i', '', $name);
            $path = '/images/designer/' . $validName;
            $info = array('name' => $validName, 'path' => $path, 'content' => '');

            if ($data) {
                $content = '[DELETED]' !== $data ? base64_decode($data) : $data;
                $info['content'] = $content;
                $this->_result[$path] = $content;
            }
            $this->_customImagesInfo[$name] = $info;
    }
    }

    /**
     * @param $themeStorage
     */
    private function buildTemplateFiles($themeStorage)
    {
        $timeLogging = LoggingTime::getInstance();
        $timeLogging->start('[PHP] Build template files');
        // create placeholders collection
        $placeholders = new PlaceHoldersStorage();
        //preview theme
        $placeholders->set('theme', $this->_templateName);
        // copy page control files to result
        foreach($themeStorage as $name => $content) {
            $info = pathinfo($name);
            $dirNameParts = explode('/', $info['dirname']);
            $type = '';
            if (count($dirNameParts) > 2 && 'html' == $dirNameParts[1]) {
                $componentParts = explode('_', $dirNameParts[2]);
                $type = array_shift($componentParts);
            }
            $parts      = explode('.', $info['basename']);
            $fname      = $parts[0];
            $extension  = array_pop($parts);
            // change extension for templates files
            if (('\\' === $info['dirname'] || '/' === $info['dirname']) && '/index.html' !== $name && 'html' === $extension) {
                $extension = 'php';
                $name = '/templates/' . $fname . '.' . $extension;
            }
            // exclude some files from result
            if (preg_match('/^\/fragments\//', $name)) {
                continue;
            }
            // rename translation file to theme name
            if (preg_match('/^\/language\/en-GB\/en-GB.tpl_template.ini/', $name)) {
                $this->_result['/language/en-GB/en-GB.tpl_' . $this->_templateName . '.ini'] = $content;
                Helper::createDir(JPATH_SITE . '/language/en-GB');
                Helper::writeFile(JPATH_SITE . '/language/en-GB/en-GB.tpl_' . $this->_templateName . '.ini', $content);
                continue;
            }
            switch ($name) {
                case '/style.css':
                    $name = '/css/template.css';
                    foreach($this->_customImagesInfo as $guid => $info) {
                        $p = '..' . $info['path'];
                        if (!file_exists($this->editorDir . $info['path']) && '' === $info['content'])
                            $p = '../' . $p;
                        $content = str_replace('url(' . $guid . ')','url(' . $p . ')', $content);
                    }
                    break;
                case '/bootstrap.css':
                    $name = '/css/bootstrap.css';
                    break;
                default:
                    break;
            }

            if ('php' === $extension) {
                // change guids for images
                foreach($this->_customImagesInfo as $guid => $info) {
                    if (!file_exists($this->editorDir . $info['path']) && '' === $info['content']) {
                        $p = '<?php echo JURI::base() . \'templates/\' . JFactory::getApplication()->getTemplate(); ?>' . $info['path'];
                    } else {
                        $p = '<?php echo JURI::base() . \'templates/\' . JFactory::getApplication()->getTemplate(); ?>/editor' . $info['path'];
                    }
                    $content = str_replace('url(' . $guid . ')', $p, $content);
                }
                $content = preg_replace('#url\((https?://[^\)]+)\)#', '$1', $content);
            }
            if (!preg_match('/^\/app/', $name))
                $content = $placeholders->replace($content);
            if (isset($this->_result[$name])) {
                $this->_result[$name] .= $content;
            } else {
                $this->_result[$name] = $content;
            }
        }
        $timeLogging->end('[PHP] Build template files');
    }

    private function updatePreviewTheme()
    {
        $timeLogging = LoggingTime::getInstance();
        $timeLogging->start('[PHP] Update preview theme');

        foreach($this->_result as $path => $content) {
            $pos = strrpos($path, "/"); // find position last slash
            //check exists new added control file
            $mainPath = $this->themeDir . $path;
            if (preg_match('/^\/html/', $path) && !file_exists($mainPath)) {
                $currentDir = $this->themeDir . substr($path, 0, $pos + 1);
                Helper::createDir($currentDir);
                Helper::writeFile($mainPath, $content);
            }
            $fullPath = $this->editorDir . $path;
            if ('[DELETED]' === $content) {
                $this->removeDeletedFile($fullPath);
                $this->_preview->removeKey($path);
                $this->_newFiles->refresh(array($path => '[DELETED]'));
                continue;
            } else {
                if (false !== $pos) {
                    $currentDir = $this->editorDir . substr($path, 0, $pos + 1);
                    Helper::createDir($currentDir);
                }
                Helper::writeFile($fullPath, $content);
                $this->_newFiles->refresh(array($path => '[NEW]'));
            }
        }
        $this->_imagesProcessing($this->editorDir);
        $this->_newFiles->save();
        $timeLogging->end('[PHP] Update preview theme');
    }

    public function updateOriginalTheme()
    {
        $timeLogging = LoggingTime::getInstance();
        $timeLogging->start('[PHP] Copying or deleting new files');
        $newFiles = $this->_newFiles->toArray();
        foreach($newFiles as $path => $content) {
            $fullThemePath = $this->themeDir . $path;
            $fullEditorPath = $this->editorDir . $path;
            if ('[DELETED]' === $content) {
                Helper::deleteFile($fullThemePath);
            } else {
                if (!file_exists($fullEditorPath))
                    continue;
                $content = Helper::readFile($fullEditorPath);
                $info = pathinfo($path);
                $ext = isset($info['extension']) ? $info['extension'] : '';
                $pos = strrpos($path, "/");
                if (false !== $pos) {
                    $currentDir = $this->themeDir . substr($path, 0, $pos + 1);
                    Helper::createDir($currentDir);
                }
                if (!preg_match('/^\/app\//', $path) && 'php' === $ext) {

                    $old = array('<?php $document = JFactory::getDocument(); echo $document->templateUrl; ?>/images',
                        '<?php echo JFactory::getDocument()->baseurl . \'/templates/\' . JFactory::getApplication()->getTemplate(); ?>/images');
                    $new = '<?php echo JURI::base() . \'templates/\' . JFactory::getApplication()->getTemplate(); ?>/images';
                    $content = str_replace($old[0], $new, $content);
                    $content = str_replace($old[1], $new, $content);

                    $content = str_replace('?>/editor/images/designer/', '?>/images/designer/', $content);
                    //change preview file
                    Helper::writeFile($this->editorDir . $path, $content);

                    $content = $this->_preview->removeDataId($path, $content);
                }
                if (preg_match('/^\/css\/template\.css/', $path)) {
                    $content = str_replace('url(../../images/designer/', 'url(../images/designer/', $content);
                    $previewContent = str_replace('url(../images/designer/', 'url(../../images/designer/', $content);
                    Helper::writeFile($this->editorDir . '/css/template.css', $previewContent);

                    $content = preg_replace('|url\(([\"\']{0,1})\.\./\.\./\.\./\.\./images/|', 'url($1../../../images/', $content);
                }
                if (preg_match('/functions\.php$/', $path))
                    $content = str_replace('\'is_preview\' => true', '\'is_preview\' => false', $content);
                if (preg_match('/script\.js$/', $path))
                    $content = str_replace('var PREVIEW = true', 'var PREVIEW = false', $content);
                Helper::writeFile($fullThemePath, $content);
            }
        }
        $this->_newFiles->destroy();
        $timeLogging->end('[PHP] Copying or deleting new files');

        // build print.css and editor.css
        $timeLogging->start('[PHP] Build additional css files');
        $cssContent = '';
        $cssDir = $this->themeDir . '/css';
        $cssEditorDir = $this->editorDir . '/css';
        if (file_exists($cssEditorDir . '/print.css')) {
            if (file_exists($cssEditorDir . '/bootstrap.css')) {
                $cssContent .= Helper::readFile($cssEditorDir . '/bootstrap.css');
            }
            $cssContent .= Helper::readFile($cssEditorDir . '/print.css');
        }
        if ($cssContent) {
            $cssContent = preg_replace('/(border-|color|background-)[^:]*:\s*[^;]*;/', '', $cssContent);
            Helper::writeFile($cssDir . '/print.css', $cssContent);
        }
        $timeLogging->end('[PHP] Build additional css files');

        // remove images from preview
        Helper::removeDir($this->editorDir . '/images/designer', false);

        $this->_imagesProcessing($this->themeDir, true);

        $timeLogging->start('[PHP] Build manifest');
        $this->_buildManifest($this->themeDir);
        $timeLogging->end('[PHP] Build manifest');

        $timeLogging->start('[PHP] Remove min css files');
        Helper::deleteFile($this->themeDir . '/css/template.min.css');
        Helper::deleteFile($this->themeDir . '/css/bootstrap.min.css');
        $timeLogging->end('[PHP] Remove min css files');

        // save new control positions for preview
        $timeLogging->start('[PHP] Save diffs');
        $this->_preview->save();
        $timeLogging->end('[PHP] Save diffs');
    }

    private function _imagesProcessing($dir, $fullSave = false)
    {
        $names = array();
        if ($fullSave && file_exists($dir . '/images/designer')) {
            //retrieve names of images
            $flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir . '/images/designer', $flags));
            foreach ($iterator as $fileInfo) {
                $names[] = $fileInfo->getFilename();
            }
        }

        foreach($this->_customImagesInfo as $info) {
            $index = array_search($info['name'], $names);
            if (false !== $index)
                unset($names[$index]);
            $path = $info['path'];
            $fullPath = $dir . $path;
            $content = $info['content'];
            if (!$fullSave && file_exists($fullPath))
                $this->_newFiles->refresh(array($path =>
                    '[DELETED]' === $content ? '[DELETED]' : '[NEW]'));
            if ('' === $content)
                continue;
            $pos = strrpos($path, "/");
            if ('[DELETED]' === $content) {
                $this->removeDeletedFile($fullPath);
            } else {
                if (false !== $pos) {
                    $currentDir = $dir . substr($path, 0, $pos + 1);
                    Helper::createDir($currentDir);
                }
                Helper::writeFile($fullPath, $content);
            }
        }
        // delete unused images
        foreach($names as $name) {
            $filePath = $dir . '/images/designer/' . $name;
            if (file_exists($filePath))
                Helper::deleteFile($filePath);
        }
    }
    /**
     * @param $content
     * @return array
     */
    public function getTemplatePositions($content)
    {
        $result = array();
        preg_match_all('/\$view->position\(\'([^\']+)\'/', $content, $matches);
        foreach($matches[1] as $value){
            if (!in_array($value, $result))
                array_push($result, $value);
        }
        sort($result);
        return $result;
    }

    /**
     * @param $themeDir
     */
    private function _buildManifest($themeDir)
    {
        $file = $themeDir . '/templateDetails.xml';
        $manifest = Helper::readFile($file);

        //Build manifest, which contains set of files
        $placeholders = new PlaceHoldersStorage(array('theme' => $this->_templateName));
        $manifest = $placeholders->replace($manifest);

        $xml = simplexml_load_string($manifest);
        // Set theme metadata info
        $xml->name = $this->_templateName;
        $xml->creationDate = date('Y-m-d');
        $xml->version = '1.0';
        $xml->author = '';
        $xml->authorUrl = '';
        $xml->description = '';
        // Build list of folders
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $folders = JFolder::folders($themeDir, '.', false, true, array('tmp'));
        sort($folders);
        unset($xml->files->folder);
        foreach ($folders as $folder) {
            if (Helper::isEmptyDir($folder)) {
                Helper::removeDir($folder);
                continue;
            }
            $xml->files->addChild('folder', basename($folder));
        }
        // Build list of files
        $files = JFolder::files($themeDir);
        sort($files);
        unset($xml->files->file);
        foreach ($files as $value) {
            $xml->files->addChild('file', $value);
        }
        // Build list of positions
        unset($xml->positions->position);
        $files = JFolder::files($themeDir, '.php', true, true, array('editor', 'tmp', 'app'));
        $pageContent = '';
        foreach ($files as $value) {
            $pageContent .= JFile::read($value);
        }
        $elements  = $this->getTemplatePositions($pageContent);
        foreach ($elements as $element) {
            $xml->positions->addChild('position', $element);
        }

        if (JComponentHelper::getComponent('com_virtuemart', true)->enabled == '1') {
            $prodSlider = $xml->config->fields->addChild('fieldset');
            $prodSlider->addAttribute('name', 'productsslider');

            $field = $prodSlider->addChild('field');
            $field->addAttribute('name', 'slidersOptions');
            $field->addAttribute('type', 'hidden');
            $field->addAttribute('default', '');

            $field = $prodSlider->addChild('field');
            $field->addAttribute('name', 'sliders');
            $field->addAttribute('default', '');
            $field->addAttribute('type', 'sliders');
            $field->addAttribute('label', 'TPL_SLIDERSLIST_LABEL');
            $field->addAttribute('description', 'TPL_SLIDERSLIST_DESC');

            $names = array("desktops", "laptops", "tablets", "phones");
            $labels = array("TPL_DESKTOPS_WIDTH", "TPL_LAPTOPS_WIDTH", "TPL_TABLETS_WIDTH", "TPL_PHONES_WIDTH");
            $descriptions = array("TPL_DESKTOPS_WIDTH_DESCRIPTION", "TPL_LAPTOPS_WIDTH_DESCRIPTION", "TPL_TABLETS_WIDTH_DESCRIPTION", "TPL_PHONES_WIDTH_DESCRIPTION");
            $defaults = array("", "", "12", "");
            $values1 = array('Default', '1', '2', '3', '4', '8');
            $values2 = array('', '24', '12', '8', '6', '3');

            for($i = 0; $i < count($names); $i++) {
                $field = $prodSlider->addChild('field');
                $field->addAttribute('name', $names[$i]);
                $field->addAttribute('type', 'list');
                $field->addAttribute('default', $defaults[$i]);
                $field->addAttribute('label', $labels[$i]);
                $field->addAttribute('description', $descriptions[$i]);

                for($j = 0; $j < count($values1); $j++) {
                    $option = $field->addChild('option', $values1[$j]);
                    $option->addAttribute('value', $values2[$j]);
                }
            }

            $field = $prodSlider->addChild('field');
            $field->addAttribute('name', 'note');
            $field->addAttribute('type', 'note');
            $field->addAttribute('label', 'TPL_COMMON_PRODUCT_SLIDERS_OPTIONS');

            $field = $prodSlider->addChild('field');
            $field->addAttribute('name', 'itemsInRow');
            $field->addAttribute('type', 'text');
            $field->addAttribute('label', 'TPL_ITEMS_IN_SLIDE');
            $field->addAttribute('description', 'TPL_DESKTOPS_WIDTH_DESCRIPTION');
            $field->addAttribute('default', '2');
        }

        // Save dom to xml file
        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            Helper::writeFile($themeDir . '/templateDetails.xml', $dom->saveXML());
        } else {
            Helper::writeFile($themeDir . '/templateDetails.xml', $xml->asXML());
        }
    }

    /**
     * @param $filePath
     */
    private function removeDeletedFile($filePath)
    {
        $info  = pathinfo($filePath);
        $dirName = $info['dirname'];
        if (file_exists($filePath)) {
            Helper::deleteFile($filePath);
        }
        if (Helper::isEmptyDir($dirName))
            Helper::removeDir($dirName);
    }
}