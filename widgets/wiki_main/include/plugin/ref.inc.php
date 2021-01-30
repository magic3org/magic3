<?php
/**
 * refプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// Image refernce plugin
// Include an attached image-file as an inline-image

// File icon image
if (! defined('FILE_ICON'))
	define('FILE_ICON',
	'<img src="' . IMAGE_DIR . 'file.png" width="20" height="20"' .
	' alt="file" style="border-width:0px" />');

/////////////////////////////////////////////////
// Default settings

// Horizontal alignment
define('PLUGIN_REF_DEFAULT_ALIGN', 'left'); // 'left', 'center', 'right'

// URL指定時に画像サイズを取得するか
//define('PLUGIN_REF_URL_GET_IMAGE_SIZE', FALSE); // FALSE, TRUE

// UPLOAD_DIR のデータ(画像ファイルのみ)に直接アクセスさせる
//define('PLUGIN_REF_DIRECT_ACCESS', FALSE); // FALSE or TRUE
// - これは従来のインラインイメージ処理を互換のために残すもので
//   あり、高速化のためのオプションではありません
// - UPLOAD_DIR をWebサーバー上に露出させており、かつ直接アクセス
//   できる(アクセス制限がない)状態である必要があります
// - Apache などでは UPLOAD_DIR/.htaccess を削除する必要があります
// - ブラウザによってはインラインイメージの表示や、「インライン
//   イメージだけを表示」させた時などに不具合が出る場合があります

/////////////////////////////////////////////////

// Image suffixes allowed
define('PLUGIN_REF_IMAGE', '/\.(gif|png|jpe?g)$/i');

// Usage (a part of)
define('PLUGIN_REF_USAGE', "([pagename/]attached-file-name[,parameters, ... ][,title])");

function plugin_ref_inline()
{
	// Not reached, because of "$aryargs[] = $body" at plugin.php
	// if (! func_num_args())
	//	return '&amp;ref(): Usage:' . PLUGIN_REF_USAGE . ';';

	$params = plugin_ref_body(func_get_args());

	if (isset($params['_error']) && $params['_error'] != '') {
		// Error
		return '&amp;ref(): ' . $params['_error'] . ';';
	} else {
		return $params['_body'];
	}
}
/**
 * ブロック型出力処理
 */
function plugin_ref_convert()
{
	global $gEnvManager;

	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	
	if (! func_num_args())
		return '<p>#ref(): Usage:' . PLUGIN_REF_USAGE . "</p>\n";

	// パラメータ解析
	$params = plugin_ref_body(func_get_args());

	if (isset($params['_error']) && $params['_error'] != '') {
		return "<p>#ref(): {$params['_error']}</p>\n";
	}

	if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
		if ($params['around']) {
			$style = ($params['_align'] == 'right') ? 'float:right' : 'float:left';
		} else {
			$style = "text-align:{$params['_align']}";
		}

		if (empty($params['caption'])){
			// Pタグで囲む(Pタグはデフォルトで下マージンが付加される)
			return "<p style=\"$style\">{$params['_body']}</p>\n";
		} else {
			// FIGUREタグはPタグに入らないのでDIVタグで囲む。FIGUREタグの下マージンあり。
			return "<div style=\"$style\">{$params['_body']}</div>\n";
		}
	} else {
		if ($params['around']) {
			$style = ($params['_align'] == 'right') ? 'float:right' : 'float:left';
		} else {
			$style = "text-align:{$params['_align']}";
		}

		// divで包む
		return "<div class=\"image_wrap\" style=\"$style\">{$params['_body']}</div>\n";
	}
}

