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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getLibPath() .	'/TCPDF-6.2.13/config/lang/jpn.php');
require_once($gEnvManager->getLibPath() .	'/TCPDF-6.2.13/tcpdf.php');

class pdf_listWidgetContainer extends BaseWidgetContainer
{
	private $fieldInfoArray = array();			// お問い合わせ項目情報
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE_NAME = 'PDF名簿';	// デフォルトのタイトル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// デフォルト値設定
		$inputEnabled = true;			// 入力の許可状態
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// お問い合わせフィールド情報
		
		$act = $request->trimValueOf('act');
		if ($act == 'downloadpdf'){		// PDFダウンロード
			$downloadPdf = false;		// PDFダウンロード可能かどうか
			$postTicket = $request->trimValueOf('ticket');		// POST確認用
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				$downloadPdf = true;		// PDFダウンロード可能かどうか
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			
			if ($downloadPdf){		// PDFダウンロード可能かどうか
				// PDFファイル作成
				$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
				
				// PDFファイルのプロパティ
				$pdf->SetCreator('Magic3 ' . M3_SYSTEM_VERSION);
				//$pdf->SetAuthor('作者名');
				$pdf->SetTitle(self::DEFAULT_TITLE_NAME);
				//$pdf->SetSubject('サブタイトル');
				//$pdf->SetKeywords('キーワード1, キーワード2');
				
				// ヘッダ、フッタなしに設定
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
				$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
				$pdf->setLanguageArray($l);

				// 日本語フォント設定
				$pdf->SetFont('ipagp', '', 12);		// ＩＰＡ Ｐゴシック

				// 1ページ目を準備
				$pdf->AddPage();
				
				// ヘッダ作成
				$header = array('名前', '住所', '電話番号');
				$pdf->SetFillColor(0, 238, 0);
				$pdf->SetTextColor(255);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->SetLineWidth(0.5);
				
				$pdf->SetFont('', 'B');
				$w = array(40, 100, 40);		// カラムの幅
				for($i = 0; $i < count($header); $i++){
					$pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
				}
				$pdf->Ln();
				
				// 行を作成
				$pdf->SetFillColor(224, 235, 255);
				$pdf->SetTextColor(0);
				$pdf->SetFont('');
				$fill = 0;
				$fieldCount = count($this->fieldInfoArray);
				for ($i = 0; $i < $fieldCount; $i++){
					$infoObj = $this->fieldInfoArray[$i];
					$name		= $infoObj->name;// 名前
					$address	= $infoObj->address;		// 住所
					$phone		= $infoObj->phone;		// 電話番号
			
					$pdf->Cell($w[0], 6, $name, 'LR', 0, 'L', $fill);
					$pdf->Cell($w[1], 6, $address, 'LR', 0, 'L', $fill);
					$pdf->Cell($w[2], 6, $phone, 'LR', 0, 'C', $fill);
					$pdf->Ln();
					$fill =! $fill;
				}
				$pdf->Cell(array_sum($w), 0, '', 'T');
				
				// PDF を出力
				$downloadFilename = self::DEFAULT_TITLE_NAME . '.pdf';					// ダウンロード時のファイル名
				$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
				$pdf->Output($tmpFile, "F");

				// ページ作成処理中断
				$this->gPage->abortPage();
				
				// ダウンロード処理
				$ret = $this->gPage->downloadFile($tmpFile, $downloadFilename, true/*実行後ファイル削除*/);
				
				// システム強制終了
				$this->gPage->exitSystem();
			}
		} else {
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
		}
		
		// HTMLサブタイトルを設定
//		$this->gPage->setHeadSubTitle(self::DEFAULT_TITLE_NAME);
		$this->gPage->setHeadSubTitle();			// 共通設定画面の「タイトル」値を使用する

		// 名簿作成
		$fieldCount = $this->createFieldList($inputEnabled);
		if ($fieldCount == 0) $this->tmpl->setAttribute('field_list', 'visibility', 'hidden');
		
		$this->tmpl->addVar("_widget", "field_count", $fieldCount);// 名簿項目数
	}
	/**
	 * 名簿作成
	 *
	 * @param bool $enabled		項目の入力許可状態
	 * @return int	フィールド項目数
	 */
	function createFieldList($enabled)
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$name		= $infoObj->name;// 名前
			$address	= $infoObj->address;		// 住所
			$phone		= $infoObj->phone;		// 電話番号
			
			$row = array(
				'name'		=> $this->convertToDispString($name),	// 名前
				'address'	=> $this->convertToDispString($address),	// 住所
				'phone'		=> $this->convertToDispString($phone)	// 電話番号
			);
			$this->tmpl->addVars('field_list', $row);
			$this->tmpl->parseTemplate('field_list', 'a');
		}
		return $fieldCount;
	}
}
?>
