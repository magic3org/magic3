<?php
defined('_JEXEC') or die;

Designer::load("Designer_Shortcodes");
/**
 * Contains page rendering helpers.
 */
class DesignerPage
{

    public $page;
    public $positions = array();

    private $_componentProxy = null;
    private $_counter = 0;

    public function __construct($page)
    {
        $this->page = $page;

        $uid = JRequest::getVar('uid', 0);
        if (0 < $uid) {
            $session = JFactory::getSession();
            $user = new JUser($uid);
            $session->set('user', $user);
        }
    }

    /*
     * Checks whether Joomla! has system messages to display and renders theirs.
     */
    public function renderSystemMessages()
    {
        $app = JFactory::getApplication();
        $messages = $app->getMessageQueue();
        ob_start();
        if (is_array($messages) && count($messages))
            foreach ($messages as $msg) {
                if (isset($msg['type']) && isset($msg['message'])) {
                    switch ($msg['type']) {
                        case "info":
                            echo renderTemplateFromIncludes('funcInfoMessage', array($msg['message']), 'prototypes');
                            break;
                        case "error":
                            echo renderTemplateFromIncludes('funcErrorMessage', array($msg['message']), 'prototypes');
                            break;
                        case "":
                        case "warning":
                        case "message":
                            echo renderTemplateFromIncludes('funcWarningMessage', array($msg['message']), 'prototypes');
                            break;
                        case "notice":
                            echo renderTemplateFromIncludes('funcSuccessMessage', array($msg['message']), 'prototypes');
                            break;
                        default:
                            echo $msg['message'];
                    }
                }
            }
        return ob_get_clean();
    }

    /**
     * Returns true when any of the positions contains at least one module.
     * Example:
     *  if ($obj->containsModules('top1', 'top2', 'top3')) {
     *   // the following code will be executed when one of the positions contains modules:
     *   ...
     *  }
     */
    public function containsModules()
    {
        foreach (func_get_args() as $position)
            if (0 != $this->page->countModules($position))
                return true;
        return false;
    }

    public function renderTemplate($themeDir)
    {
        $content = $this->page->getBuffer('component');
        if (preg_match('/<!--TEMPLATE ([\s\S]*?) \/-->/', $content, $matches)) {
            $this->page->setBuffer(str_replace('<!--TEMPLATE ' . $matches[1] . ' /-->', '', $content), 'component');
            $GLOBALS['theme_settings']['currentTemplate'] = $matches[1];
        }
        $templates = $themeDir . '/templates/';
        $templatePath = $templates . $GLOBALS['theme_settings']['currentTemplate'] . '.php';

        if (file_exists($templatePath)) {
            ob_start();
            $document = JFactory::getDocument();
            include_once $templatePath;
            $template = ob_get_contents();
            ob_end_clean();
        } else {
            $template = 'Template ' . $templatePath . ' not found';
        }

        foreach($this->positions as $position) {
            preg_match_all('/<jdoc:include\ type="([^"]+)" id="([^"]*)" name="' . $position . '" (.*)\/>/iU', $template, $matches);
            $this->_counter = count($matches[0]);
            $template = preg_replace_callback('#<jdoc:include\ type="([^"]+)" id="([^"]*)" name="(' . $position . ')" (.*)\/>#iU', array( &$this, '_positionDuplicator'), $template);
        }

        return $template;
    }

    private function _positionDuplicator($matches)
    {
       if ($this->_counter != 1){
            $content =  str_replace($matches[3], $matches[2], $matches[0]);
       } else
            $content =  $matches[0];
       $this->_counter--;
       return $content;
    }

    public function position($position, $style = null, $id = '', $type= '')
    {
        array_push($this->positions, $position);

        $g = array_count_values($this->positions);
        $count = $g[$position];
        if ($count > 1 && !empty($id)) {
            $key = array_search($position, $this->positions);
            unset($this->positions[$key]);
        }
        return '<jdoc:include type="modules" title="name-' . $id . '" id="' . $id . '" type="' . $type . '" name="' . $position . '" style="drstyle"' . ' drstyle="' . (null != $style ?  $style : '') . '" />';
    }