function plugin_ref_body($args)
{
	global $script;
	global $WikiName, $BracketName; // compat
	global $gEnvManager;
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	
	// 戻り値
	$params = array(
		'left'   => FALSE, // 左寄せ
		'center' => FALSE, // 中央寄せ
		'right'  => FALSE, // 右寄せ
		'wrap'   => FALSE, // TABLEで囲む(廃止)
		'nowrap' => FALSE, // TABLEで囲まない(廃止)
		'around' => FALSE, // 回り込み
		'noicon' => FALSE, // アイコンを表示しない
		'nolink' => FALSE, // 元ファイルへのリンクを張らない
		'noimg'  => FALSE, // 画像を展開しない
		'zoom'   => FALSE, // 縦横比を保持する
		
		// Magic3追加分
		'caption'   => FALSE, // キャプション
		'captionpos'   => FALSE, // キャプションの表示位置
		'margin' 	=> FALSE, // マージン
		'rounded'   => FALSE, // 角丸
		'circle'   	=> FALSE, // 円形
		'thumbnail' => FALSE, // サムネール
		
		// 解析値
		'_size'  => FALSE, // サイズ指定あり
		'_w'     => 0,       // 幅
		'_h'     => 0,       // 高さ
		'_%'     => 0,     // 拡大率
		'_args'  => array(),
		'_done'  => FALSE,
		'_error' => ''
	);

	// 添付ファイルのあるページ: defaultは現在のページ名
	//$page = isset($vars['page']) ? $vars['page'] : '';
	$page = WikiParam::getPage();

	// 添付ファイルのファイル名
	$name = '';

	// 添付ファイルまでのパスおよび(実際の)ファイル名
	$file = '';

	// 第一引数: "[ページ名および/]添付ファイル名"、あるいは"URL"を取得
	$name = array_shift($args);
	$is_url = is_url($name);
	// 「/」で始まる名前はドキュメントルートからの相対URLとする
	if (strncmp($name, '/', 1) == 0) $is_url = true;
	
	// ##### 添付ファイルの場合のパラメータチェック #####
	if (!$is_url){
		// 添付ファイル
		if (! is_dir(UPLOAD_DIR)) {
			$params['_error'] = 'No UPLOAD_DIR';
			return $params;
		}

		$matches = array();
		// ファイル名にページ名(ページ参照パス)が合成されているか
		//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
		if (preg_match('#^(.+)/([^/]+)$#', $name, $matches)) {
			if ($matches[1] == '.' || $matches[1] == '..') {
				$matches[1] .= '/'; // Restore relative paths
			}
			$name = $matches[2];
			$page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
			$file = UPLOAD_DIR . encode($page) . '_' . encode($name);
			$is_file = is_file($file);

/*		// ########## 旧バージョンの書式は削除する ##########
		// 第二引数以降が存在し、それはrefのオプション名称などと一致しない
		} else if (isset($args[0]) && $args[0] != '' && ! isset($params[$args[0]])) {
			$e_name = encode($name);

			// Try the second argument, as a page-name or a path-name
			$_arg = get_fullname(strip_bracket($args[0]), $page); // strip is a compat
			$file = UPLOAD_DIR .  encode($_arg) . '_' . $e_name;
			$is_file_second = is_file($file);

			// If the second argument is WikiName, or double-bracket-inserted pagename (compat)
			$is_bracket_bracket = preg_match("/^($WikiName|\[\[$BracketName\]\])$/", $args[0]);

			if ($is_file_second && $is_bracket_bracket) {
				// Believe the second argument (compat)
				array_shift($args);
				$page = $_arg;
				$is_file = TRUE;
			} else {
				// Try default page, with default params
				$is_file_default = is_file(UPLOAD_DIR . encode($page) . '_' . $e_name);

				// Promote new design
				if ($is_file_default && $is_file_second) {
					// Because of race condition NOW
					$params['_error'] = htmlspecialchars('The same file name "' .
						$name . '" at both page: "' .  $page . '" and "' .  $_arg .
						'". Try ref(pagename/filename) to specify one of them');
				} else {
					// Because of possibility of race condition, in the future
					$params['_error'] = 'The style ref(filename,pagename) is ambiguous ' .
						'and become obsolete. ' .
						'Please try ref(pagename/filename)';
				}
				return $params;
			}*/
		} else {	// 添付ファイル名のみの指定の場合
			// Simple single argument
			$file = UPLOAD_DIR . encode($page) . '_' . encode($name);
			$is_file = is_file($file);
		}
		if (! $is_file) {
			$params['_error'] = htmlspecialchars('File not found: "' . $name . '" at page "' . $page . '"');
			return $params;
		}
	}

	// 残りの引数の処理
	if (! empty($args))
		foreach ($args as $arg)
			ref_check_arg($arg, $params);

/*
 $nameをもとに以下の変数を設定
 $url,$url2 : URL
 $title :タイトル
 $is_image : 画像のときTRUE
 $info : 画像ファイルのときgetimagesize()の'size'
         画像ファイル以外のファイルの情報
         添付ファイルのとき : ファイルの最終更新日とサイズ
         URLのとき : URLそのもの
*/
	$title = $url = $url2 = $info = '';
	$width = $height = 0;
	$matches = array();
	
	$is_image = (! $params['noimg'] && preg_match(PLUGIN_REF_IMAGE, $name));	// 画像かどうか

	if ($is_url) {	// URL
		if (PKWK_DISABLE_INLINE_IMAGE_FROM_URI) {
			//$params['_error'] = 'PKWK_DISABLE_INLINE_IMAGE_FROM_URI prohibits this';
			//return $params;
			$url = htmlspecialchars($name);
			$params['_body'] = '<a href="' . $url . '">' . $url . '</a>';
			return $params;
		}

		$url = $url2 = htmlspecialchars($name);
		$title = htmlspecialchars(preg_match('/([^\/]+)$/', $name, $matches) ? $matches[1] : $url);

//		$is_image = (! $params['noimg'] && preg_match(PLUGIN_REF_IMAGE, $name));

		// ### 相対URLの場合のみ画像の情報を取得(Magic3仕様) ###
//		if ($is_image && PLUGIN_REF_URL_GET_IMAGE_SIZE && (bool)ini_get('allow_url_fopen')) {
		if ($is_image && strncmp($name, '/', 1) == 0){
			$imagePath = $gEnvManager->getAbsolutePath($gEnvManager->getDocumentRootUrl() . $name);

/*			$size = @getimagesize($imagePath);
			if (is_array($size)) {
				$width  = $size[0];
				$height = $size[1];
				$info   = $size[3];
			}*/
		}

	} else { // 添付ファイル

		$title = htmlspecialchars($name);

//		$is_image = (! $params['noimg'] && preg_match(PLUGIN_REF_IMAGE, $name));

		// Count downloads with attach plugin
		//$url = $script . '?plugin=attach' . '&amp;refer=' . rawurlencode($page) .
		//	'&amp;openfile=' . rawurlencode($name); // Show its filename at the last
		$url = $script . WikiParam::convQuery('?plugin=attach' . '&amp;refer=' . rawurlencode($page) . '&amp;openfile=' . rawurlencode($name)); // Show its filename at the last

		if ($is_image) {
			// Swap $url
			$url2 = $url;

			// 画像参照用のURLはプラグイン経由のみとする
			// URI for in-line image output
			//if (! PLUGIN_REF_DIRECT_ACCESS) {
				// With ref plugin (faster than attach)
				//$url = $script . '?plugin=ref' . '&amp;page=' . rawurlencode($page) . '&amp;src=' . rawurlencode($name); // Show its filename at the last
				$url = $script . WikiParam::convQuery('?plugin=ref' . '&amp;page=' . rawurlencode($page) . '&amp;src=' . rawurlencode($name)); // Show its filename at the last
			//} else {
			//	// Try direct-access, if possible
			//	$url = $file;
			//}
			
			// 画像パス取得
			$imagePath = $file;
/*
			$width = $height = 0;
			$size = @getimagesize($file);
			if (is_array($size)) {
				$width  = $size[0];
				$height = $size[1];
			}*/
		} else {
			$info = get_date('Y/m/d H:i:s', filemtime($file) - LOCALZONE) .
				' ' . sprintf('%01.1f', round(filesize($file)/1024, 1)) . 'KB';
		}
	}

	// 拡張パラメータをチェック
	if (! empty($params['_args'])) {
		$_title = array();
		foreach ($params['_args'] as $arg) {
			if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $matches)) {	// 「nnnxnnn」フォーマットで画像サイズ指定の場合
				$params['_size'] = TRUE;
				$params['_w'] = $matches[1];
				$params['_h'] = $matches[2];
			} else if (preg_match('/^([0-9.]+)%$/', $arg, $matches) && $matches[1] > 0) {	// 「nnn%」フォーマットで画像サイズ指定の場合
				$params['_%'] = $matches[1];
			} else {
				$_title[] = $arg;
			}
		}

		if (! empty($_title)) {
			$title = htmlspecialchars(join(',', $_title));
			if ($is_image) $title = make_line_rules($title);
		}
	}

	// 画像サイズ調整
	if ($is_image) {
		// 幅、高さの計算が必要な場合は画像の情報を取得
		if (!empty($imagePath) && ($params['_size'] || $params['_%'])){
			$size = @getimagesize($imagePath);
			if (is_array($size)) {
				$width  = $size[0];
				$height = $size[1];
				$info   = $size[3];
			}
		}
		
		// 指定されたサイズを使用する
		if ($params['_size']) {
			if ($width == 0 && $height == 0) {
				$width  = $params['_w'];
				$height = $params['_h'];
			} else if ($params['zoom']) {
				$_w = $params['_w'] ? $width  / $params['_w'] : 0;
				$_h = $params['_h'] ? $height / $params['_h'] : 0;
				$zoom = max($_w, $_h);
				if ($zoom) {
					$width  = (int)($width  / $zoom);
					$height = (int)($height / $zoom);
				}
			} else {
				$width  = $params['_w'] ? $params['_w'] : $width;
				$height = $params['_h'] ? $params['_h'] : $height;
			}
		}
		if ($params['_%']) {
			$width  = (int)($width  * $params['_%'] / 100);
			$height = (int)($height * $params['_%'] / 100);
		}
		if ($width && $height) $info = "width=\"$width\" height=\"$height\" ";
	}

	// アラインメント判定
	$params['_align'] = PLUGIN_REF_DEFAULT_ALIGN;
	foreach (array('right', 'left', 'center') as $align) {
		if ($params[$align])  {
			$params['_align'] = $align;
			break;
		}
	}

	$optionAttr = '';	// 追加属性
	if (intval($templateType / 10) * 10 == M3_TEMPLATE_BOOTSTRAP_30){	// Bootstrap型テンプレートの場合
		$optionAttr = 'data-toggle="tooltip"';	// ツールチップを付加
	}
	
	if ($is_image) { // 画像
		$class = '';
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
			$classArray = array();
			
			// キャプション付きの場合
			if (!empty($params['caption'])) $classArray[] = 'figure-img';
			
			//if (WikiParam::getIsInline()){		// インライン型表示のとき
			//	//$class = 'class="float-left"';
			//} else {	// ブロック型表示のとき
				// 画像の幅がコンテナに内に収まるように調整する
				$classArray[] = 'img-fluid';
			//}
			
			// 画像マージンクラスの追加
			$marginClass = $params['margin'];
			if (!empty($marginClass)){
				if (is_numeric($marginClass)){
					$classArray[] = 'mx-' . intval($marginClass);
					$classArray[] = 'my-' . intval($marginClass);
				} else {
					$classArray = array_merge($classArray, explode(' ', $marginClass));
				}
			}
			
			// 画像表現を追加
			if ($params['rounded']) $classArray[] = 'rounded';	// 角丸
			if ($params['circle']) $classArray[] = 'rounded-circle';	// 円形
			if ($params['thumbnail']) $classArray[] = 'img-thumbnail';	// 枠線
			
			if (count($classArray) > 0) $class = 'class="' . implode(' ', $classArray) . '"';
		}
		
		$params['_body'] = "<img src=\"$url\" alt=\"$title\" title=\"$title\" $class $info $optionAttr />";
		if (!$params['nolink'] && $url2) $params['_body'] = "<a href=\"$url2\" title=\"$title\">{$params['_body']}</a>";
		
		// ### キャプション付きの場合はFIGUREタグで囲む ###
		if (!empty($params['caption'])){
			$captionClass = '';
			$captionClassArray = array();
			$captionClassArray[] = 'figure-caption';
			
			switch ($params['captionpos']){
				case 'top-left':
				case 'bottom-left':
				default:
					$captionClassArray[] = 'text-left';
					break;
				case 'top-center':
				case 'bottom-center':
					$captionClassArray[] = 'text-center';
					break;
				case 'top-right':
				case 'bottom-right':
					$captionClassArray[] = 'text-right';
					break;
			}
			if (count($captionClassArray) > 0) $captionClass = 'class="' . implode(' ', $captionClassArray) . '"';
			
			if ($params['captionpos'] == 'top-left' || $params['captionpos'] == 'top-center' || $params['captionpos'] == 'top-right'){
				$params['_body'] = '<figure class="figure"><figcaption ' . $captionClass . '>' . htmlspecialchars($params['caption']) . '</figcaption>' . $params['_body'] . '</figure>';
			} else {
				$params['_body'] = '<figure class="figure">' . $params['_body'] . '<figcaption ' . $captionClass . '>' . htmlspecialchars($params['caption']) . '</figcaption></figure>';
			}
		}
	} else {
		$icon = $params['noicon'] ? '' : FILE_ICON;
		$params['_body'] = "<a href=\"$url\" title=\"$info\" $optionAttr>$icon$title</a>";
	}

	return $params;
}

