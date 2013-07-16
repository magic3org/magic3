/**
 * Magic3Ajaxライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m3opt1.1.0.js 3414 2010-07-22 01:52:56Z fishbone $
 * @link       http://www.magic3.org
 */
/**
 * Ajax非同期通信
 * @param string request_widget		指定ウィジェット
 * @param string request_params		リクエストパラメータ
 * @param function success_func(request, retcode, jsondata)	通信成功時の呼び出し関数
 * @param function failure_func(request)					通信失敗時の呼び出し関数
 * @param string request_url		リクエスト先URL
 */
function m3_ajax_request(request_widget, request_params, success_func, failure_func, request_url)
{
	if (request_url == null || request_url == "") request_url = document.location.pathname;

	var params = "";
	if (request_widget != null && request_widget != "") params += "cmd=dowidget&widget=" + request_widget;
	if (request_params != null && request_params != "") params += "&" + request_params;
	
	$.ajax({	url: request_url,
				type:		'post',
				data:		params,
				dataType:	'json',
				success:	function(data, textStatus){
								if (data) alert("JSON data must be in header with 'X-JSON' type");
							},
				error:		function(request, textStatus, errorThrown){
								if (request.status == 200){
									var json = eval(request.getResponseHeader("X-JSON"));
									m3_ajax_success(request, json, success_func);
								} else {
									m3_ajax_failure(request, json, failure_func);
								}
							}
			});
}
/**
 * Ajax非同期通信正常時に呼ばれるデフォルト関数
 * @param XMLHttpRequest	request		サーバからのレスポンス
 * @param Object			json		JSON型データ
 */
function m3_ajax_success(request, json, success_func)
{
	if (success_func == ''){
		alert(request.statusText + "\nstatus code = " + request.status);
	} else if (success_func){
		if (typeof success_func == "function"){
			var retcode = -1;
			var jsondata;
			if (json != null){
				retcode = json.retcode;
				jsondata = json.data;
			}
			success_func(request, retcode, jsondata);
		} else {
			alert('cannot found success function');
		}
	}
}
/**
 * Ajax非同期通信の通信エラー時に呼ばれるデフォルト関数
 * @param XMLHttpRequest	request		サーバからのレスポンス
 */
function m3_ajax_failure(request, json, failure_func)
{
	if (failure_func == ''){
		alert(request.statusText + "\nstatus code = " + request.status);
	} else if (failure_func){
		if (typeof failure_func == "function"){
			failure_func(request);
		} else {
			alert('cannot found failure function');
		}
	}
}
/**
 * テーブルに縦のスクロールバー付加
 *
 *  テーブルの表示領域を指定行数に制限し、テーブルに縦のスクロールバー付加する
 *
 * @param object  object			テーブルオブジェクトまたはテーブルID文字列
 * @param int     viewLineCount 	表示行数(-1のときはスクロールバーなし)
 * @param int     viewTopLineNo 	先頭に表示する行番号(0～)
 * @return なし
 */
