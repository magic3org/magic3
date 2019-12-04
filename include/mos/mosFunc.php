<?php
/**
 * Joomla用関数
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: mosFunc.php 3903 2010-12-21 07:49:58Z fishbone $
 * @link       http://www.magic3.org
 */
/**
* Cache some modules information
* @return array
*/
function &initModules()
{
	return true;
}
/**
* @param string THe template position
*/
function mosCountModules($position = 'left')
{
	global $gPageManager;
	$count = $gPageManager->getWidgetsCount($position);
	return $count;
}
/**
* @param string The position
* @param int The style.  0=normal, 1=horiz, -1=no wrapper
*/
function mosLoadModules($position='left', $style=0)
{
	global $gPageManager;
	echo $gPageManager->getContents($position);
}
function mosShowHead()
{
	global $gPageManager;
	$gPageManager->getHeader();
}
/**
* Displays the capture output of the main element
*/
function mosMainBody()
{
	global $gPageManager;
	echo $gPageManager->getContents('main');
}
/**
* BODYタグにスタイルを付加(Magic3専用)
*/
function mosBodyStyle()
{
	global $gPageManager;
	
	echo $gPageManager->getBodyStyle();
}
/**
* Utility functions and classes
*/
function mosLoadComponent( $name ) {
}
/**
* Returns current date according to current local and time offset
* @param string format optional format for strftime
* @returns current date
*/
function mosCurrentDate( $format="" ) {
	global $mosConfig_offset;
	if ($format=="") {
		$format = _DATE_FORMAT_LC;
	}
	$date = strftime( $format, time() + ($mosConfig_offset*60*60) );
	return $date;
}
/**
 * Utility function to return a value from a named array or a specified default
 * @param array A named array
 * @param string The key to search for
 * @param mixed The default value to give if no key found
 * @param int An options mask: _MOS_NOTRIM prevents trim, _MOS_ALLOWHTML allows safe html, _MOS_ALLOWRAW allows raw input
 */
define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW", 0x0004 );
function mosGetParam( &$arr, $name, $def=null, $mask=0 ) {
	static $noHtmlFilter 	= null;
	static $safeHtmlFilter 	= null;

	$return = null;
	if (isset( $arr[$name] )) {
		$return = $arr[$name];
		
		if (is_string( $return )) {
			// trim data
			if (!($mask&_MOS_NOTRIM)) {
				$return = trim( $return );
			}
			
			if ($mask&_MOS_ALLOWRAW) {
				// do nothing
			} else if ($mask&_MOS_ALLOWHTML) {
				// do nothing - compatibility mode
				/*
				if (is_null( $safeHtmlFilter )) {
					$safeHtmlFilter = new InputFilter( null, null, 1, 1 );
				}
				$arr[$name] = $safeHtmlFilter->process( $arr[$name] );
				*/
			} else {
				// send to inputfilter
				if (is_null( $noHtmlFilter )) {
					$noHtmlFilter = new InputFilter( /* $tags, $attr, $tag_method, $attr_method, $xss_auto */ );
				}
				$return = $noHtmlFilter->process( $return );
			}
			
			// account for magic quotes setting
			//if (!get_magic_quotes_gpc()) {
				$return = addslashes( $return );
			//}
		}
		
		return $return;
	} else {
		return $def;
	}
}
/*
* Includes pathway file
*/
function mosPathWay() {
}
?>