// オプションを解析する
// オプションの先頭から規定のオプションパラメータを取得する。
// 直接値が設定されるnnnxnnnやnnn%やタイトルが検出された時点で規定のオプションパラメータの取得は終了し、残りは$params['_args']に格納する。
function ref_check_arg($val, & $params)
{
	if ($val == '') {
		$params['_done'] = TRUE;
		return;
	}

	// 「=」が含まれている場合はオプションキーを解析
	list($optionKey, $optionValue) = explode('=', $val);
	$optionKey = strtolower($optionKey);
			
	if (! $params['_done']) {
		foreach (array_keys($params) as $key) {
//			if (strpos($key, strtolower($val)) === 0) {
//				$params[$key] = TRUE;
			if (strpos($key, $optionKey) === 0) {
				if (empty($optionValue)){
					$params[$key] = TRUE;
				} else {
					$params[$key] = $optionValue;	// 「=」の右側の値を取得
				}
				return;
			}
		}
		// 既定のオプションパラメータ以外を検出した場合はそこで解析を終了する
		$params['_done'] = TRUE;
	}

	// 処理できなかったオプションパラメータはそのまま保存
	$params['_args'][] = $val;
}

// Output an image (fast, non-logging <==> attach plugin)
function plugin_ref_action()
{
	//global $vars;
	global $gPageManager;

	$usage = 'Usage: plugin=ref&amp;page=page_name&amp;src=attached_image_name';

/*
	if (! isset($vars['page']) || ! isset($vars['src']))
		return array('msg'=>'Invalid argument', 'body'=>$usage);

	$page     = $vars['page'];
	$filename = $vars['src'] ;
	*/
	$page     = WikiParam::getPage();
	$filename = WikiParam::getVar('src');
	
	if ($page == '' || $filename == '') return array('msg'=>'Invalid argument', 'body'=>$usage);
		
	$ref = UPLOAD_DIR . encode($page) . '_' . encode(preg_replace('#^.*/#', '', $filename));
	if(! file_exists($ref))
		return array('msg'=>'Attach file not found', 'body'=>$usage);

	$got = @getimagesize($ref);
	if (! isset($got[2])) $got[2] = FALSE;
	switch ($got[2]) {
	case 1: $type = 'image/gif' ; break;
	case 2: $type = 'image/jpeg'; break;
	case 3: $type = 'image/png' ; break;
	case 4: $type = 'application/x-shockwave-flash'; break;
	default:
		return array('msg'=>'Seems not an image', 'body'=>$usage);
	}

	// Care for Japanese-character-included file name
	if (LANG == 'ja') {
		switch(UA_NAME . '/' . UA_PROFILE){
		case 'Opera/default':
			// Care for using _auto-encode-detecting_ function
			$filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
			break;
		case 'MSIE/default':
			$filename = mb_convert_encoding($filename, 'SJIS', 'auto');
			break;
		}
	}
	$file = htmlspecialchars($filename);
	$size = filesize($ref);

	// ページ作成処理中断
	$gPageManager->abortPage();
	
	// Output
	pkwk_common_headers();
	header('Content-Disposition: inline; filename="' . $filename . '"');
	header('Content-Length: ' . $size);
	header('Content-Type: '   . $type);
	@readfile($ref);
	//exit;
	
	// システム強制終了
	$gPageManager->exitSystem();
}
?>