function m3_tableWithScrollbar(object, viewLineCount, viewTopLineNo)
{
	var tHeight;		// 表示高さ
	var srcTable;		// テーブルオブジェクト
	
	if(!document.createElement) return;
	if(navigator.userAgent.match('Opera')) return;

	// テーブルのオブジェクトを取得
	if (typeof object == "string"){
		srcTable = document.getElementById(object);
	} else {
		srcTable = object;
	}
	if (!srcTable) return;
	var hasHead = (srcTable.tHead == null) ? false : true;		// ヘッダがあるかどうか
	var hasFoot = (srcTable.tFoot == null) ? false : true;		// フッタがあるかどうか
	
	// データ行数が表示行数よりも少ないときは終了
	var bodyRowCount = srcTable.tBodies[0].rows.length;	// データ行数
	if (viewLineCount < 0 || bodyRowCount <= viewLineCount){
		// テーブルのカラー設定
		m3_tableWithCololor(srcTable, true);
		return;
	}
	
	// カラム数のエラーチェック
	if (srcTable.tHead.rows[0].cells.length != srcTable.tBodies[0].rows[0].cells.length){
		alert('カラム数にエラーがあります\nヘッダカラム数=' + srcTable.tHead.rows[0].cells.length +
			 ',ボディカラム数=' + srcTable.tBodies[0].rows[0].cells.length);
		return;
	}
	if (hasFoot){
		if (srcTable.tFoot.rows[0].cells.length != srcTable.tBodies[0].rows[0].cells.length){
			alert('カラム数にエラーがあります\nフッタカラム数=' + srcTable.tFoot.rows[0].cells.length +
				 ',ボディカラム数=' + srcTable.tBodies[0].rows[0].cells.length);
			return;
		}
	}
	// カラムの幅をヘッダのカラム幅に合わせる
	for(var i = 0; i < srcTable.tHead.rows[0].cells.length; i++) {
		srcTable.tHead.rows[0].cells[i].style.width = 
		srcTable.tBodies[0].rows[0].cells[i].style.width = 
			(srcTable.tHead.rows[0].cells[i].clientWidth - srcTable.cellPadding * 2)+ 'px';
		if (hasFoot) srcTable.tFoot.rows[0].cells[i].style.width = srcTable.tHead.rows[0].cells[i].style.width;
	}

	// ヘッダ部、フッタ部の高さを退避
	var thHeight = srcTable.tHead.offsetHeight;
	if (hasFoot) var tfHeight = srcTable.tFoot.offsetHeight;

	srcTable.style.width = srcTable.offsetWidth + 'px';
	var tWidth = srcTable.offsetWidth;	// ヘッダ、フッタ、データ領域の幅

	// テーブルを複製、tbodyの中身を削除
	var destTableHead = srcTable.cloneNode(true);
	while (destTableHead.tBodies[0].rows.length) {
		destTableHead.tBodies[0].deleteRow(0);
	}

	// 新規DIV - ヘッダ部用を作成
	var newDivHead = document.createElement('div');
	newDivHead.style.width = tWidth+'px';
	newDivHead.style.height = thHeight+'px';
	newDivHead.style.overflow = 'hidden';
	newDivHead.style.position = 'relative';
	destTableHead.style.position = 'absolute';
	destTableHead.style.left = '0';
	destTableHead.style.top = '0';
	newDivHead.appendChild(destTableHead);
	srcTable.parentNode.insertBefore(newDivHead, srcTable);
	
	// テーブルの複製を作成　ヘッダ部を削除
	var destTableBody = srcTable.cloneNode(true);
	destTableBody.deleteTHead();
	if (hasFoot) destTableBody.deleteTFoot();	// フッタ削除

	// 新規DIV - ボディ部用を作成
	var newDivBody = document.createElement('div');
	newDivBody.style.width = (tWidth+18)+'px';
	newDivBody.style.overflow = 'auto';
	newDivBody.appendChild(destTableBody);
	srcTable.parentNode.insertBefore(newDivBody, srcTable);
	
	// データ表示領域のサイズ設定
	var rowHeight = destTableBody.clientHeight / bodyRowCount;	// データ行の高さ
	tHeight = rowHeight * viewLineCount;
	newDivBody.style.height = tHeight + 'px';
	newDivBody.scrollTop = rowHeight * viewTopLineNo;		// 表示領域の先頭行を設定

	// 新規DIV - フッタ部用を作成
	if (hasFoot){
		// テーブルの複製
		var destTableFoot = srcTable.cloneNode(true);
		while (destTableFoot.tHead.rows.length) {
			destTableFoot.tHead.deleteRow(0);
		}
		while (destTableFoot.tBodies[0].rows.length) {
			destTableFoot.tBodies[0].deleteRow(0);
		}
		
		var newDivFoot = document.createElement('div');
		newDivFoot.style.width = tWidth+'px';
		newDivFoot.style.height = tfHeight+'px';
		newDivFoot.style.overflow = 'hidden';
		newDivFoot.style.position = 'relative';
		destTableFoot.style.position = 'absolute';
		destTableFoot.style.left = '0';
		destTableFoot.style.top = '0';
		newDivFoot.appendChild(destTableFoot);
		srcTable.parentNode.insertBefore(newDivFoot, srcTable);
	}
	
	// 元テーブルを削除
	srcTable.parentNode.removeChild(srcTable);
	
	// テーブルのカラー設定
	m3_tableWithCololor(destTableBody, true);
}
/**
 * テーブル行のカラー設定
 *
 *  テーブル行の行ごとに色分け、カレント行の色分けのためのクラス設定を行う
 *
 * @param object  object			テーブルオブジェクトまたはテーブルID文字列
 * @param bool    withMouse			マウスに合わせて色を変えるかどうか
 * @return なし
 */
function m3_tableWithCololor(object, withMouse)
{
	var tableObj;		// テーブルオブジェクト
	
	if (typeof object == "string"){
		tableObj = document.getElementById(object);
	} else {
		tableObj = object;
	}
	// テーブル行のカラー設定
	$(tableObj).find('tbody tr:odd').addClass("even");
	
	// マウスオーバーイベントへの対応
	if (!withMouse) return;
	$(tableObj).find('tbody tr').mouseover(function(){
		$(this).addClass("ruled");
	}).mouseout(function(){
		$(this).removeClass("ruled");
	}).click(function(){
		$(this).toggleClass("selected");
	});
}