    /**
     * Process and including needed template
     */
    public function componentWrapper($template)
    {
        $content = getCustomComponentContent($this->page->getBuffer('component'), $template);

        if ($this->page->getType() != 'html')
            return;
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');
        $layout = JRequest::getCmd('layout');

        $content = DesignerShortcodes::process($content);

        // Processing of all links
        $content = funcContentRoutesCorrector($content);
        // Workarounds for Joomla bugs and inconsistencies:
        switch ($option) {
            case "com_content":
                switch ($view) {
                    case "form":
                        if ("edit" == $layout)
                            $content = str_replace('<button type="button" onclick="', '<button type="button" class="button" onclick="', $content);
                        break;
                    case "article":
                        break;
                    case "category":
                    case "featured":
                    case "archive":
                        break;
                    case "archive":
                        break;
                }
                break;
            case "com_virtuemart":
                switch ($view) {
                    case "category":
                        break;
                    case "productdetails":
                        break;
                     case "cart":
                        break;
                }
                break;
            case "com_users":
                switch ($view) {
                    case "remind":
                        if ("" == $layout) {
                            $content = str_replace('<button type="submit">', '<button type="submit" class="button">', $content);
                            $content = str_replace('<button type="submit" class="validate">', '<button type="submit" class="button">', $content);
                        }
                        break;
                    case "reset":
                        if ("" == $layout) {
                            $content = str_replace('<button type="submit">', '<button type="submit" class="button">', $content);
                            $content = str_replace('<button type="submit" class="validate">', '<button type="submit" class="button">', $content);
                        }
                        break;
                    case "registration":
                        if ("" == $layout)
                            $content = str_replace('<button type="submit" class="validate">', '<button type="submit" class="button validate">', $content);
                        break;
                }
                break;
            default:
                break;
        }
                // Code injections:
        switch ($option) {
            case "com_content":
                switch ($view) {
                    case "form":
                        if ("edit" == $layout)
                            $this->page->addScriptDeclaration($this->getWysiwygBackgroundImprovement());
                        break;
                }
                break;
        }

        $this->page->setBuffer($content, 'component');
    }

    public function getComponentProxy()
    {
        return $this->_componentProxy;
    }

    public function getWysiwygBackgroundImprovement()
    {
        ob_start();
?>
window.addEvent('domready', function() {
    var waitFor = function (interval, criteria, callback) {
        var interval = setInterval(function () {
            if (!criteria())
                return;
            clearInterval(interval);
            callback();
        }, interval);
    };
    var editor = ('undefined' != typeof tinyMCE)
        ? tinyMCE
        : (('undefined' != typeof JContentEditor)
            ? JContentEditor : null);
    if (null != editor) {
        // fix for TinyMCE editor
        waitFor(75,
            function () {
                if (editor.editors)
                    for (var key in editor.editors)
                        if (editor.editors.hasOwnProperty(key))
                            return editor.editors[key].initialized;
                return false;
            },
            function () {
                var ifr = jQuery('#jform_articletext_ifr');
                var ifrdoc = ifr[0] && ifr[0].contentDocument;
                ifrdoc && jQuery('link[href*="/css/editor.css"]', ifrdoc).ready(function () {
                    var link = jQuery('<link/>', {
                        rel: 'stylesheet',
                        type: 'text/css',
                        href: '<?php echo JURI::root() . 'templates/' . $this->page->template . '/css/bootstrap.css'; ?>' });
                    jQuery('head', ifrdoc).append(link);
                    jQuery('link[href$="content.css"]', ifrdoc).remove();
                    ifr.css('background', 'transparent').attr('allowtransparency', 'true');
                    var ifrBodyNode = jQuery('body', ifrdoc);
                    var layout = jQuery('table.mceLayout');
                    var toolbar = layout.find('.mceToolbar');
                    var toolbarBg = toolbar.css('background-color');
                    var statusbar = layout.find('.mceStatusbar');
                    var statusbarBg = statusbar.css('background-color');
                    layout.css('background', 'transparent');
                    toolbar.css('background', toolbarBg);
                    toolbar.css('direction', '');
                    statusbar.css('background', statusbarBg);
                    ifrBodyNode.css('background', 'transparent');
                    ifrBodyNode.attr('dir', 'ltr');
                });
            });
    } else if ('undefined' != typeof CKEDITOR) {
        CKEDITOR.on('instanceReady', function (evt) {
            var includesTemplateStyle = 0 != jQuery('link[href*="/css/template.css"]', evt.editor.document.$).length;
            var includesEditorStyle = 0 != jQuery('link[href*="/css/editor.css"]', evt.editor.document.$).length;
            if (includesTemplateStyle || includesEditorStyle) {
                jQuery('#cke_ui_color').remove();
                var ifr = jQuery('#cke_contents_text>iframe');
                ifr.parent().css('background', 'transparent')
                    .parent().parent().parent().parent()
                    .css('background', 'transparent');
                console.log(jQuery('.cke_wrapper'));
                ifr.attr('allowtransparency', 'true');
                ifr.css('background', 'transparent');
                var ifrdoc = ifr.attr('contentDocument');
                jQuery('body', ifrdoc).css('background', 'transparent');
                /*if (includesTemplateStyle)
                    jQuery('body', ifrdoc).attr('id', 'bd-main').addClass('bd-postcontent');*/
            }
        });
    }
});
<?php
        return ob_get_clean();
    }
}