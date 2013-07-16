/*
 * FCKEmojiCommand
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: emoji.js 3480 2010-08-16 09:27:01Z fishbone $
 * @link       http://www.magic3.org
 */
var editorObj = window.parent.InnerDialogLoaded();
var FCK			= editorObj.FCK;
var FCKLang		= editorObj.FCKLang;
var FCKConfig	= editorObj.FCKConfig;
var FCKTools	= editorObj.FCKTools;

// スクリプト読み込み
function Import(aSrc) {
   document.write('<scr'+'ipt type="text/javascript" src="' + aSrc + '"></sc' + 'ript>');
}
Import(FCKConfig.FullBasePath + 'dialog/common/fck_dialog_common.js');
Import('../../../../m3/emoji.js');

// 画像パス設定
var imageDir = '../../../../../images/system/emoji/';

function OnLoad()
{
	// 各言語対応の変換
	editorObj.FCKLanguageManager.TranslatePage(document);

	// 設定データ読み込み
	LoadSelection();
	
	// ボタンの設定
	window.parent.SetOkButton(true);
	window.parent.SetAutoSize(true);
}

function Ok()
{
	var image = GetE('sel_image');
	if (image && image.src){
		var imgObj = editorObj.FCK.InsertElement('img');
		imgObj.src = GetE('sel_image').src;
	}
	return true;
}
// 絵文字プレビュー表示
function showEmojiPreview(code)
{
	var name = m3EojiImages[code]['name'];
	
	// プレビュー絵文字の名前
	GetE('previewimagetext').innerHTML = name;
	
	var filenameI = m3EojiImages[code]['i'];		// i-mode画像
	var filenameE = m3EojiImages[code]['e'];		// ez-web画像
	var filenameS = m3EojiImages[code]['s'];		// softbank画像
	
	var imgPathI = imageDir + filenameI + '.gif';
	imageI = GetE('image_i');
	imageI.src = imgPathI;
	imageI.style.display = '';	// 画像を表示
}
// 絵文字を選択したときの処理
function selectEmoji(code)
{
	var name = m3EojiImages[code]['name'];
	
	// 選択絵文字の名前
	GetE('selimagetext').innerHTML = name;
	
	var filenameI = m3EojiImages[code]['i'];		// i-mode画像
	
	var imgPathI = imageDir + filenameI + '.gif?code=' + code;
	image = GetE('sel_image');
	image.src = imgPathI;
	image.style.display = '';	// 画像を表示
}
// 設定値読み込み
function LoadSelection()
{
	// 絵文字を選択している場合は取得
	var imgObj = FCK.Selection.GetSelectedElement() ;
	if (imgObj && imgObj.tagName != 'IMG' && !(imgObj.tagName == 'INPUT' && imgObj.type == 'image')) imgObj = null;
	if (!imgObj) return;

	var image = GetE('sel_image');
	if (image){
		image.src = GetAttribute(imgObj, 'src', '');
		image.style.display = '';	// 画像を表示
	}
}

function create_emojimap()
{
	var imageWidth = 400;
	var imageHeight = 260;
	var iconSize = 20;		// 個別のアイコンの高さ、幅
	var data = '';
	data += '<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" >';
	data += '<tr>';
	data += '<td>';
	data += '<img id="emojiimg" src="emoji_icons.gif" usemap="#emojimap" border="1" width="' + imageWidth + '" height="' + imageHeight + '">';
	data += "<map name='emojimap'>";

	// 画像へのクリックマップを作成
	var px1 = 0; //左上のx座標
	var py1 = 0; //左上のy座標
	var px2 = px1 + iconSize; //右下のx座標
	var py2 = py1 + iconSize; //右下のy座標

	// 基本絵文字176文字を設定
	for (var i = 0; i < 176; i++){
		var code = i;
		var tip = m3EojiImages[i]['name'];
		
		// タグを出力
		data += '<area shape="rect" onmouseover="showEmojiPreview(\'' + code + '\');" coords="' + px1 +',' + py1 + ',' + px2 + ',' + py2 + '" alt="' + tip + '" href="javascript:selectEmoji(\'' + code + '\');">';

		// 座標を更新
		px1 += iconSize;
		px2 = px1 + iconSize;

		// マップの右端に到達すると改行
		if (px2 > imageWidth){
			px1 = 0;
			py1 += iconSize;
			px2 = px1 + iconSize;
			py2 = py1 + iconSize;
		}
	}

	// 拡張絵文字を設定
	for (var i = 0; i < 76; i++){
		var code = i + 176;
		var tip = m3EojiImages[i + 176]['name'];
		
		// タグを出力
		data += '<area shape="rect" onmouseover="showEmojiPreview(\'' + code + '\');" coords="' + px1 + ',' + py1 + ',' + px2 + ',' + py2 + '" alt="' + tip + '" href="javascript:selectEmoji(\'' + code + '\');">';

		// 座標を更新
		px1 += iconSize;
		px2 = px1 + iconSize;

		// マップの右端に到達すると改行
		if (px2 > imageWidth){
			px1 = 0;
			py1 += iconSize;
			px2 = px1 + iconSize;
			py2 = py1 + iconSize;
		}
	}
	data += "</map>";
//	data += '<br>';
	data += '</td>';
	data += '</tr>';
	data += '</table>';
	return data;
}

