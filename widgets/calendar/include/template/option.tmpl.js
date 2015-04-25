/*
 * オブジェクト作成時オプション
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
<patTemplate:tmpl name="_tmpl">
// ヘッダーのタイトルとボタン
header: {
	// title, prev, next, prevYear, nextYear, today
	left: 'prev,next today',
	center: 'title',
	//right: 'month,agendaWeek,agendaDay'
	//right: 'month,basicWeek,basicDay'
	right: ''
},
// コンテンツの高さ(px)
//contentHeight: 600,
// カレンダーの縦横比(比率が大きくなると高さが縮む)
//aspectRatio: 1.35,
// イベントの表示項目数の制限
eventLimit: true,	// カラムの高さで制限
// イベントの時刻表示フォーマット
timeFormat: 'H:mm',
</patTemplate:tmpl>
