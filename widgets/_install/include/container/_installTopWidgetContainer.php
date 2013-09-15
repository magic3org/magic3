<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');

class _installTopWidgetContainer extends _installBaseWidgetContainer
{
	private $langTypeArray;		// 言語タイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 言語タイプ
		$this->langTypeArray = array(	array(	'name' => '日本語(Japanese)',		'value' => 'ja'),
										array(	'name' => '英語(English)',		'value' => 'en'));
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'top.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$this->langId = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);
		if (empty($this->langId)) $this->langId = self::DEFAULT_LANG;
		
		// 言語メニュー作成
		$this->createLangTypeMenu();
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['label_language'] = $this->_('Language');
		$localeText['msg_install'] = $this->_('Start Installing');		// インストール開始のメッセージ
		$this->setLocaleText($localeText);
	}
	/**
	 * 言語メニュー作成
	 *
	 * @return なし
	 */
	function createLangTypeMenu()
	{
		for ($i = 0; $i < count($this->langTypeArray); $i++){
			$value = $this->langTypeArray[$i]['value'];
			$name = $this->langTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->langId) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// グラフ種別ID
				'name'     => $name,			// グラフ種別
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('langtype_list', $row);
			$this->tmpl->parseTemplate('langtype_list', 'a');
		}
	}
}
?>
