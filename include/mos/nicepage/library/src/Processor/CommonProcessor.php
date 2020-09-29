<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

class CommonProcessor
{
    /**
     * Processing of default image path
     *
     * @param string $content Page content
     *
     * @return mixed
     */
    public function processDefaultImage($content)
    {
        // replace default image placeholder
        $url = JURI::root() . 'components/com_nicepage/assets/images/nicepage-images/default-image.jpg';
        return str_replace('[image_default]', $url, $content);
    }

    /**
     * Processing of forms
     *
     * @param string $content Page content
     * @param string $pageId  Type id
     *
     * @return mixed
     */
    public function processForm($content, $pageId)
    {
        // process source is joomla
        $content = preg_replace('/(<form[^>]*action=[\'\"]+).*?([\'\"][^>]*source=[\'\"]joomla)/', '$1index.php?option=com_nicepage&task=sendmail$2', $content);
        $content = preg_replace_callback(
            '/<form[^>]+redirect-address=[\'\"](.*?)[\'\"][^>]*>/',
            function ($matches) {
                if (preg_match('/<form[^>]+redirect=[\'\"]true[\'\"]/', $matches[0])) {
                    return $matches[0] . '<input type="hidden" name="redirect" value="' . $matches[1] . '">';
                }
                return $matches[0];
            },
            $content
        );
        // process source is customphp
        if ($pageId) {
            $content = preg_replace(
                '/(<form[^>]*action=[\'\"]+)\[\[form\-(.*?)\]\]([\'\"][^>]*source=[\'\"]customphp)/',
                '$1index.php?option=com_nicepage&task=form&id=' . $pageId . '&formId=$2$3',
                $content
            );
        }
        return $content;
    }

    /**
     * Process all custom php on the page
     *
     * @param string $content Page content
     *
     * @return mixed
     */
    public function processCustomPhp($content) {
        $content = preg_replace('/data-custom-php=["\']+(<\!--custom_php-->[\s\S]+?<\!--\/custom_php-->)["\']+([^>]*?>)/', '$2$1', $content);
        $content = preg_replace_callback(
            '/<\!--custom_php-->([\s\S]+?)<\!--\/custom_php-->/',
            function ($matches) {
                $code = trim($matches[1]);
                $code = str_replace("<?php", "", $code);
                $code = str_replace("?>", "", $code);
                $code = str_replace("&quot;", "\"", $code);
                ob_start();
                @eval($code);
                return ob_get_clean();
            },
            $content
        );
        return $content;
    }
}