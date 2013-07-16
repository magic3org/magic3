<?php
/**
* @version $Id: sef.php 1955 2009-06-02 08:50:24Z fishbone $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Converts an absolute URL to SEF format
 * @param string The URL
 * @return string
 */
function sefRelToAbs( $string ) {
	global $mosConfig_live_site, $mosConfig_sef, $mosConfig_mbf_content;
	global $iso_client_lang;

	//multilingual code url support
	if( $mosConfig_mbf_content && $string!='index.php' && !eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),'index.php') && !eregi('lang=', $string) ) {
		$string .= '&lang='. $iso_client_lang;
	}

	// SEF URL Handling
	if ($mosConfig_sef && !eregi("^(([^:/?#]+):)",$string) && !strcasecmp(substr($string,0,9),'index.php')) {
		// Replace all &amp; with &
		$string = str_replace( '&amp;', '&', $string );

		/*
		Home
		index.php
		*/
		if ($string=='index.php') {
			$string='';
		}

		$sefstring = '';
		if ( (eregi('option=com_content',$string) || eregi('option=content',$string) ) && !eregi('task=new',$string) && !eregi('task=edit',$string) ) {
			// Handle fragment identifiers (ex. #foo)
			$fragment = '';
			if (eregi('#', $string)) {
				$temp = split('#', $string, 2);
				$string = $temp[0];
				// ensure fragment identifiers are compatible with HTML4
				if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $temp[1])) {
					$fragment = '#'. $temp[1];
				}
			}
			
			/*
			Content
			index.php?option=com_content&task=$task&sectionid=$sectionid&id=$id&Itemid=$Itemid&limit=$limit&limitstart=$limitstart&year=$year&month=$month&module=$module
			*/
			$sefstring .= 'content/';
			if (eregi('&task=',$string)) {
				$temp = split('&task=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&sectionid=',$string)) {
				$temp = split('&sectionid=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&id=',$string)) {
				$temp = split('&id=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&Itemid=',$string)) {
				$temp = split('&Itemid=', $string);
				$temp = split('&', $temp[1]);

				if ( $temp[0] !=  99999999 ) {
					$sefstring .= $temp[0].'/';
				}
			}
			if (eregi('&limit=',$string)) {
				$temp = split('&limit=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&limitstart=',$string)) {
				$temp = split('&limitstart=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= $temp[0].'/';
			}
			if (eregi('&lang=',$string)) {
				$temp = split('&lang=', $string);
				$temp = split('&', $temp[1]);

				$sefstring .= 'lang,'.$temp[0].'/';
			}
			if (eregi('&year=',$string)) {
				$temp = split('&year=', $string);
				$temp = split('&', $temp[1]);
				
				$sefstring .= $temp[0].'/';
			}
			if (eregi('&month=',$string)) {
				$temp = split('&month=', $string);
				$temp = split('&', $temp[1]);
				
				$sefstring .= $temp[0].'/';
			}
			if (eregi('&module=',$string)) {
				$temp = split('&module=', $string);
				$temp = split('&', $temp[1]);
				
				$sefstring .= $temp[0].'/';
			}
			
			$string = $sefstring . $fragment;
		} else if (eregi('option=com_',$string) && !eregi('task=new',$string) && !eregi('task=edit',$string)) {
			/*
			Components
			index.php?option=com_xxxx&...
			*/
			$sefstring 	= 'component/';
			$temp 		= split("\?", $string);
			$temp 		= split('&', $temp[1]);

			foreach($temp as $key => $value) {
				$sefstring .= $value.'/';
			}
			$string = str_replace( '=', ',', $sefstring );
		}

		// comment line below if you dont have mod_rewrite
		return $mosConfig_live_site .'/'. $string;

		// allows SEF without mod_rewrite
		// uncomment Line 348 and comment out Line 354	
	
		// uncomment line below if you dont have mod_rewrite
		// return $mosConfig_live_site.'/index.php/'.$string;
		// If the above doesnt work - try uncommenting this line instead
		// return $mosConfig_live_site.'/index.php?/'.$string;
	} else {
	// Handling for when SEF is not activated
		// Relative link handling
		if ( !(strpos( $string, $mosConfig_live_site ) === 0) ) {
			// if URI starts with a "/", means URL is at the root of the host...
			if (strncmp($string, '/', 1) == 0) {
				// splits http(s)://xx.xx/yy/zz..." into [1]="htpp(s)://xx.xx" and [2]="/yy/zz...":
				$live_site_parts = array();
				eregi("^(https?:[\/]+[^\/]+)(.*$)", $mosConfig_live_site, $live_site_parts);
				
				$string = $live_site_parts[1] . $string;
			} else {
				// URI doesn't start with a "/" so relative to the page (live-site):
				$string = $mosConfig_live_site .'/'. $string;
			}
		}
		
		return $string;
	}
}
?>