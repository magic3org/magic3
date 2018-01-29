<?php
require_once('Transformers.php');
require_once('PlaceHoldersStorage.php');

abstract class DefaultTransformer
{
    /**
     * @param $rules
     * @return mixed
     */
    public function transform($rules)
    {
        foreach($rules as &$rule)
            $rule = $this->transformRule($rule);
        return $rules;
    }

    /**
     * @param $rule
     * @return stdClass
     */
    public function  transformRule($rule)
    {
        $newRule = new stdClass();
        $newRule->selectors = $this->transformSelectors($rule->selectors);
        $newRule->properties = $this->transformProperties($rule->properties);
        $newRule->parent = $rule->parent;
        return $newRule;
    }

    /**
     * @param $selectors
     * @return mixed
     */
    protected function transformSelectors($selectors)
    {
        return $selectors;
    }

    /**
     * @param $properties
     * @return mixed
     */
    protected function transformProperties($properties)
    {
        return $properties;
    }
}

class PrintCssTransformer  extends DefaultTransformer
{
    /**
     * @param $selectors
     * @return array|mixed
     */
    public function  transformSelectors($selectors)
    {
        $excludedSelectors = array('slider', 'arrow',
          'loading', 'close', 'cw', 'ccw', 'preview-cms-logo',
          'lightbox', 'reset');
        $re = '/(' . implode('|', $excludedSelectors) . ')/';

        $result = array();
        foreach($selectors as $value) {
          $selectorsValidChrs = '-?[_a-zA-Z]+[_a-zA-Z0-9-]*';
          $collapsed = '/\.' . $selectorsValidChrs . 'postcontent' . $selectorsValidChrs . '/';
          $value = trim(preg_replace($collapsed, '', $value));
          if ('' !== $value && !preg_match($re, $value))
              array_push($result, $value);
        }
        return $result;
    }

    /**
     * @param $properties
     * @return array|mixed
     */
    public function transformProperties($properties)
    {
        $result = array();
        foreach($properties as $property => $data){
            if (!preg_match('/(border-|color|background-)/', $property)){
                $result[$property] = $data;
            }
        }
        return $result;
    }
}

class EditorCssTransformer  extends DefaultTransformer
{
    /**
     * @param $selectors
     * @return array|mixed
     */
    public function  transformSelectors($selectors)
    {
        $excludedSelectors = array('slider', 'arrow',
            'loading', 'close', 'cw', 'ccw', 'preview-cms-logo',
            'lightbox', 'reset');
        $re = '/(' . implode('|', $excludedSelectors) . ')/';

        $result = array();
        foreach($selectors as $value) {
            $selectorsValidChrs = '-?[_a-zA-Z]+[_a-zA-Z0-9-]*';
            $collapsed = '/(\.' . $selectorsValidChrs . 'postcontent' . $selectorsValidChrs . ')/';
            $value = preg_replace('/^\.' . $selectorsValidChrs . 'postcontent' . $selectorsValidChrs . ' (\w*)/', 'body $1', $value);
            $value = trim(preg_replace($collapsed, '', $value));
            $value = '' !== $value ? $value : 'body';
            if(!preg_match($re, $value))
                array_push($result, $value);
        }
        return $result;
    }

    /**
     * @param $rule
     * @return stdClass
     */
    public function transformRule($rule)
    {
         $selectors = $this->transformSelectors($rule->selectors);
         $newRule = new stdClass();
         $newRule->selectors = $selectors;
         $newRule->properties = $this->transformProperties($rule->properties);
         $newRule->parent = $rule->parent;
         if (false !== in_array('body', $selectors)){
                $properties = array();
                foreach($rule->properties as $property => $data){
                     switch ($property) {
                        case 'overflow':
                        case 'position':
                        case 'width':
                        case 'min-width':
                            break;
                        default:
                            $properties[$property] = $data;
                            break;
                    }
                }
                $newRule->properties = $properties;
         }
         return $newRule;
    }

}

class CssParser
{
    private $_result = array();

    /**
     * @param $content
     * @return array
     */
    public function parse($content) 
    {
        $clearedContent = $this->clearContent($content);

        // parse media rules
        while(false !== $mediaPos = strpos($clearedContent, '@media', 0)) {
            if (false !== $mediaPos) {
                $mediaOpenBracketPos = strpos($clearedContent, '{', $mediaPos);
                $start = $mediaOpenBracketPos;
                while(true) {
                    $openBracketPos = strpos($clearedContent, '{', $start + 1);
                    $closeBracketPos = strpos($clearedContent, '}', $start + 1);

                    if ((false === $openBracketPos && false !== $closeBracketPos) || $closeBracketPos < $openBracketPos) {
                        $mediaCloseBracketPos = $closeBracketPos;
                        break;
                    } else {
                        $start = $closeBracketPos;
                    }
                }
                $rules = substr($clearedContent, $mediaOpenBracketPos + 1, $mediaCloseBracketPos - ($mediaOpenBracketPos + 1));
                $parent = substr($clearedContent, $mediaPos, $mediaOpenBracketPos - $mediaPos);
                $this->matchRules($rules, $this->_result, $parent);
                $clearedContent = substr_replace($clearedContent, '', $mediaPos, ($mediaCloseBracketPos + 1) - $mediaPos);
            } 
        }
            
        $this->matchRules($clearedContent, $this->_result);

        return $this->_result;
    }

