/**
 * Magic3 CKEditorプラグイン
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: linkinfo.js 5951 2013-04-19 13:11:15Z fishbone $
 * @link       http://www.magic3.org
 */
CKEDITOR.dialog.add( 'linkinfoDialog', function( editor ) {
	return {

		// Basic properties of the dialog window: title, minimum size.
		title: editor.lang.linkinfo.title,
		minWidth: 400,
		minHeight: 200,

		// Dialog window contents definition.
		contents: [
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab_basic',
				label: 'Basic Settings',

				// The tab contents.
				elements: [
					{
						type : 'select',
						id : 'page',
						label : editor.lang.linkinfo.page_title,
						items : [
							[ '接続中', '' ]
						],
						onLoad : function(){		// 起動時イベント
							var elementId = '#' + this.getInputElement().$.id;

							// Ajaxでページ情報を取得
							m3_ajax_request('', 'task=linkinfo&act=getpage', function(request, retcode, jsondata){		// 正常終了
								// ページ選択メニューを更新
								$('option', elementId).remove();
								if (jsondata.pagelist){
									$.each(jsondata.pagelist, function(index, item) {
										$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
									});
								}
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});
						},
						onChange : function(){	// 選択値変更時イベント
							var dialog = this.getDialog();
							var elementId = '#' + dialog.getContentElement('tab_basic', 'content').getInputElement().$.id;		// コンテンツ選択メニュー
							var subId = this.getValue();

							// Ajaxでコンテンツ情報を取得
							m3_ajax_request('', 'task=linkinfo&act=getcontent&subid=' + subId, function(request, retcode, jsondata){		// 正常終了
								// コンテンツ選択メニューを更新
								if (jsondata.contentlist){
									$('option', elementId).remove();
									if (jsondata.contentlist.length > 0){
										$.each(jsondata.contentlist, function(index, item) {
											$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
										});
										dialog.getContentElement('tab_basic', 'content').getElement().show();		// 項目を表示
									} else {
										dialog.getContentElement('tab_basic', 'content').getElement().hide();		// 項目を非表示
									}
								}
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});
						}
					},
					{
						type : 'select',
						id : 'content',
						label : editor.lang.linkinfo.content_title,
						items : [
							[ '接続中', '' ]
						],
						onLoad : function(){		// 起動時イベント
							this.getElement().hide();		// 初期時は項目を隠す
						}
						//onChange : function(){	// 選択値変更時イベント
						//}
					}
				]
			}
		],

		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {
			var dialog = this;
			var subId = dialog.getContentElement('tab_basic', 'page').getValue();
			var contentId = dialog.getContentElement('tab_basic', 'content').getValue();
			
			// 作成したURLを設定
			if (typeof setLinkUrl == 'function'){
				setLinkUrl(subId + contentId);
			}
			// The context of this function is the dialog object itself.
			// http://docs.ckeditor.com/#!/api/CKEDITOR.dialog
/*			var dialog = this;

			// Creates a new <abbr> element.
			var abbr = editor.document.createElement( 'abbr' );

			// Set element attribute and text, by getting the defined field values.
			abbr.setAttribute( 'title', dialog.getValueOf( 'tab_basic', 'title' ) );
			abbr.setText( dialog.getValueOf( 'tab_basic', 'abbr' ) );

			// Now get yet another field value, from the advanced tab.
			var id = dialog.getValueOf( 'tab-adv', 'id' );
			if ( id )
				abbr.setAttribute( 'id', id );

			// Finally, inserts the element at the editor caret position.
			editor.insertElement( abbr );
			   */
		}
	};
});
