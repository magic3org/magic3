<?php
/**
 * Magic3コアクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
class _Core
{
	protected $gLog;
	protected $gInstance;
	protected $gSystem;
	protected $gEnv;
	protected $gOpeLog;
	protected $gCache;
	protected $gLaunch;
	protected $gAccess;
	protected $gConfig;
	protected $gError;
	protected $gPage;
	protected $gRequest;
	protected $gDesign;
	protected $gDisp;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gLogManager;
		global $gInstanceManager;
		global $gSystemManager;
		global $gEnvManager;
		global $gOpeLogManager;
		global $gCacheManager;
		global $gLaunchManager;
		global $gAccessManager;
		global $gConfigManager;
		global $gErrorManager;
		global $gPageManager;
		global $gRequestManager;
		global $gDesignManager;
		global $gDispManager;
	
		$this->gLog			= $gLogManager;
		$this->gInstance	= $gInstanceManager;
		$this->gSystem		= $gSystemManager;
		$this->gEnv			= $gEnvManager;
		$this->gOpeLog		= $gOpeLogManager;
		$this->gCache		= $gCacheManager;
		$this->gLaunch		= $gLaunchManager;
		$this->gAccess		= $gAccessManager;
		$this->gConfig		= $gConfigManager;
		$this->gError		= $gErrorManager;
		$this->gPage		= $gPageManager;
		$this->gRequest		= $gRequestManager;
		$this->gDesign		= $gDesignManager;
		$this->gDisp		= $gDispManager;
	}
}
?>
