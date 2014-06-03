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

( function() {
	CKEDITOR.dialog.add( 'm3link', function( editor ) {
		var plugin = CKEDITOR.plugins.m3link;
		var accessPoint = '';		// アクセスポイント
		var dialog;					// このダイアログへの参照

		// Handles the event when the "Type" selection box is changed.
		var linkTypeChanged = function() {
				var dialog = this.getDialog(),
					partIds = [ 'urlOptions' ],
					typeValue = this.getValue();

				if ( typeValue == 'url' ) {
					if ( editor.config.linkShowTargetTab )
						dialog.showPage( 'target' );
				} else {
					dialog.hidePage( 'target' );
				}

				for ( var i = 0; i < partIds.length; i++ ) {
					var element = dialog.getContentElement( 'info', partIds[ i ] );
					if ( !element )
						continue;

					element = element.getElement().getParent().getParent();
					if ( partIds[ i ] == typeValue + 'Options' )
						element.show();
					else
						element.hide();
				}

				dialog.layout();
			};

		var setupParams = function( page, data ) {
				if ( data[ page ] )
					this.setValue( data[ page ][ this.id ] || '' );
			};

		var setupAdvParams = function( data ) {
				return setupParams.call( this, 'advanced', data );
			};

		var commitParams = function( page, data ) {
				if ( !data[ page ] )
					data[ page ] = {};

				data[ page ][ this.id ] = this.getValue() || '';
			};

		var commitAdvParams = function( data ) {
				return commitParams.call( this, 'advanced', data );
			};

		var commonLang = editor.lang.common,
		linkLang = editor.lang.m3link;

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
			title: linkLang.title,
			minWidth: 500,
			minHeight: 300,
			contents: [
				{
				id: 'info',
				label: linkLang.info,
				title: linkLang.info,
				elements: [
					{
					id: 'linkType',
					type: 'select',
					label: linkLang.type,
					'default': 'url',
					items: [
						[ linkLang.toUrl, 'url' ]
						],
					onChange: linkTypeChanged,
					setup: function( data ) {
						this.setValue( data.type || 'url' );
					},
					commit: function( data ) {
						data.type = this.getValue();
					}
				},
					{
					type: 'vbox',
					id: 'urlOptions',
					children: [
						{
						type: 'hbox',
						widths: [ '25%', '75%' ],
						children: [
							{
							type: 'text',
							id: 'url',
							label: commonLang.url,
							required: true,
							onLoad: function() {
								this.allowOnChange = true;
							},
							onKeyUp: function() {
								//this.allowOnChange = false;
												var url = this.getValue();
								//this.allowOnChange = true;
							},
							onChange: function() {
								if ( this.allowOnChange ) // Dont't call on dialog load.
								this.onKeyUp();
							},
							validate: function() {
								var dialog = this.getDialog();

								if ( dialog.getContentElement( 'info', 'linkType' ) && dialog.getValueOf( 'info', 'linkType' ) != 'url' )
									return true;

								if ( !editor.config.linkJavaScriptLinksAllowed && ( /javascript\:/ ).test( this.getValue() ) ) {
									alert( commonLang.invalidValue );
									return false;
								}

								if ( this.getDialog().fakeObj ) // Edit Anchor.
								return true;

								var func = CKEDITOR.dialog.validate.notEmpty( linkLang.noUrl );
								return func.apply( this );
							},
							setup: function( data ) {
								this.allowOnChange = false;
								if ( data.url )
									this.setValue( data.url.url );
								this.allowOnChange = true;

							},
							commit: function( data ) {
								// IE will not trigger the onChange event if the mouse has been used
								// to carry all the operations #4724
								this.onChange();

								if ( !data.url )
									data.url = {};

								data.url.url = this.getValue();
								this.allowOnChange = false;
							}
						}
						],
						setup: function( data ) {
							if ( !this.getDialog().getContentElement( 'info', 'linkType' ) )
								this.getElement().show();
						}
					},
						{
						type: 'button',
						id: 'browse',
						hidden: 'true',
						filebrowser: 'info:url',
						label: commonLang.browseServer
					}
					]
				}
				]
			},
				{
				id: 'target',
				requiredContent: 'a[target]', // This is not fully correct, because some target option requires JS.
				label: linkLang.target,
				title: linkLang.target,
				elements: [
					{
					type: 'hbox',
					widths: [ '50%', '50%' ],
					children: [
						{
						type: 'select',
						id: 'linkTargetType',
						label: commonLang.target,
						'default': 'notSet',
						style: 'width : 100%;',
						'items': [
							[ commonLang.notSet, 'notSet' ],
							[ commonLang.targetNew, '_blank' ]
							],
//						onChange: targetChanged,
/*						setup: function( data ) {
							if ( data.target )
								this.setValue( data.target.type || 'notSet' );
							targetChanged.call( this );
						},*/
						commit: function( data ) {
							if ( !data.target )
								data.target = {};

							data.target.type = this.getValue();
						}
					}
					]
				}
				]
			},
				{
				id: 'advanced',
				label: linkLang.advanced,
				title: linkLang.advanced,
				elements: [
					{
					type: 'vbox',
					padding: 1,
					children: [
							{
								type : 'select',
								id : 'access_point',
								label : linkLang.access_point_title,
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
					},
					{
					type: 'vbox',
					padding: 1,
					children: [
						{
						type: 'hbox',
						widths: [ '45%', '35%', '20%' ],
						children: [
							{
							type: 'text',
							id: 'advId',
							requiredContent: 'a[id]',
							label: linkLang.id,
							setup: setupAdvParams,
							commit: commitAdvParams
						},
							{
							type: 'select',
							id: 'advLangDir',
							requiredContent: 'a[dir]',
							label: linkLang.langDir,
							'default': '',
							style: 'width:110px',
							items: [
								[ commonLang.notSet, '' ],
								[ linkLang.langDirLTR, 'ltr' ],
								[ linkLang.langDirRTL, 'rtl' ]
								],
							setup: setupAdvParams,
							commit: commitAdvParams
						},
							{
							type: 'text',
							id: 'advAccessKey',
							requiredContent: 'a[accesskey]',
							width: '80px',
							label: linkLang.acccessKey,
							maxLength: 1,
							setup: setupAdvParams,
							commit: commitAdvParams

						}
						]
					},
						{
						type: 'hbox',
						widths: [ '45%', '35%', '20%' ],
						children: [
							{
							type: 'text',
							label: linkLang.name,
							id: 'advName',
							requiredContent: 'a[name]',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
							{
							type: 'text',
							label: linkLang.langCode,
							id: 'advLangCode',
							requiredContent: 'a[lang]',
							width: '110px',
							'default': '',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
							{
							type: 'text',
							label: linkLang.tabIndex,
							id: 'advTabIndex',
							requiredContent: 'a[tabindex]',
							width: '80px',
							maxLength: 5,
							setup: setupAdvParams,
							commit: commitAdvParams

						}
						]
					}
					]
				},
					{
					type: 'vbox',
					padding: 1,
					children: [
						{
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [
							{
							type: 'text',
							label: linkLang.advisoryTitle,
							requiredContent: 'a[title]',
							'default': '',
							id: 'advTitle',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
							{
							type: 'text',
							label: linkLang.advisoryContentType,
							requiredContent: 'a[type]',
							'default': '',
							id: 'advContentType',
							setup: setupAdvParams,
							commit: commitAdvParams

						}
						]
					},
						{
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [
							{
							type: 'text',
							label: linkLang.cssClasses,
							requiredContent: 'a(cke-xyz)', // Random text like 'xyz' will check if all are allowed.
							'default': '',
							id: 'advCSSClasses',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
							{
							type: 'text',
							label: linkLang.charset,
							requiredContent: 'a[charset]',
							'default': '',
							id: 'advCharset',
							setup: setupAdvParams,
							commit: commitAdvParams

						}
						]
					},
						{
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [
							{
							type: 'text',
							label: linkLang.rel,
							requiredContent: 'a[rel]',
							'default': '',
							id: 'advRel',
							setup: setupAdvParams,
							commit: commitAdvParams
						},
							{
							type: 'text',
							label: linkLang.styles,
							requiredContent: 'a{cke-xyz}', // Random text like 'xyz' will check if all are allowed.
							'default': '',
							id: 'advStyles',
							validate: CKEDITOR.dialog.validate.inlineStyle( editor.lang.common.invalidInlineStyle ),
							setup: setupAdvParams,
							commit: commitAdvParams
						}
						]
					}
					]
				}
				]
			}
			],
			onShow: function() {
				var editor = this.getParentEditor(),
					selection = editor.getSelection(),
					element = null;

				// Fill in all the relevant fields if there's already one link selected.
				if ( ( element = plugin.getSelectedLink( editor ) ) && element.hasAttribute( 'href' ) ) {
					// Don't change selection if some element is already selected.
					// For example - don't destroy fake selection.
					if ( !selection.getSelectedElement() )
						selection.selectElement( element );
				} else
					element = null;

				var data = plugin.parseLinkAttributes( editor, element );

				// Record down the selected element in the dialog.
				this._.selectedElement = element;

				this.setupContent( data );
				
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
			onOk: function() {
				var data = {};

				// Collect data from fields.
				this.commitContent( data );

				var selection = editor.getSelection(),
					attributes = plugin.getLinkAttributes( editor, data );

				if ( !this._.selectedElement ) {
					var range = selection.getRanges()[ 0 ];

					// Use link URL as text with a collapsed cursor.
					if ( range.collapsed ) {
						// Short mailto link text view (#5736).
						var text = new CKEDITOR.dom.text( attributes.set[ 'data-cke-saved-href' ], editor.document );
						range.insertNode( text );
						range.selectNodeContents( text );
					}

					// Apply style.
					var style = new CKEDITOR.style( {
						element: 'a',
						attributes: attributes.set
					} );

					style.type = CKEDITOR.STYLE_INLINE; // need to override... dunno why.
					style.applyToRange( range, editor );
					range.select();
				} else {
					// We're only editing an existing link, so just overwrite the attributes.
					var element = this._.selectedElement,
						href = element.data( 'cke-saved-href' ),
						textView = element.getHtml();

					element.setAttributes( attributes.set );
					element.removeAttributes( attributes.removed );

					// Update text view when user changes protocol (#4612).
					if ( href == textView ) {
						// Short mailto link text view (#5736).
						element.setHtml( attributes.set[ 'data-cke-saved-href' ] );

						// We changed the content, so need to select it again.
						selection.selectElement( element );
					}

					delete this._.selectedElement;
				}
			},
			onLoad: function() {
				if ( !editor.config.linkShowAdvancedTab )
					this.hidePage( 'advanced' ); //Hide Advanded tab.

				if ( !editor.config.linkShowTargetTab )
					this.hidePage( 'target' ); //Hide Target tab.
				
				// 設定変更時の確認ダイアログを非表示にする
				this.on('cancel', function(cancelEvent){ return false; }, this, null, -1);
			
				// このダイアログへの参照を取得
				dialog = this;
			
/*				// ダイアログ項目の表示制御
				updateItems();
			
				// 起動時の初期値を設定
				accessPoint = _m3AccessPoint;		// アクセスポイント
				dialog.getContentElement('tab_basic', 'url').setValue(_m3Url);
				*/
			},
			// Inital focus on 'url' field if link is of type URL.
			onFocus: function() {
				var linkType = this.getContentElement( 'info', 'linkType' ),
					urlField;

				if ( linkType && linkType.getValue() == 'url' ) {
					urlField = this.getContentElement( 'info', 'url' );
					urlField.select();
				}
			}
		};
	} );
} )();
