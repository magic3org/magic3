<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
defined('_JEXEC') or die;

header('Content-Type: text/html; charset=utf-8');
ob_start();
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script>window.loadAppHook = parent.loadAppHook;</script>
        <script type="text/javascript" src="<?php echo $this->startFiles['editor']; ?>"></script>

        <script id="loader-script" type="text/javascript"
            src="<?php echo $this->startFiles['loader']; ?>"
            data-swurl="<?php echo $this->startFiles['sw']; ?>"
            data-assets="components/com_nicepage/assets/app/"
            data-processor="joomla">
        </script>

        <script type="text/javascript" src="<?php echo $this->startFiles['auth']; ?>"></script>
    </head>
    <body></body>
    </html>
<?php
echo ob_get_clean();
exit();
?>