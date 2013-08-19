/*
 * オブジェクト作成時オプション
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
<patTemplate:tmpl name="_tmpl">
// ヘッダーのタイトルとボタン
header: {
	// title, prev, next, prevYear, nextYear, today
	left: 'prev,next today',
	center: 'title',
	//right: 'month agendaWeek agendaDay'
	right: ''
},
// 最初の曜日
firstDay: 1, // 1:月曜日
// 土曜、日曜を表示
weekends: true,
// 週モード (fixed, liquid, variable)
weekMode: 'fixed',
// 週数を表示
weekNumbers: false,
// コンテンツの高さ(px)
//contentHeight: 600,
// カレンダーの縦横比(比率が大きくなると高さが縮む)
//aspectRatio: 1.35,
</patTemplate:tmpl>
