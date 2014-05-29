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
	var accessPoint = '';		// アクセスポイント
	var dialog;					// このダイアログへの参照
	
	// コンテンツリスト、コンテンツ内容表示を更新
	function updateContentList()
	{
		// コンテンツリストを取得
		var elementId = '#' + dialog.getContentElement('tab_basic', 'content_list').getInputElement().$.id;
		var contentType = dialog.getContentElement('tab_basic', 'content_type').getValue();
		var pageNo = 1;

		// コンテンツプレビュークリア
		dialog.getContentElement('tab_basic', 'url').setValue('');
		$('#content_text').text('');
		
		// Ajaxでページ情報を取得
		m3_ajax_request('', 'task=linkinfo&act=getcontentlist&contenttype=' + contentType + '&accesspoint=' + accessPoint + '&page=' + pageNo, function(request, retcode, jsondata){		// 正常終了
			// コンテンツ種別選択メニューを更新
			$('option', elementId).remove();
			if (jsondata.contentlist){
				$.each(jsondata.contentlist, function(index, item) {
					$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
				});
			}
		}, function(request){		// 異常終了
			alert('通信に失敗しました。');
		});
	}
		// コンテンツタイプを取得
	function updateContentType()
	{
		var elementId = '#' + dialog.getContentElement('tab_basic', 'content_type').getInputElement().$.id;
		
		// Ajaxでコンテンツタイプを取得
		m3_ajax_request('', 'task=linkinfo&act=getcontenttype&accesspoint=' + accessPoint, function(request, retcode, jsondata){		// 正常終了
			// コンテンツ種別選択メニューを更新
			$('option', elementId).remove();
			if (jsondata.contenttype){
				$.each(jsondata.contenttype, function(index, item) {
					$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
				});
			}
			
			// デフォルトのコンテンツリストを取得
			updateContentList();
		}, function(request){		// 異常終了
			alert('通信に失敗しました。');
		});
	}
	// ページリストを取得
	function updatePageList()
	{
		var elementId = '#' + dialog.getContentElement('tab_basic', 'page_list').getInputElement().$.id;

		// Ajaxでページ情報を取得
		m3_ajax_request('', 'task=linkinfo&act=getpage&accesspoint=' + accessPoint, function(request, retcode, jsondata){		// 正常終了
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
	}
	// ダイアログ上の項目の表示制御
	function updateItems()
	{
		// リンク対象を取得
		var selValue = dialog.getValueOf('tab_basic', 'link_target');
		
		switch (selValue){
			case 'content':
				dialog.getContentElement('tab_basic', 'content_type').getElement().show();
				dialog.getContentElement('tab_basic', 'content_list').getElement().show();
				dialog.getContentElement('tab_basic', 'page_list').getElement().hide();
				dialog.getContentElement('tab_basic', 'content_label').getElement().show();
				$('#content_text').show();
				break;
			case 'page':
				dialog.getContentElement('tab_basic', 'content_type').getElement().hide();
				dialog.getContentElement('tab_basic', 'content_list').getElement().hide();
				dialog.getContentElement('tab_basic', 'page_list').getElement().show();
				dialog.getContentElement('tab_basic', 'content_label').getElement().hide();
				$('#content_text').hide();
				break;
			case 'others':
				dialog.getContentElement('tab_basic', 'content_type').getElement().hide();
				dialog.getContentElement('tab_basic', 'content_list').getElement().hide();
				dialog.getContentElement('tab_basic', 'page_list').getElement().hide();
				dialog.getContentElement('tab_basic', 'content_label').getElement().hide();
				$('#content_text').hide();
				break;
		}
	}
	// URLを更新。必要項目が選択されていない場合はクリア。
	function updateUrl()
	{
		var url = M3_ROOT_URL;
		if (accessPoint != '') url += '/' + accessPoint;
		url += '/index.php';
		
		// リンク対象を取得
		var linkTarget = dialog.getValueOf('tab_basic', 'link_target');
		switch (linkTarget){
			case 'content':
				var contentType = dialog.getContentElement('tab_basic', 'content_type').getValue();
				var contentId = dialog.getContentElement('tab_basic', 'content_list').getValue();

				if (contentId){
					switch (contentType){
						case 'content':
						case 'product':
						case 'event':
						case 'photo':
							url += '?' + contentType + 'id=' + contentId;
							break;
						case 'blog':
							url += '?entryid=' + contentId;
							break;
						case 'wiki':
							url += '?' + encodeURIComponent(contentId);
							break;
					}
				} else {
					url = '';
				}
				break;
			case 'page':
				var pageSubId = dialog.getContentElement('tab_basic', 'page_list').getValue();
				switch (pageSubId){
					case '':
						url = '';
						break;
					case '_root':
						url = M3_ROOT_URL;
						if (accessPoint != '') url += '/' + accessPoint;
						url += '/';
						break;
					default:
						url += '?sub=' + pageSubId;
						break;
				}
				break;
			case 'others':
				url = '';
				break;
		}
		dialog.getContentElement('tab_basic', 'url').setValue(url);
	}
	return {
		title: editor.lang.linkinfo.title,
		minWidth: 500,
		minHeight: 300,

		onLoad: function(){
			// 設定変更時の確認ダイアログを非表示にする
			this.on('cancel', function(cancelEvent){ return false; }, this, null, -1);
			
			// このダイアログへの参照を取得
			dialog = this;
			
			// ダイアログ項目の表示制御
			updateItems();
			
			// 起動時の初期値を設定
			accessPoint = _m3AccessPoint;		// アクセスポイント
			dialog.getContentElement('tab_basic', 'url').setValue(_m3Url);
		},
		onShow : function(){	// 再表示時イベント			
			// ダイアログ項目の表示制御
			updateItems();
			
			// 起動時の初期値を設定
			$('#content_text').text('');// コンテンツプレビュークリア
			accessPoint = _m3AccessPoint;		// アクセスポイント
			dialog.getContentElement('tab_basic', 'url').setValue(_m3Url);
			
			// フレーム内にある場合は表示位置を調整
			if (window.parent != window.self){
				this.move(this.getPosition().x, 0);
			}
		},
		contents: [
			{
				id: 'tab_basic',
				label: editor.lang.linkinfo.tab_info_title,
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
							updateItems();
							
							// URLを更新
							updateUrl();
						}
					},
					{	// コンテンツ種別選択
						type : 'select',
						id : 'content_type',
						label : editor.lang.linkinfo.content_type_title,
						items : [
							[ '接続中', '' ]
						],
						onShow : function(){	// 選択値変更時イベント
							// コンテンツタイプ更新
							updateContentType();
						},
						onChange : function(){	// 選択値変更時イベント
							// コンテンツリストを更新
							updateContentList();
						}
					},
					{	// コンテンツリスト
						type : 'select',
						id : 'content_list',
						label : editor.lang.linkinfo.content_list_title,
						items : [
							[ '接続中', '' ]
						],
						onChange : function(){	// 選択値変更時イベント
							// コンテンツプレビュークリア
							$('#content_text').text('');
							
							// コンテンツ内容を取得
							var contentType = dialog.getContentElement('tab_basic', 'content_type').getValue();
							var contentId = dialog.getContentElement('tab_basic', 'content_list').getValue();

							// Ajaxでコンテンツ内容を取得
							m3_ajax_request('', 'task=linkinfo&act=getcontent&contenttype=' + contentType + '&contentid=' + contentId + '&accesspoint=' + accessPoint, function(request, retcode, jsondata){		// 正常終了

								if (jsondata.content){
									$('#content_text').text(jsondata.content);
									$('#content2_image').hide();
									$('#content2_text').hide();
								}
								
								// URLを更新
								updateUrl();
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});
						}
					},
					{
						type : 'select',
						id : 'page_list',
						label : editor.lang.linkinfo.page_list_title,
						items : [
							[ '接続中', '' ]
						],
						//onLoad : function(){		// 起動時イベント
						onShow : function(){		// 再表示イベント
							// ページリスト更新
							updatePageList();
						},
						onChange : function(){	// 選択値変更時イベント
							// URLを更新
							updateUrl();
						}
					},
					{
						type : 'html',
						id: 'content_label',
						html : '<label>コンテンツ内容：</label>'
					},
					{
						type: 'html',
						html: '<p id="content_text" style="white-space: -moz-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"></p>'
					},
					{
						type: 'hbox',
						widths: [ '20%', '80%' ],
						children: [
						{
							type: 'html',
							html: '<p id="content2_image"></p>'
						},{
							type: 'html',
							html: '<p id="content2_text" style="white-space: -moz-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"></p>'
						} ]
					},
					{
						type: 'text',
						id: 'url',
						label: editor.lang.linkinfo.url_title,
						width: '100%'
					}
				]
			},
			{
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
									$.each(jsondata.accesspoint, function(index, item){
										$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
									});
								}
								// 項目を再選択
								$(elementId).val(accessPoint);
							}, function(request){		// 異常終了
								alert('通信に失敗しました。');
							});
						},
						onShow : function(){		// 再表示イベント
							var elementId = '#' + this.getInputElement().$.id;
							
							// 項目を再選択
							$(elementId).val(accessPoint);
						},
						onChange : function(){
							// アクセスポイント変更
							accessPoint = dialog.getContentElement('tab_advanced', 'access_point').getValue();
		
							// コンテンツタイプ更新
							updateContentType();
							
							// ページリスト更新
							updatePageList();
						}
					}
				]
			}
		],
		onOk: function(){
			var url = dialog.getContentElement('tab_basic', 'url').getValue();
			
			// 作成したURLを設定
			if (typeof _m3SetUrlCallback == 'function'){
				_m3SetUrlCallback(url);
			}
		}
	};
});