    /**
     * @param $content
     * @param $result
     * @param string $parent
     */
    private function matchRules($content, &$result, $parent = '') 
    {
        $offset = 0;
        while (preg_match('/([^{]+)\s*\{\s*([^}]+)\s*\}/', $content, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $offset = $matches[0][1] + strlen($matches[0][0]);
            $item = new stdClass();
            $item->selectors = $this->parseSelector($matches[1][0]);
            $item->properties = $this->parseProperties($matches[2][0]);
            $item->parent = $parent;
            $result[] = $item;
        }
    }

    /**
     * @return array
     */
    public function getStylesheet() 
    {
        return $this->_result; 
    }

    /**
     * @param $criteria
     * @param bool $precision
     * @param bool $all
     * @return array|null
     */
    public function getRule($criteria, $precision = true, $all = false) 
    {
        $items = array();
        foreach($this->_result as $value) {
            $str = implode(', ', $value->selectors);
            if ($precision) {
                if ($criteria === $str)
                    array_push($items, $value);
            } else {
                if (false !== strpos($str, $this->_args['criteria']))
                    array_push($items, $value);
            }
        }
        return (0 === count($items)) ? null : ($all ? $items : $items[0]);
    }

    /**
     * @param $rule
     * @param $property
     * @param $value
     */
    public function setRule($rule, $property, $value) 
    {
        if (is_object($rule)) {
            $rule->properties[$property] = $value;
        }
    }

    /**
     * @param $content
     * @return mixed
     */
    private function clearContent($content)
    {
        $result = preg_replace('/\/\* Begin Additional CSS Styles \*\/([\s\S]*)\/\* End Additional CSS Styles \*\//', '', $content);
        $result = preg_replace('/\/\*[^\/]*\*\//', '', $result);
        $result = preg_replace('/#marker[^}]+}/', '', $result);
        return $result;
    }

    /**
     * @param $selector
     * @return mixed
     */
    private function parseSelector($selector)
    {
        $parts = explode(',', $selector);
        foreach($parts as &$part)
            $part = trim($part);
        return $parts;
    }

    /**
     * @param $properties
     * @return array
     */
    private function parseProperties($properties) 
    {
        $propsArray = explode(';', $properties);
        $result = array();
        foreach($propsArray as $value) {
            if ('' === trim($value))
                continue;
            $parts = explode(':', $value);
            if (2 === count($parts)) {
                $propertyName = trim($parts[0]);
                $propertyValue = trim($parts[1]);
                if (isset($result[$propertyName])) {
                    $currentValue = $result[$propertyName];
                    if (is_array($currentValue)) {
                        array_push($currentValue, $propertyValue);
                    } else {
                        $currentValue = array($currentValue, $propertyValue);
                    }
                    $propertyValue = $currentValue;
                }
                $result[$propertyName] = $propertyValue;
            }
        }
        return $result;
    }
}

class CssPrinter 
{
    /**
     * @param $stylesheet
     * @return array
     */
    public function printing($stylesheet) {

        if (is_string($stylesheet)) {
            return $stylesheet;
        }
        $result = array();
        foreach ($stylesheet as $value) {
            if (!(count($value->selectors) > 0 && count($value->properties) > 0))
                continue;
            if ('' !== $value->parent) {
                array_push($result, $value->parent . " {\r\n");
            }
            array_push($result, implode(', ', $value->selectors));
            array_push($result, '{');
            foreach($value->properties as $property => $data) {
                if (is_string($data)) {
                    $data = array($data);
                }
                foreach($data as $style) {
                    array_push($result, '  ' .  $property . ': ' . $style . ';');
                }
            }
            array_push($result, "}\r\n");
            if ('' !== $value->parent) {
                array_push($result, "}\r\n");
            }
        }
        $result = implode("\r\n", $result);
        return $result;
    }
}

class StylesBuilder
{
    /**
     * @return CssParser
     */
    public static function getCssParser()
    {
        return new CssParser();
    }

    /**
     * @return CssPrinter
     */
    public static function getCssPrinter()
    {
        return new CssPrinter();
    }

    /**
     * @return EditorCssTransformer
     */
    public static function getEditorCssTransformer()
    {
        return new EditorCssTransformer();
    }

    /**
     * @return PrintCssTransformer
     */
    public static function getPrintCssTransformer()
    {
        return new PrintCssTransformer();
    }

    /**
     * @param $content
     * @return PlaceHoldersStorage
     */
    public static function buildPlaceHoldersUrls(&$content)
    {
        $placeholders = new PlaceHoldersStorage(null, '[[[', ']]]');
        while (preg_match('/url\([^)]*\)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $content = str_replace($matches[0][0], $placeholders->create($matches[0][0]), $content);
        }
        return $placeholders;
    }

    /**
     * @param $content
     * @return array
     */
    public static function buildCssFiles($content)
    {
        $timeLogging = LoggingTime::getInstance();

        $placeholders = StylesBuilder::buildPlaceHoldersUrls($content);

        $timeLogging->start('[PHP] Parse css content');
        $stylesheet = StylesBuilder::getCssParser()->parse($content);
        $timeLogging->end('[PHP] Parse css content');

        $printer    = StylesBuilder::getCssPrinter();

        $timeLogging->start('[PHP] Build print.css');
        $printCss = $printer->printing(StylesBuilder::getPrintCssTransformer()->transform($stylesheet));
        $timeLogging->end('[PHP] Build print.css');

        $timeLogging->start('[PHP] Build editor.css');
        $editorCss = $printer->printing(StylesBuilder::getEditorCssTransformer()->transform($stylesheet));
        $timeLogging->end('[PHP] Build editor.css');

        return array('print' => $placeholders->replace($printCss), 'editor' => $placeholders->replace($editorCss));
    }
}