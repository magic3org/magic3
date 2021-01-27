/**
 * Magic3対応用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    v1.0
 * @link       http://www.magic3.org
 */
/**
 * 画像を画面サイズに収まるようにリサイズ
 *
 * @return なし
 */
function resizeImage(){
	$('img').each(function(){
		var max = $(window).width();
		var w = $(this).width();
		var h = $(this).height();
		if (w > max) {
			$(this).width(max).height(Math.round((max / w) * h));
		}
	});
}
/**
 * 初期処理
 *
 * @return なし
 */
$(function(){
	// CSSクラス追加
	$('.button').addClass('btn btn-secondary');// ボタンにデフォルトカラーを設定
	$('input[type=text]').addClass('form-control');
	$('pre').removeClass('wiki_pre').addClass('card card-body bg-light');
	
	// 画像リサイズ
//	resizeImage();
//	$(window).resize(function(){ resizeImage(); });
	
	// ツールチップ作成
	$('[data-toggle="tooltip"]').tooltip();
});
