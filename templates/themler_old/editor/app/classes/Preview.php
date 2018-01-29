<?php
class Preview
{
    private $_themeName;
    private $_data = array();
	private $_data_id_string;

    /**
     * @param $themeName
     */
    public function __construct($themeName)
    {
		$this->_data_id_string = 'data-con' . 'trol-id'; // HARD FIX. this string must not replaced by regexp in this file!!!
        $this->_themeName = $themeName;
        $diffs = JPATH_SITE . '/templates/' . $themeName . '/app/diffs.json';
        $obj = new PreviewDiffs($diffs);
        $this->_data = $obj->get()->toArray();
    }

    /**
     * @param $path
     * @param $content
     * @return mixed
     */
    public function removeDataId($path, $content)
    {
        if (trim($content)) {
            $diff = array();
            $lines = preg_split('/(\r\n|\n|\r|\f|\x0b|\x85)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

            for ($i = 0; $i < count($lines); $i++) {
                if (!strlen(trim($lines[$i])) || strpos($lines[$i], $this->_data_id_string) === false) continue;

                $ids = array();
                while ($data = $this->_splitByFirstDataId($lines[$i])) {
                    $ids[] = $data['id'];
                    $lines[$i] = $data['str'];
                }

                if (count($ids)) {
                    $diff[] = array(
                        'str' => $lines[$i],
                        'ids' => $ids
                    );
                }
            }

            $content = implode($lines);

            $this->_data[$this->_getKey($path)] = $diff;
        }

        return $content;
    }

    /**
     * @param $path
     * @param $content
     * @return mixed
     */
    public function restoreDataId($path, $content)
    {
        $key = $this->_getKey($path);
        $resKey = '';
        if (array_key_exists($key, $this->_data))
            $resKey = $key;
        if (array_key_exists('/' . $key, $this->_data))
            $resKey = '/' . $key;
        if (trim($content) && '' !== $resKey) {
            $diff = $this->_data[$resKey];
            $lines = preg_split('/(\r\n|\n|\r|\f|\x0b|\x85)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

            for ($i = 0; $i < count($lines); $i ++) {
                $line = $lines[$i];
                $lineLength = strlen(trim($line));
                if ($lineLength === 0) continue;
				
                foreach ($diff as $key => $d) {
                    //if (($lev = levenshtein(substr($line, 0, 255), substr($d['str'], 0, 255))) / $lineLength > .2) continue;
                    if (strcmp($line, $d['str']) !== 0) continue;

                    foreach (array_reverse($d['ids']) as $dataId) {
                        if (!array_key_exists('type', $dataId)) continue;
                        if ($dataId['type'] === 'attr') {
                            $line = substr_replace($line, sprintf($this->_data_id_string . '="%d"', $dataId['id']), $dataId['offset'], 0);
                        } else if ($dataId['type'] === 'class') {
                            $line = substr_replace($line, sprintf($this->_data_id_string . '-%d', $dataId['id']), $dataId['offset'], 0);
                        }
                    }

                    array_splice($diff, $key, 1);

                    break;
                }

                $lines[$i] = $line;
            }

            $content = implode($lines);
        }

        return $content;
    }

    /**
     * @param $path
     */
    public function removeKey($path)
    {
        $key = $this->_getKey($path);
        $withSlash = '/' . $key;
        if (array_key_exists($key, $this->_data))
            unset($this->_data[$key]);
        if (array_key_exists($withSlash, $this->_data))
            unset($this->_data[$withSlash]);
    }

    public function save()
    {
        $diffs = JPATH_SITE . '/templates/' . $this->_themeName . '/app/diffs.json';
        $obj = new PreviewDiffs($diffs);
        $obj->refresh($this->_data);
        $obj->save();
    }

    /**
     * @param $path
     * @return mixed
     */
    private function _getKey($path)
    {
        return $path;
    }

    /**
     * @param $content
     * @return array|bool
     */
    private function _splitByFirstDataId($content)
    {
        $result = false;
        $chunks = preg_split(
            '/(' . $this->_data_id_string . ')(=["\'](\d+)["\']|-(\d+))/i',
            $content,
            2,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
        );
        if (count($chunks) === 5) {
            $result = array(
                'id' => array(
                    'offset' => $chunks[1][1],
                    'id' => $chunks[3][0],
                    'type' => 'attr'
                ),
                'str' => $chunks[0][0] . $chunks[4][0]
            );
        } else if (count($chunks) === 6) {
            $result = array(
                'id' => array(
                    'offset' => $chunks[1][1],
                    'id' => $chunks[4][0],
                    'type' => 'class'
                ),
                'str' => $chunks[0][0] . $chunks[5][0]
            );
        }
        return $result;
    }
}