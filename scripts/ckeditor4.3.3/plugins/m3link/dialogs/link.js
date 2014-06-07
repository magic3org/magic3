/**
 * Magic3 CKEditorプラグイン
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
'use strict';

(function () {
	CKEDITOR.dialog.add('m3link', function (editor) {
		var plugin = CKEDITOR.plugins.m3link;
		var commonLang = editor.lang.common;
		var linkLang = editor.lang.m3link;
		var accessPoint = ''; // アクセスポイント
		var dialog; // このダイアログへの参照
		var cancelOnChange;		// changeイベントをキャンセルするかどうか

		var setupParams = function (page, data) {
			if (data[page])
				this.setValue(data[page][this.id] || '');
		};

		var setupAdvParams = function (data) {
			return setupParams.call(this, 'tab_advanced', data);
		};

		var commitParams = function (page, data) {
			if (!data[page])
				data[page] = {};

			data[page][this.id] = this.getValue() || '';
		};

		var commitAdvParams = function (data) {
			return commitParams.call(this, 'tab_advanced', data);
		};

		// コンテンツリスト、コンテンツ内容表示を更新
		function updateContentList(contentId) {
			// コンテンツリストを取得
			var elementId = '#' + dialog.getContentElement('tab_info', 'content_list').getInputElement().$.id;
			var contentType = dialog.getContentElement('tab_info', 'content_type').getValue();
			var pageNo = 1;

			// Ajaxでページ情報を取得
			m3_ajax_request('', 'task=linkinfo&act=getcontentlist&contenttype=' + contentType + '&accesspoint=' + accessPoint + '&page=' + pageNo, function (request, retcode, jsondata) { // 正常終了
				// コンテンツ種別選択メニューを更新
				$('option', elementId).remove();
				if (jsondata.contentlist) {
					$.each(jsondata.contentlist, function (index, item) {
						$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
					});
				}
				// 選択値を設定
				if (contentId){
					elementId = '#' + dialog.getContentElement('tab_info', 'content_list').getInputElement().$.id;
					$(elementId).val(contentId);
					
					// コンテンツ内容を更新
					updateContent(false);
				}
			}, function (request) { // 異常終了
				alert('通信に失敗しました。');
			});
		}
		// コンテンツタイプを取得
		function updateContentType(contentType, contentId) {
			var elementId = '#' + dialog.getContentElement('tab_info', 'content_type').getInputElement().$.id;

			// Ajaxでコンテンツタイプを取得
			m3_ajax_request('', 'task=linkinfo&act=getcontenttype&accesspoint=' + accessPoint, function (request, retcode, jsondata) { // 正常終了
				// コンテンツ種別選択メニューを更新
				$('option', elementId).remove();
				if (jsondata.contenttype) {
					$.each(jsondata.contenttype, function (index, item) {
						$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
					});
				}
				// 選択値を設定
				if (contentType){
					elementId = '#' + dialog.getContentElement('tab_info', 'content_type').getInputElement().$.id;
					$(elementId).val(contentType);
				}
				// デフォルトのコンテンツリストを取得
				updateContentList(contentId);
			}, function (request) { // 異常終了
				alert('通信に失敗しました。');
			});
		}
		// ページリストを取得
		function updatePageList(pageId) {
			var elementId = '#' + dialog.getContentElement('tab_info', 'page_list').getInputElement().$.id;

			// Ajaxでページ情報を取得
			m3_ajax_request('', 'task=linkinfo&act=getpage&accesspoint=' + accessPoint, function (request, retcode, jsondata) { // 正常終了
				// ページ選択メニューを更新
				$('option', elementId).remove();
				if (jsondata.pagelist) {
					$.each(jsondata.pagelist, function (index, item) {
						$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
					});
				}
				
				if (pageId){
					elementId = '#' + dialog.getContentElement('tab_info', 'page_list').getInputElement().$.id;
					$(elementId).val(pageId);
				}
			}, function (request) { // 異常終了
				alert('通信に失敗しました。');
			});
		}
		// コンテンツ内容を取得
		function updateContent(updateUrlField) {
			var contentType = dialog.getContentElement('tab_info', 'content_type').getValue();
			var contentId = dialog.getContentElement('tab_info', 'content_list').getValue();

			// Ajaxでコンテンツ内容を取得
			m3_ajax_request('', 'task=linkinfo&act=getcontent&contenttype=' + contentType + '&contentid=' + contentId + '&accesspoint=' + accessPoint, function (request, retcode, jsondata) { // 正常終了

				if (jsondata.content) {
					$('#content_text').text(jsondata.content);
					$('#content2_image').hide();
					$('#content2_text').hide();
				}

				// URLを更新
				if (updateUrlField) updateUrl();
			}, function (request) { // 異常終了
				alert('通信に失敗しました。');
			});
		}
		// コンテンツプレビュークリア
		function clearPreview() {
			dialog.getContentElement('tab_info', 'url').setValue('');
			$('#content_text').text('');
		}
		// ダイアログ上の項目の表示制御
		function updateItems() {
			// リンク対象を取得
			var selValue = dialog.getValueOf('tab_info', 'link_target');

			switch (selValue) {
			case 'content':
				dialog.getContentElement('tab_info', 'content_type').getElement().show();
				dialog.getContentElement('tab_info', 'content_list').getElement().show();
				dialog.getContentElement('tab_info', 'page_list').getElement().hide();
				dialog.getContentElement('tab_info', 'content_label').getElement().show();
				$('#content_text').show();
				break;
			case 'page':
				dialog.getContentElement('tab_info', 'content_type').getElement().hide();
				dialog.getContentElement('tab_info', 'content_list').getElement().hide();
				dialog.getContentElement('tab_info', 'page_list').getElement().show();
				dialog.getContentElement('tab_info', 'content_label').getElement().hide();
				$('#content_text').hide();
				break;
			case 'others':
				dialog.getContentElement('tab_info', 'content_type').getElement().hide();
				dialog.getContentElement('tab_info', 'content_list').getElement().hide();
				dialog.getContentElement('tab_info', 'page_list').getElement().hide();
				dialog.getContentElement('tab_info', 'content_label').getElement().hide();
				$('#content_text').hide();
				break;
			}
		}
		// URLを更新。必要項目が選択されていない場合はクリア。
		function updateUrl() {
			var url = M3_ROOT_URL;
			if (accessPoint != '') url += '/' + accessPoint;
			url += '/index.php';

			// リンク対象を取得
			var linkTarget = dialog.getValueOf('tab_info', 'link_target');
			switch (linkTarget) {
			case 'content':
				var contentType = dialog.getContentElement('tab_info', 'content_type').getValue();
				var contentId = dialog.getContentElement('tab_info', 'content_list').getValue();

				if (contentId) {
					switch (contentType) {
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
				var pageSubId = dialog.getContentElement('tab_info', 'page_list').getValue();
				switch (pageSubId) {
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
			dialog.getContentElement('tab_info', 'url').setValue(url);
		}
		// URLを解析
		function parseUrl(url) {
			var urlMatch, queryMatch;
			var linkTarget;
			var contentType, contentId;
			var pageSubId;
			var urlRegex;
			var queryRegex = /([^&=#]+)=?([^&#]*)/;
			var elementId;
			
			// 初期化
			accessPoint = _m3AccessPoint; // アクセスポイント
			linkTarget = 'content';			// リンク対象
			contentType = 'content';		// 汎用コンテンツ
			
			if (url){
				linkTarget = 'others';		// リンク対象「その他」
				
				urlRegex = new RegExp("^" + M3_ROOT_URL.replace(/\W/g,'\\$&') + "(.*)\\/index.php\\?(.*)$");
				urlMatch = url.match( urlRegex );
				if (urlMatch) {
					// アクセスポイントを取得
					switch (urlMatch[1]){
					case '/m':
						accessPoint = 'm';
						break;
					case '/s':
						accessPoint = 's';
						break;
					default:
						accessPoint = '';
						break;
					}

					// 1番目のパラメータを取得
					var query = urlMatch[2];
					queryMatch = query.match(queryRegex);
					if (queryMatch) {
						if (queryMatch[2]){
							switch (queryMatch[1]){
							case 'contentid':
								linkTarget = 'content';
								contentType = 'content';
								break;
							case 'productid':
								linkTarget = 'content';
								contentType = 'product';
								break;
							case 'eventid':
								linkTarget = 'content';
								contentType = 'event';
								break;
							case 'photoid':
								linkTarget = 'content';
								contentType = 'photo';
								break;
							case 'entryid':
								linkTarget = 'content';
								contentType = 'blog';
								break;
							case 'sub':
								linkTarget = 'page';
								pageSubId = queryMatch[2];
								// ページ選択(changeイベント発生させない)
								//dialog.getContentElement('tab_info', 'page_list').setValue(queryMatch[2]);
								//elementId = '#' + dialog.getContentElement('tab_info', 'page_list').getInputElement().$.id;
								//$(elementId).val(queryMatch[2]);
								break;
							default:
								break;
							}
							contentId = queryMatch[2];
						} else {		// キーのみの場合
							linkTarget = 'content';
							contentType = 'wiki';
							contentId = queryMatch[1];
						}
					}
				//	elementId = '#' + dialog.getContentElement('tab_info', 'content_type').getInputElement().$.id;
				//	$(elementId).val(linkTarget);
				}
	//			alert(dialog.getContentElement('tab_advanced', 'access_point').getInputElement().$.id);
	//			alert(accessPoint);
	//			dialog.getContentElement('tab_advanced', 'access_point').getInputElement().$.setValue(accessPoint);

			} else {		// URLが空のときはデフォルトの値を表示
				// アクセスポイントの変更をセレクトメニューに反映
				//dialog.getContentElement('tab_advanced', 'access_point').setValue(accessPoint);
			}
			// アクセスポイントの変更をセレクトメニューに反映
			cancelOnChange = true;		// changeイベントをキャンセル
			dialog.getContentElement('tab_advanced', 'access_point').setValue(accessPoint);
//				elementId = '#' + dialog.getContentElement('tab_advanced', 'access_point').getInputElement().$.id;
//				$(elementId).val(accessPoint);
			
			// リンク対象を設定
			dialog.getContentElement('tab_info', 'link_target').setValue(linkTarget);
//			alert(linkTarget);
//				elementId = '#' + dialog.getContentElement('tab_info', 'link_target').getInputElement().$.id;
//				$(elementId).val(linkTarget);
			
/*			if (linkTarget == 'page'){
				// ページ選択
				dialog.getContentElement('tab_info', 'page_list').setValue(pageSubId);
			}*/
			cancelOnChange = false;		// changeイベントをキャンセル
			
			// ページリスト更新(アクセスポイントに連動)
			updatePageList(pageSubId);
			
			// コンテンツタイプの場合はコンテンツ内容を表示
			if (linkTarget == 'content'){
				// コンテンツプレビュークリア
				$('#content_text').text('');
				
				// コンテンツタイプ作成。コンテンツリストを取得。
				updateContentType(contentType, contentId);
			}
		}
		
		return {
			title: linkLang.title,
			minWidth: 500,
			minHeight: 300,

			contents: [{
				id: 'tab_info',
				label: linkLang.info,
				title: linkLang.info,
				elements: [{ // リンク対象選択
					type: 'radio',
					id: 'link_target',
					label: linkLang.link_target_title,
					items: [
						['コンテンツ', 'content'],
						['ページ', 'page'],
						['その他', 'others']
					],
					'default': 'content',
					onClick: function () {
						if (cancelOnChange) return;
								
						// ダイアログ項目の表示制御
						updateItems();

						// URLを更新
						updateUrl();
					}
				}, { // コンテンツ種別選択
					type: 'select',
					id: 'content_type',
					label: linkLang.content_type_title,
					items: [
						[linkLang.on_connecting, '']
					],
/*					onShow: function () { // 選択値変更時イベント
						// コンテンツタイプ更新
						updateContentType();
					},*/
					onChange: function () { // 選択値変更時イベント
						// コンテンツリストを更新
						updateContentList();
								
						// コンテンツプレビュークリア
						clearPreview();
					}
				}, { // コンテンツリスト
					type: 'select',
					id: 'content_list',
					label: linkLang.content_list_title,
					items: [
						[linkLang.on_connecting, '']
					],
					onChange: function () { // 選択値変更時イベント
						// コンテンツプレビュークリア
						$('#content_text').text('');

						// コンテンツ内容を取得
						updateContent(true);
					}
				}, {
					type: 'select',
					id: 'page_list',
					label: linkLang.page_list_title,
					items: [
						[linkLang.on_connecting, '']
					],
/*					onShow: function () { // 再表示イベント
						// ページリスト更新
						updatePageList();
					},*/
					onChange: function () { // 選択値変更時イベント
						if (cancelOnChange) return;
								
						// URLを更新
						updateUrl();
					}
				}, {
					type: 'html',
					id: 'content_label',
					html: '<label>コンテンツ内容：</label>'
				}, {
					type: 'html',
					html: '<p id="content_text" style="white-space: -moz-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"></p>'
				}, {
					type: 'hbox',
					widths: ['20%', '80%'],
					children: [{
						type: 'html',
						html: '<p id="content2_image"></p>'
					}, {
						type: 'html',
						html: '<p id="content2_text" style="white-space: -moz-pre-wrap; white-space: pre-wrap; word-wrap: break-word;"></p>'
					}]
				}, {
					type: 'text',
					id: 'url',
					label: linkLang.url_title,
					width: '100%',
					setup: function (data) {
						if (data.url) this.setValue(data.url.url);
					},
					commit: function (data) {
						if (!data.url) data.url = {};

						data.url.url = this.getValue();
					}
				}]
			}, {
				id: 'tab_target',
				requiredContent: 'a[target]', // This is not fully correct, because some target option requires JS.
				label: linkLang.target,
				title: linkLang.target,
				elements: [{
					type: 'hbox',
					widths: ['50%', '50%'],
					children: [{
						type: 'select',
						id: 'linkTargetType',
						label: commonLang.target,
						'default': 'notSet',
						style: 'width : 100%;',
						'items': [
							[commonLang.notSet, 'notSet'],
							[commonLang.targetNew, '_blank']
						],
						setup: function( data ) {
							if ( data.target )
								this.setValue( data.target.type || 'notSet' );
						},
						commit: function (data) {
							if (!data.target)
								data.target = {};

							data.target.type = this.getValue();
						}
					}]
				}]
			}, {
				id: 'tab_advanced',
				label: linkLang.advanced,
				title: linkLang.advanced,
				elements: [{
					type: 'vbox',
					padding: 1,
					children: [{
						type: 'select',
						id: 'access_point',
						label: linkLang.access_point_title,
						items: [
							[linkLang.on_connecting, '']
						],
						onLoad: function () { // 起動時イベント
							var elementId = '#' + this.getInputElement().$.id;

							// Ajaxでページ情報を取得
							m3_ajax_request('', 'task=linkinfo&act=getaccesspoint', function (request, retcode, jsondata) { // 正常終了
								// アクセスポイント選択メニューを更新
								$('option', elementId).remove();
								if (jsondata.accesspoint) {
									$.each(jsondata.accesspoint, function (index, item) {
										$(elementId).get(0).options[$(elementId).get(0).options.length] = new Option(item[1], item[0]);
									});
								}
								// 項目を再選択
								//$(elementId).val(accessPoint);
						//dialog.getContentElement('tab_advanced', 'access_point').setValue(accessPoint);
							}, function (request) { // 異常終了
								alert('通信に失敗しました。');
							});
						},
/*						onShow: function () { // 再表示イベント
							var elementId = '#' + this.getInputElement().$.id;

							// 項目を再選択
							$(elementId).val(accessPoint);
						},*/
						onChange: function () {
							if (cancelOnChange) return;
										
							// アクセスポイント変更
							accessPoint = dialog.getContentElement('tab_advanced', 'access_point').getValue();

							// コンテンツタイプ更新
							updateContentType();
										
							// ページリスト更新
							updatePageList();
										
							// コンテンツプレビュークリア
							clearPreview();
						}
					}]
				}, {
					type: 'vbox',
					padding: 1,
					children: [{
						type: 'hbox',
						widths: ['45%', '35%', '20%'],
						children: [{
							type: 'text',
							id: 'advId',
							requiredContent: 'a[id]',
							label: linkLang.id,
							setup: setupAdvParams,
							commit: commitAdvParams
						}, {
							type: 'select',
							id: 'advLangDir',
							requiredContent: 'a[dir]',
							label: linkLang.langDir,
							'default': '',
							style: 'width:110px',
							items: [
								[commonLang.notSet, ''],
								[linkLang.langDirLTR, 'ltr'],
								[linkLang.langDirRTL, 'rtl']
							],
							setup: setupAdvParams,
							commit: commitAdvParams
						}, {
							type: 'text',
							id: 'advAccessKey',
							requiredContent: 'a[accesskey]',
							width: '80px',
							label: linkLang.acccessKey,
							maxLength: 1,
							setup: setupAdvParams,
							commit: commitAdvParams

						}]
					}, {
						type: 'hbox',
						widths: ['45%', '35%', '20%'],
						children: [{
							type: 'text',
							label: linkLang.name,
							id: 'advName',
							requiredContent: 'a[name]',
							setup: setupAdvParams,
							commit: commitAdvParams

						}, {
							type: 'text',
							label: linkLang.langCode,
							id: 'advLangCode',
							requiredContent: 'a[lang]',
							width: '110px',
							'default': '',
							setup: setupAdvParams,
							commit: commitAdvParams

						}, {
							type: 'text',
							label: linkLang.tabIndex,
							id: 'advTabIndex',
							requiredContent: 'a[tabindex]',
							width: '80px',
							maxLength: 5,
							setup: setupAdvParams,
							commit: commitAdvParams

						}]
					}]
				}, {
					type: 'vbox',
					padding: 1,
					children: [{
						type: 'hbox',
						widths: ['45%', '55%'],
						children: [{
							type: 'text',
							label: linkLang.advisoryTitle,
							requiredContent: 'a[title]',
							'default': '',
							id: 'advTitle',
							setup: setupAdvParams,
							commit: commitAdvParams

						}, {
							type: 'text',
							label: linkLang.advisoryContentType,
							requiredContent: 'a[type]',
							'default': '',
							id: 'advContentType',
							setup: setupAdvParams,
							commit: commitAdvParams

						}]
					}, {
						type: 'hbox',
						widths: ['45%', '55%'],
						children: [{
							type: 'text',
							label: linkLang.cssClasses,
							requiredContent: 'a(cke-xyz)', // Random text like 'xyz' will check if all are allowed.
							'default': '',
							id: 'advCSSClasses',
							setup: setupAdvParams,
							commit: commitAdvParams

						}, {
							type: 'text',
							label: linkLang.charset,
							requiredContent: 'a[charset]',
							'default': '',
							id: 'advCharset',
							setup: setupAdvParams,
							commit: commitAdvParams

						}]
					}, {
						type: 'hbox',
						widths: ['45%', '55%'],
						children: [{
							type: 'text',
							label: linkLang.rel,
							requiredContent: 'a[rel]',
							'default': '',
							id: 'advRel',
							setup: setupAdvParams,
							commit: commitAdvParams
						}, {
							type: 'text',
							label: linkLang.styles,
							requiredContent: 'a{cke-xyz}', // Random text like 'xyz' will check if all are allowed.
							'default': '',
							id: 'advStyles',
							validate: CKEDITOR.dialog.validate.inlineStyle(editor.lang.common.invalidInlineStyle),
							setup: setupAdvParams,
							commit: commitAdvParams
						}]
					}]
				}]
			}],
			onLoad: function () {
				if (!editor.config.linkShowAdvancedTab)
					this.hidePage('tab_advanced'); //Hide Advanded tab.

				if (!editor.config.linkShowTargetTab)
					this.hidePage('tab_target'); //Hide Target tab.

				// 設定変更時の確認ダイアログを非表示にする
				this.on('cancel', function (cancelEvent) {
					return false;
				}, this, null, -1);

				// このダイアログへの参照を取得
				dialog = this;

				// ダイアログ項目の表示制御
			//updateItems();

				// 起動時の初期値を設定
//				accessPoint = _m3AccessPoint; // アクセスポイント
//				accessPoint = 'm';
				//dialog.getContentElement('tab_info', 'url').setValue(_m3Url);
			},
			onShow: function () {
				var editor = this.getParentEditor(),
					selection = editor.getSelection(),
					element = null;

				// Fill in all the relevant fields if there's already one link selected.
				if ((element = plugin.getSelectedLink(editor)) && element.hasAttribute('href')) {
					// Don't change selection if some element is already selected.
					// For example - don't destroy fake selection.
					if (!selection.getSelectedElement())
						selection.selectElement(element);
				} else
					element = null;

				var data = plugin.parseLinkAttributes(editor, element);

				// Record down the selected element in the dialog.
				this._.selectedElement = element;
				
				this.setupContent(data);
				
				// URL解析
				parseUrl(data.url.url);
				
				// ダイアログ項目の表示制御
				updateItems();
//dialog.getContentElement('tab_advanced', 'access_point').setValue('m');
//dialog.layout();
//accessPoint = 'm';
//alert(dialog.getContentElement('tab_advanced', 'access_point').getValue());
				// 起動時の初期値を設定
//				$('#content_text').text(''); // コンテンツプレビュークリア
//				accessPoint = _m3AccessPoint; // アクセスポイント
				//dialog.getContentElement('tab_info', 'url').setValue(_m3Url);

				// フレーム内にある場合は表示位置を調整
				if (window.parent != window.self) {
					this.move(this.getPosition().x, 0);
				}
			},
			onOk: function () {
				var data = {};

				// Collect data from fields.
				this.commitContent(data);

				var selection = editor.getSelection(),
					attributes = plugin.getLinkAttributes(editor, data);

				if (!this._.selectedElement) {
					var range = selection.getRanges()[0];

					// Use link URL as text with a collapsed cursor.
					if (range.collapsed) {
						// Short mailto link text view (#5736).
						var text = new CKEDITOR.dom.text(attributes.set['data-cke-saved-href'], editor.document);
						range.insertNode(text);
						range.selectNodeContents(text);
					}

					// Apply style.
					var style = new CKEDITOR.style({
						element: 'a',
						attributes: attributes.set
					});

					style.type = CKEDITOR.STYLE_INLINE; // need to override... dunno why.
					style.applyToRange(range, editor);
					range.select();
				} else {
					// We're only editing an existing link, so just overwrite the attributes.
					var element = this._.selectedElement,
						href = element.data('cke-saved-href'),
						textView = element.getHtml();

					element.setAttributes(attributes.set);
					element.removeAttributes(attributes.removed);

					// Update text view when user changes protocol (#4612).
					if (href == textView) {
						// Short mailto link text view (#5736).
						element.setHtml(attributes.set['data-cke-saved-href']);

						// We changed the content, so need to select it again.
						selection.selectElement(element);
					}

					delete this._.selectedElement;
				}
			}
			// Inital focus on 'url' field if link is of type URL.
			/*			onFocus: function() {
				var linkType = this.getContentElement( 'tab_info', 'linkType' ),
					urlField;

				if ( linkType && linkType.getValue() == 'url' ) {
					urlField = this.getContentElement( 'tab_info', 'url' );
					urlField.select();
				}
			}*/
		};
	});
})();
