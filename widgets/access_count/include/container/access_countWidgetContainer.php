<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: access_countWidgetContainer.php 4423 2011-11-07 05:31:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/access_countDb.php');

class access_countWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
    private $config = array();
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $isHiddenCounter;	// システム管理者、システム運用者の場合のみカウンターを表示するかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ###### アクセスカウンターの表示設定 ######
 		// 画像のURL
		$this->config['img'] = $this->getUrl($this->gEnv->getCurrentWidgetRootUrl()) . '/images/';
        $this->config['animated_img'] = $this->config['img'];
		$this->config['animated_img_prefix'] = 'a';		// アニメーション画像ファイル用プレフィックス
		
        // 表示桁数
        $this->config['pad'] = 7;

        // 画像のサイズ
        $this->config['width']  = 16;
        $this->config['height'] = 22;
		
		// DBオブジェクト作成
		$this->db = new access_countDb();
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
		return 'index.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		
		// 表示設定取得
		$this->isHiddenCounter = 0;	// システム管理者、システム運用者の場合のみカウンターを表示するかどうか
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->isHiddenCounter	= $paramObj->isHiddenCounter;
		}
			
		// 作成したHTMLを出力
		$numberList = $this->createOutput();
		if (empty($numberList)){		// データが空のときは出力をキャンセル
			$this->cancelParse();
		} else {
			$this->tmpl->addVar("_widget", "number_list", $numberList);
		}
	}
	/**
	 * 新規アクセスかどうかのチェック
	 *
	 * @return bool					true=新規アクセス、false=2回目以降アクセス
	 */
    function isNewVisitor()
	{
		// セッションIDを取得
		$ssid = session_id();
		
		// 登録済みかチェック
		$ret = $this->db->isSessionExists($ssid);
		if ($ret){		// レコードが存在しているときは、既存訪問者
			return false;
		} else {
			return true;
		}
    }
	/**
	 * 現在のカウントを取得
	 *
	 * @param bool         $up		新規のアクセスかどうか(true=新規アクセス、false=2回目以降アクセス)
	 * @return int					アクセス数
	 */
    function readCounter(&$up)
	{
		$date = date('Y-m-d');
		$up = false;		// 新規かどうか
		
		// カウンタを更新
		if (!$this->isSystemManageUser){	// システム管理者、システム運用者の場合はカウントしない
			// 1セッションに付き、一回だけカウンタを更新する
			// 新規セッションであれば、カウンタを更新
			if ($this->isNewVisitor()){
				$this->db->incrementCount($date);
				$up = true;
			}
		}
		$count = $this->db->getCount($date);
		return $count;
    }
	/**
	 * カウンタのHTMLを作成
	 *
	 * @return string					HTMLテキスト
	 */
    function createOutput()
	{
		// カウンター更新
        $count = $this->readCounter($up);
		
		if (!empty($this->isHiddenCounter) && !$this->isSystemManageUser){		// システム管理者、システム運用者以外はカウンターを表示しない
			return '';
		} else {
			$countStr = sprintf("%0"."".$this->config['pad'].""."d", $count -1);	// 1つ前のカウント
	        $destCountStr = sprintf("%0"."".$this->config['pad'].""."d", $count);	// 実際のカウント
			$html_output = '';
			if ($up){
				for ($i = 0; $i < strlen($countStr); $i++){
					if (substr($countStr, $i, 1) == substr($destCountStr, $i, 1)){
						$digit_pos = substr($countStr, $i, 1);
						$html_output .= "<img src=\"".$this->config['img']."$digit_pos.gif\"";
					} else {
						$digit_pos = substr($destCountStr, $i, 1);
						$html_output .= "<img src=\"".$this->config['animated_img']. $this->config['animated_img_prefix'] . "$digit_pos.gif\"";
					}
					$html_output .= " width=\"".$this->config['width']."\" height=\"".$this->config['height']."\" style=\"border:0;margin:0;padding:0;\" />";
				}
			} else {
				for ($i = 0; $i < strlen($countStr); $i++){
					$digit_pos = substr($destCountStr, $i, 1);
					$html_output .= "<img src=\"".$this->config['img']."$digit_pos.gif\"";
					$html_output .= " width=\"".$this->config['width']."\" height=\"".$this->config['height']."\" style=\"border:0;margin:0;padding:0;\" />";
				}
			}
	        return $html_output;
		}
    }
}
?>
