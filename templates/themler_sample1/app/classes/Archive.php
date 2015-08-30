<?php

class Archive
{
    public function __construct() {}

    public function getArchive($base, $oldThemeName, $newThemeName, $includeEditor = false)
    {
        $lowerOldThemeName = strtolower($oldThemeName);
        $lowerNewThemeName = strtolower($newThemeName);
        $zipFilesArray = array();
        $flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, $flags));
        foreach ($iterator as $fileInfo) {
            if (preg_match('/^(tmp)/', $iterator->getSubPathname()))
                continue;
            if (!$includeEditor && (preg_match('/^(app|editor)/', $iterator->getSubPathname()) ||
                    'fields/designer.php' === $iterator->getSubPathname()))
                continue;
            $name = $iterator->getSubPathname();
            $data = Helper::readFile($fileInfo->getPathName());
            $info = pathinfo($name);
            $dirNameParts = explode('/', $info['dirname']);
            $type = '';
            if (count($dirNameParts) > 2 && 'html' == $dirNameParts[0]) {
                $componentParts = explode('_', $dirNameParts[1]);
                $type = array_shift($componentParts);
            }
            $parts      = explode('.', $info['basename']);
            $fname      = $parts[0];
            if (!$includeEditor && ('com' === $type || 'mod' === $type || 'pagination' === $fname || 'error' === $fname
                || 'modules' === $fname || 'index.php' === $name || 'modrender.php' === $name)) {
                $data = preg_replace('/\<\?php\s+\/\*BEGIN_EDITOR_OPEN\*\/([\s\S]*?)\/\*BEGIN_EDITOR_CLOSE\*\/\s+\?\>/s', '', $data);
                $data = preg_replace('/<\?php\s+\/\*END_EDITOR_OPEN\*\/([\s\S]*?)\/\*END_EDITOR_CLOSE\*\/\s+\?\>/s', '', $data);
            }
            if ('language/en-GB/en-GB.tpl_' . $lowerOldThemeName . '.ini' === $iterator->getSubPathname()) {
                $name = 'language/en-GB/en-GB.tpl_' . $lowerNewThemeName . '.ini';
            }
            if ('templateDetails.xml' === $iterator->getSubPathname()) {
                $xml = simplexml_load_string($data);
                $xml->name = $newThemeName;
                $path = $xml->config->fields['addfieldpath'];
                $xml->config->fields['addfieldpath'] = str_replace($lowerOldThemeName, $lowerNewThemeName, $path);
                foreach($xml->languages->language as $node) {
                    $language = $node[0];
                    $node[0] = str_replace($lowerOldThemeName, $lowerNewThemeName, $language);
                }
                if (!$includeEditor) {
                    $newChildren = array();
                    foreach($xml->files->children() as  $type => $filesName) {
                        if (($filesName == 'app' || $filesName == 'editor')) {
                            continue;
                        }
                        $newChildren[] = array('type' => $type, 'name' => $filesName->__toString());
                    }
                    unset($xml->files->folder);
                    foreach($newChildren as $child) {
                        if ($child['type'] == 'folder')
                            $xml->files->addChild($child['type'], $child['name']);
                    }
                    list($designer) = $xml->xpath('//field[@type="designer"]');
                    unset($designer[0]);
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
            }
            $zipFilesArray[] = array('name' => $name, 'data' => $data);
        }
        $tmp = $base . '/tmp';
        Helper::createDir($tmp);

        jimport('joomla.filesystem.archive');
        jimport('joomla.filesystem.file');
        $archiveName = $lowerNewThemeName . '.zip';
        $zip = JArchive::getAdapter('zip');
        $zip->create($tmp . '/' . $archiveName, $zipFilesArray);
        $result = base64_encode(Helper::readFile($tmp . '/' . $archiveName));

        Helper::removeDir($tmp);

        return $result;
    }
}