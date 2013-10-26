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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
CKEDITOR.dialog.add('linkinfoDialog', function(editor){
	var accessPoint = '0';		// アクセスポイント
	var dialog;					// このダイアログへの参照
	
	// コンテンツリスト、コンテンツ内容表示を更新
	function updateContent()
	{
	}
	return {
		// Basic properties of the dialog window: title, minimum size.
		title: editor.lang.linkinfo.title,
		minWidth: 500,
		minHeight: 300,

		onLoad: function(){
			// 設定変更時の確認ダイアログを非表示にする
			this.on('cancel', function(cancelEvent){ return false; }, this, null, -1);
			
			// このダイアログへの参照を取得
			dialog = this;
		},
		// Dialog window contents definition.
		contents: [
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab_basic',
				label: editor.lang.linkinfo.tab_info_title,

				// The tab contents.
				elements: [
					{	// リンク対象選択
						type : 'radio',
						id : 'link_target',
						label : editor.lang.linkinfo.link_target_title,
						items : [
							[ 'コンテンツ', 'content' ], [ 'ページ', 'page' ], [ 'その他', 'others' ]
						],
						'default': 'content',
						onClick: function(){
							// ダイアログ項目の表示制御
							var selValue = this.getValue();
							switch (selValue){
								case 'content':
									dialog.getContentElement('tab_basic', 'content_type').getElement().show();
									dialog.getContentElement('tab_basic', 'content').getElement().show();
									dialog.getContentElement('tab_basic', 'page').getElement().hide();
									dialog.getContentElement('tab_basic', 'url').getElement().hide();
									dialog.getContentElement('tab_basic', 'content_label').getElement().show();
									dialog.getContentElement('tab_basic', 'content_text').getElement().show();
									break;
								case 'page':
									dialog.getContentElement('tab_basic', 'content_type').getElement().hide();
									dialog.getContentElement('tab_basic', 'content').getElement().hide();
									dialog.getContentElement('tab_basic', 'page').getElement().show();
									dialog.getContentElement('tab_basic', 'url').getElement().hide();
									dialog.getContentElement('tab_basic', 'content_label').getElement().hide();
									dialog.getContentElement('tab_basic', 'content_text').getElement().hide();
									break;
								case 'others':
									dialog.getContentElement('tab_basic', 'content_type').getElement().hide();
									dialog.getContentElement('tab_basic', 'content').getElement().hide();
									dialog.getContentElement('tab_basic', 'page').getElement().hide();
									dialog.getContentElement('tab_basic', 'url').getElement().show();
									dialog.getContentElement('tab_basic', 'content_label').getElement().hide();
									dialog.getContentElement('tab_basic', 'content_text').getElement().hide();
									break;
							}
						}
					},
					{	// コンテンツ種別選択
						type : 'select',
						id : 'content_type',
						label : editor.lang.linkinfo.content_type_title,
						items : [
							[ '接続中', '' ]
						],
						setup: function( element ) {
							alert("setup");
						},
						onLoad : function(){		// 起動時イベント
							var elementId = '#' + this.getInputElement().$.id;

							// Ajaxでページ情報を取得
							m3_ajax_request('', 'task=linkinfo&act=getcontenttype&accesspoint=' + accessPoint, function(request, retcode, jsondata){		// 正常終了
								// コンテンツ種別選択メニューを更新
								$('option', elementId).remove();
								if (jsondata.contenttype){
									$.each(jsondata.contenttype, function(index, item) {
										$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
									});
								}
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});
						},
						onChange : function(){	// 選択値変更時イベント
						}
					},
					{	// コンテンツ選択
						type : 'select',
						id : 'content',
						label : editor.lang.linkinfo.content_list_title,
						items : [
							[ '接続中', '' ]
						],
						onLoad : function(){		// 起動時イベント
							var elementId = '#' + this.getInputElement().$.id;

							// Ajaxでページ情報を取得
/*							m3_ajax_request('', 'task=linkinfo&act=getcontenttype&accesspoint=' + accessPoint, function(request, retcode, jsondata){		// 正常終了
								// コンテンツ種別選択メニューを更新
								$('option', elementId).remove();
								if (jsondata.contenttype){
									$.each(jsondata.contenttype, function(index, item) {
										$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
									});
								}
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});*/
						},
						onChange : function(){	// 選択値変更時イベント
						}
					},
					{
						type : 'select',
						id : 'page',
						label : editor.lang.linkinfo.page_title,
						items : [
							[ '接続中', '' ]
						],
						onLoad : function(){		// 起動時イベント
							this.getElement().hide();		// 初期時は項目を隠す
							
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
						//	var dialog = this.getDialog();
							var elementId = '#' + dialog.getContentElement('tab_basic', 'content').getInputElement().$.id;		// コンテンツ選択メニュー
							var subId = this.getValue();

							// Ajaxでコンテンツ情報を取得
							m3_ajax_request('', 'task=linkinfo&act=getcontentlist&subid=' + subId, function(request, retcode, jsondata){		// 正常終了
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
						type: 'text',
						id: 'url',
						label: editor.lang.linkinfo.url_title,
						width: '100%'
					},
					{
						type : 'html',
						id: 'content_label',
						html : '<label>コンテンツ内容：</label>'
					},
					{
						type: 'html',
						id: 'content_text',
						//padding: '5px',
//						label: editor.lang.linkinfo.content_title,
//						labelLayout: 'horizontal',
						html: '<p>' + editor.lang.googlemaps.msgLineInstruction + '</p>'
					}
				]
			},
			{
				// Definition of the Basic Settings dialog tab (page).
				id: 'tab_advanced',
				label: editor.lang.linkinfo.tab_advanced_title,
				elements: [
					{
						type : 'select',
						id : 'access_point',
						label : editor.lang.linkinfo.access_point_title,
						items : [
							[ '接続中', '' ]
						],
						onLoad : function(){		// 起動時イベント
							var elementId = '#' + this.getInputElement().$.id;

							// Ajaxでページ情報を取得
							m3_ajax_request('', 'task=linkinfo&act=getaccesspoint', function(request, retcode, jsondata){		// 正常終了
								// アクセスポイント選択メニューを更新
								$('option', elementId).remove();
								if (jsondata.accesspoint){
									$.each(jsondata.accesspoint, function(index, item) {
										$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
									});
								}
								// 項目を再選択
								$(elementId).val(accessPoint);	
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});
						}
					}
				]
			}
		],

		// This method is invoked once a user clicks the OK button, confirming the dialog.
		onOk: function() {
//			var dialog = this;
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
