/*
 * FCKMarquee2Command
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: marquee.js 3742 2010-10-27 02:28:44Z fishbone $
 * @link       http://www.magic3.org
 */
var editorObj = window.parent.InnerDialogLoaded();
var FCK			= editorObj.FCK ;
var FCKLang		= editorObj.FCKLang ;
var FCKConfig	= editorObj.FCKConfig ;
var FCKTools	= editorObj.FCKTools ;

function Import(aSrc) {
   document.write('<scr'+'ipt type="text/javascript" src="' + aSrc + '"></sc' + 'ript>');
}
Import(FCKConfig.FullBasePath + 'dialog/common/fck_dialog_common.js');

// 選択範囲の読み込み
var marqueeObj = FCK.Selection.GetSelectedElement();
/*if (!marqueeObj || marqueeObj.tagName != 'MARQUEE')
{
	marqueeObj = FCK.Selection.GetParentElement() ;
	if (!marqueeObj || marqueeObj.tagName != 'MARQUEE') marqueeObj = null ;
}*/

function OnLoad()
{
	// 各言語対応の変換
	editorObj.FCKLanguageManager.TranslatePage(document);

	// カラーテーブルの作成
	CreateBasicColorTable();
	CreateColorTable();

	// 設定データ読み込み
	LoadSelection();
	
	// ボタンの設定
	window.parent.SetOkButton(true);
	window.parent.SetAutoSize(true);
}

function CreateColorTable()
{
	// Get the target table.
	var oTable = document.getElementById('ColorTable');

	// Create the base colors array.
	var aColors = ['00','33','66','99','cc','ff'];

	// This function combines two ranges of three values from the color array into a row.
	function AppendColorRow( rangeA, rangeB )
	{
		for ( var i = rangeA; i < rangeA + 3; i++ )
		{
			var oRow = oTable.insertRow(-1);

			for ( var j = rangeB; j < rangeB + 3; j++ )
			{
				for ( var n = 0; n < 6; n++ )
				{
					AppendColorCell( oRow, '#' + aColors[j] + aColors[n] + aColors[i] );
				}
			}
		}
	}

	// This function create a single color cell in the color table.
	function AppendColorCell( targetRow, color )
	{
		var oCell = targetRow.insertCell(-1);
		oCell.className = 'ColorCell';
		oCell.bgColor = color;

		oCell.onmouseover = function()
		{
			document.getElementById('hicolor').style.backgroundColor = this.bgColor;
			document.getElementById('hicolortext').innerHTML = this.bgColor;
		}

		oCell.onclick = function()
		{
			document.getElementById('selhicolor').style.backgroundColor = this.bgColor;
			document.getElementById('txtBgcolor').value = this.bgColor;
		}
	}

	AppendColorRow( 0, 0 );
	AppendColorRow( 3, 0 );
	AppendColorRow( 0, 3 );
	AppendColorRow( 3, 3 );
}

function CreateBasicColorTable()
{
	var oTable = document.getElementById('BasicColorTable') ;
    var aColors = ['#000000', '#333333', '#666666', '#999999', '#cccccc', '#ffffff',
                   '#ff0000', '#00ff00', '#0000ff', '#ffff00', '#00ffff', '#ff00ff'] ;
    for (var i = 0 ; i < 12 ; i++ )
    {
        var oRow = oTable.insertRow(-1) ;
        AppendColorCell(oRow, aColors[i] );
    }
	// This function create a single color cell in the color table.
	function AppendColorCell( targetRow, color )
	{
		var oCell = targetRow.insertCell(-1);
		oCell.className = 'ColorCell';
		oCell.bgColor = color;

		oCell.onmouseover = function()
		{
			document.getElementById('hicolor').style.backgroundColor = this.bgColor;
			document.getElementById('hicolortext').innerHTML = this.bgColor;
		}

		oCell.onclick = function()
		{
			document.getElementById('selhicolor').style.backgroundColor = this.bgColor;
			document.getElementById('txtBgcolor').value = this.bgColor;
		}
	}
}

function Clear()
{
	document.getElementById('selhicolor').style.backgroundColor = '';
	document.getElementById('txtBgcolor').value = '';
}

function ClearActual()
{
	document.getElementById('hicolor').style.backgroundColor = '';
	document.getElementById('hicolortext').innerHTML = '&nbsp;';
}

function UpdateColor()
{
	try		  { document.getElementById('selhicolor').style.backgroundColor = document.getElementById('txtBgcolor').value; }
	catch (e) { Clear(); }
}

function Ok()
{
	if (marqueeObj){
		SetAttribute(marqueeObj, 'style', '');
		if (GetE('cmbDirection').value != '') SetAttribute(marqueeObj, '-wap-marquee-dir', GetE('cmbDirection').value);
		if (GetE('cmbBehavior').value != '') SetAttribute(marqueeObj, '-wap-marquee-style', GetE('cmbBehavior').value);
		if (GetE('txtBgcolor').value != '') SetAttribute(marqueeObj, 'background-color', GetE('txtBgcolor').value);
		if (GetE('txtLoop').value && GetE('txtLoop').value.match(/^\d{1,2}$/)) SetAttribute(marqueeObj, '-wap-marquee-loop', GetE('txtLoop').value);
	} else {
		var styleDef = FCK.Commands.LoadedCommands.Marquee2.styleDef ;
		if (GetE('cmbDirection').value != '') styleDef.Styles['-wap-marquee-dir'] = GetE('cmbDirection').value;
		if (GetE('cmbBehavior').value != '') styleDef.Styles['-wap-marquee-style'] = GetE('cmbBehavior').value;
		if (GetE('txtBgcolor').value != '') styleDef.Styles['background-color'] = GetE('txtBgcolor').value;
		if (GetE('txtLoop').value && GetE('txtLoop').value.match(/^\d{1,2}$/)) styleDef.Styles['-wap-marquee-loop'] = GetE('txtLoop').value;

		// 選択範囲に反映
		FCK.Commands.LoadedCommands.Marquee2.ApplyStyle();
	}
	return true;
}

function LoadSelection()
{
	if (!marqueeObj) return;

	GetE('cmbDirection').value    = GetAttribute(marqueeObj, '-wap-marquee-dir', '');
	GetE('cmbBehavior').value	= GetAttribute(marqueeObj, '-wap-marquee-style', '');
    GetE('txtLoop').value	= GetAttribute(marqueeObj, '-wap-marquee-loop', '' );
	GetE('txtBgcolor').value	= marqueeObj.getAttribute('background-color');

    UpdateColor();
}
