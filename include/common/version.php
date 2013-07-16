<?php
/**
 * Magic3 パージョン情報
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: version.php 1248 2008-11-18 03:32:32Z fishbone $
 * @link       http://www.magic3.org
 */
class m3Version {
	/** @var string Product */
	var $PRODUCT 	= 'Magic3';
	/** @var int Main Release Level */
	var $RELEASE 	= '1.4.2';
	/** @var string Development Status */
	var $DEV_STATUS = 'Stable';
	/** @var int Sub Release Level */
	var $DEV_LEVEL 	= '';
	/** @var int build Number */
	var $BUILD	 	= '$Revision: 1248 $';
	/** @var string Codename */
	var $CODENAME 	= '';
	/** @var string Date */
	var $RELDATE 	= '2006-6-12';
	/** @var string Time */
	var $RELTIME 	= '00:00';
	/** @var string Timezone */
	var $RELTZ 		= 'JST';
	/** @var string Copyright Text */
	var $COPYRIGHT 	= 'Copyright (C) 2006 Magic3.org. All rights reserved.';
	/** @var string URL */
	var $URL 		= '<a href="http://www.magic3.org">Magic3</a> is Free Software released under the GNU/GPL License.<br />Translation is <a href="http://www.magic3.org">Magic3.org</a>.';
	/** @var string Whether site is a production = 1 or demo site = 0 */
	var $SITE 		= 1;

	
	/**
	 * @return string Long format version
	 */
	function getLongVersion() {
		return $this->PRODUCT .' '. $this->RELEASE .'.'. $this->DEV_LEVEL .' '
			. $this->DEV_STATUS
			.' [ '.$this->CODENAME .' ] '. $this->RELDATE .' '
			. $this->RELTIME .' '. $this->RELTZ;
	}

	/**
	 * @return string Short version format
	 */
	function getShortVersion() {
		return $this->RELEASE .'.'. $this->DEV_LEVEL;
	}

	/**
	 * @return string Version suffix for help files
	 */
	function getHelpVersion() {
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace( '.', '', $this->RELEASE );
		} else {
			return '';
		}
	}
}
?>