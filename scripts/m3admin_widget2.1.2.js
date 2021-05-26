/**
 * Magic3管理機能用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
(function($){
	window.m3 = {};
	var m3InsertIndex = -1;
	var m3UpdatePos;
	var DRAG_ITEM_WIDTH = '200px';		// ドラッグ中のウィジェットの幅
	var PANEL_WIDTH = '240';				// パネル幅
	var CURSOR_OFFSET = 10;				// カーソルの位置
	
	var m3UpdateByConfig = function(serial){
		var widgets = $('.m3_widget_sortable');
		for (var i = 0; i < widgets.length; i++){
			var widget = $(widgets[i]);
			var attrs = m3_splitAttr(widget.attr('m3'));
			
			if (attrs['serial'] == serial){
				m3TaskWidget('get', widget);
				break;
			}
		}
	};
	window.m3.m3UpdateByConfig = m3UpdateByConfig;
	
	var widgetClose = function(){
		var widget = $(this).parents('.m3_widget_sortable');
		widget.fadeOut('slow', function(){
			m3TaskWidget('delete', $(this));
			$(this).remove();
		});
	};
	var m3TaskWidget = function(task, obj, widgetType, index, pos){
		var parent = obj.parents('.m3_widgetpos_box');
		var attrs = m3_splitAttr(obj.attr('m3'));
		var pattrs = m3_splitAttr(parent.attr('m3'));
		var param;
		
		// 変更先のウィジェット
		var insertIndex = -1;
		var widgets = '';
		var children = parent.children('.m3_widget_sortable');
		for (var i = 0; i < children.length; i++){
			var child = $(children[i]);
			var wattr = m3_splitAttr(child.attr('m3'));
			
			if (task == 'move'){
				if (wattr['serial'] == attrs['serial']){
					insertIndex = i;
					if (!pos) widgets += wattr['serial'] + ',';
				} else {
					widgets += wattr['serial'] + ',';
				}
			} else {
				if (wattr['serial']) widgets += wattr['serial'] + ',';
			}
		}
		widgets = widgets.substr(0, widgets.length -1);
		
		// 更新するポジションID
		var updatepos = '';
		for (var i = 0; i < M3_POSITIONS.length; i++){
			var posObj = $(M3_POSITIONS[i]);
			var battrs = m3_splitAttr(posObj.attr('m3'));
			if (task == 'move'){
				if (battrs['pos'] == pattrs['pos'] || battrs['pos'] == pos) updatepos += posObj.attr('id') + ',';
			} else {
				if (battrs['pos'] == pattrs['pos']) updatepos += posObj.attr('id') + ',';
			}
		}
		updatepos = updatepos.substr(0, updatepos.length -1);

		// 送信パラメータ
		if (task == 'get'){
			param = '&task=wget' + '&serial=' + attrs['serial'] + '&shared=' + attrs['shared'] +
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'toggle'){
			param = '&task=wtoggle' + '&serial=' + attrs['serial'] + '&shared=' + attrs['shared'] +
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'delete'){
			param = '&task=wdelete' + '&serial=' + attrs['serial'] + '&shared=' + attrs['shared'] +
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'add'){
			var idHead = 'm3widgettype_';
			var widgetId = widgetType.substr(idHead.length);
			param = '&task=wadd' + '&widget=' + widgetId + '&index=' + index + 
					'&pos=' + pattrs['pos'] + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		} else if (task == 'move'){
			var position = pattrs['pos'];
			if (pos) position += ',' + pos;
			param = '&task=wmove' + '&serial=' + attrs['serial'] + '&index=' + insertIndex + 
					'&pos=' + position + '&widgets=' + widgets + '&updatepos=' + updatepos + '&rev=' + M3_REVISION;
		}
		
		$.ajax({	url: createUrl() + param,
					type:		'get',
					success:	function(data, textStatus){
									var children = jQuery(data).children('.m3_widgetpos_box');
									for (var i = 0; i < children.length; i++){
										var child = $(children[i]);
										if (i == 0){
											// リビジョン番号更新
											var attrs = m3_splitAttr(child.attr('m3'));
											M3_REVISION = attrs['rev'];
										}
										$('#' + child.attr('id')).replaceWith(child);
									}
									setupSortable();
				
									// ヘルプを設定
									m3SetHelp();
				
									// 関連画面を更新
									if (window.parent.m3UpdateByChildWindow) window.parent.m3UpdateByChildWindow();
								},
					error:		function(request, textStatus, errorThrown){
									$(".m3message").text('通信エラー');
								}
		});
	};
	var setupSortable = function(){
		var els = M3_POSITIONS;
		var $els = $(els.toString());
		var urlParams = m3_getUrlParam(document.URL);
		var page = urlParams['_page'];
		var sub = urlParams['_sub'];
		
		$els.sortable({
			items: '> dl',
			handle: 'dt',
			cursor: 'move',
			cursorAt: { top: CURSOR_OFFSET, left: CURSOR_OFFSET },		// 左上に初期化
			opacity: 0.5,
			distance: 10,
			helper: 'clone',
			appendTo: 'html',
			placeholder: 'm3_spacer',
			connectWith: els,
			start: function(e,ui){
				// ドラッグ中のイメージの幅を固定
				ui.helper.css('z-index', '2010');
				ui.helper.css('width', DRAG_ITEM_WIDTH);
			},
			update: updateSortable
		});
		
		// ウィジェット操作メニューにイベントを再設定
		$('.m3_wadjust').unbind('click');
		$('.m3_wadjust').bind('click', function(e){
			var attrs = m3_splitAttr($(this).closest('dl.m3_widget_sortable').attr('m3'));
			m3ShowAdjustWindow(attrs['configid'], attrs['serial'], page, sub);
			e.preventDefault();
		});
		$('.m3_wconfig').unbind('click');
		$('.m3_wconfig').bind('click', function(e){
			var attrs = m3_splitAttr($(this).closest('dl.m3_widget_sortable').attr('m3'));
		    if (attrs['useconfig'] == '0'){
		        alert("このウィジェットには設定画面がありません");
				return;
		    }
			m3ShowConfigWindow(attrs['widgetid'], attrs['configid'], attrs['serial']);
			e.preventDefault();
		});
		$('.m3_wshared').unbind('click');
		$('.m3_wshared').bind('click', function(e){
			var widget = $(this).closest('dl.m3_widget_sortable');
			m3TaskWidget('toggle', widget);
			e.preventDefault();
		});
		$('.m3_wdelete').unbind('click');
		$('.m3_wdelete').bind('click', function(e){
			var widget = $(this).closest('dl.m3_widget_sortable');
			widget.fadeOut('slow', function(){
				m3TaskWidget('delete', $(this));
				$(this).remove();
			});
			e.preventDefault();
		});
		
		// コンテキストメニューを作成
		$('.m3_widget_sortable').contextMenu('m3_widgetmenu', {
			menuStyle: {
				// border : "2px solid green",
				backgroundColor: '#FFFFFF',
//				width: "200px",
				textAlign: 'left',
				font: '15px/1.5 "Lucida Grande","Hiragino Kaku Gothic ProN",Meiryo,sans-serif'
			},
			itemStyle: {
				padding: '3px 3px'
			},
			bindings: {
				'm3_wconfig': function(t){
					var attrs = m3_splitAttr($('#' + t.id).attr('m3'));
				    if (attrs['useconfig'] == '0'){
				        alert("このウィジェットには設定画面がありません");
						return;
				    }
					m3ShowConfigWindow(attrs['widgetid'], attrs['configid'], attrs['serial']);
				},
				'm3_wadjust': function(t){
					var attrs = m3_splitAttr($('#' + t.id).attr('m3'));
					m3ShowAdjustWindow(attrs['configid'], attrs['serial'], page, sub);
				},
				'm3_wshared': function(t){
					m3TaskWidget('toggle', $('#' + t.id));
				},
				'm3_wdelete': function(t){
					var widget = $('#' + t.id);
					widget.fadeOut('slow', function(){
						m3TaskWidget('delete', $(this));
						$(this).remove();
					});
				}
			},
			onContextMenu: function(e) {
				var attrs = m3_splitAttr($(e.target).parents('dl').attr('m3'));
				if (attrs['shared'] == '0'){	// 共通ウィジェットでない
					$('#m3_wshared span').text('グローバル属性を設定');
				} else {
					$('#m3_wshared span').text('グローバル属性を解除');
				}
				return true;
			},
			onShowMenu: function(e, menu) {
				// メニュー項目の変更
				var attrs = m3_splitAttr($(e.target).parents('dl').attr('m3'));
				if (attrs['useconfig'] == '0'){
					$('#m3_wconfig', menu).remove();
				}
				return menu;
			}
		});
	};
	var updateSortable = function(e, ui){
		var obj = $('#' + ui.item[0].id);
		var recvPosObj = obj.parents('.m3_widgetpos_box');	// 移動先ポジション
		var pos = '';
		if (recvPosObj.attr('id') == $(this).attr('id')){
			if (ui.sender){
				var senderObj = $('#' + ui.sender[0].id);
				var attrs = m3_splitAttr(senderObj.attr('m3'));
				pos = attrs['pos'];
			}
			m3TaskWidget('move', obj, '', -1, pos);
		}
	};
	
	// ウィジェット一覧取得後の更新処理
	var updateWidgetList = function(){
		// アコーディオンメニュー作成
		$('.m3accordion').children('dd').slideUp();
		$('.m3accordion').children('dt').click(function(){
			if($(this).hasClass('active')){
				$(this).removeClass('active');
				$(this).next('dd').slideUp();
				
				// 一覧編集ボタン
				$('#m3paneltab_edit').show();
			} else{
				$('.m3accordion').children('dt').removeClass('active');
				$('.m3accordion').children('dd').slideUp();
				$(this).addClass('active');
				$(this).next('dd').slideDown();
				
				// 一覧編集ボタン
				$('#m3paneltab_edit').hide();
			}
		});
		
		$('dl.m3_widgetlist_item').draggable({
			helper: 'clone',
			cursor: 'move',
			cursorAt: { top: CURSOR_OFFSET, left: CURSOR_OFFSET },	// 左上に初期化
			opacity: 0.5,
			distance: 10,
			zIndex:2001,
			appendTo: 'html',
			drag: function(e, ui){
				var objects = $('.m3_widgetpos_box');
				if (objects.length > 0){
					for (var i = 0; i < objects.length; i++){
						var widget = $(objects[i]);
						if (mouseInDrag(widget, e)) return;
					}
				}
			},
			start: function(e, ui){
				// ドラッグ中のイメージの幅を固定
				ui.helper.css("width", DRAG_ITEM_WIDTH);
			},
			stop: function(e, ui){
				m3TaskWidget('add', $('.m3_spacer'), this.id, m3InsertIndex);
			}
		});
	}
	// ウィジェットリストからのドラッグ処理
	var mouseInDrag = function(obj, e){
		var width = obj.width();
		var height = obj.height();
		var left = obj.offset().left;
		var top = obj.offset().top;

		if (left <= e.pageX && e.pageX <= left + width &&
			top <= e.pageY && e.pageY <= top + height){

			// ドロップする位置
			var children = obj.children('.m3_widget_sortable');
			var spacePos = -1;
			for (var i = 0; i < children.length; i++){
				var child = $(children[i]);
				if (child.hasClass('m3_spacer')){
					spacePos = i;
					break;
				}
			}
			
			var insertPos = -1;
			var i = 0;
			var play = 0;
			for (i = 0; i < children.length; i++){
				var child = $(children[i]);
				var childLeft = child.offset().left;
				var childTop = child.offset().top;
				var childWidth = child.width();
				var childHeight = child.height();

				play = 0;
				if (spacePos != -1){
					if (i == spacePos - 1){
						play = -10;
					} else if (i == spacePos + 1){
						play = 10;
					} else if (i == spacePos){
						if (childLeft <= e.pageX && e.pageX <= childLeft + childWidth &&
							childTop <= e.pageY && e.pageY <= childTop + childHeight){
							break;
						}
					}
				}
				if (e.pageY < child.offset().top + childHeight / 2 + play){
					break;
				}
			}
			var spacing = true;
			if (spacePos == -1){
				insertPos = i;
			} else {
				if (insertPos < spacePos){
					insertPos = i;
				} else {
					if (insertPos == spacePos || insertPos == spacePos +1){
						spacing = false;
					} else {
						insertPos = i -1;
					}
				}
			}
			if (spacing){
				$('.m3_spacer').remove();
				
				children = obj.children('.m3_widget_sortable');
				
				var spacerHtml = '<div class="m3_spacer"></div>';
				if (insertPos < children.length){
					$(children[insertPos]).before(spacerHtml);
				} else {
					obj.append(spacerHtml);
				}
				m3InsertIndex = insertPos;
			}
			return true;
		} else {
			return false;
		}
	};
	var createUrl = function(){
		var url;
		var urlParams = m3_getUrlParam(document.URL);
		var page = urlParams['_page'];
		var sub = urlParams['_sub'];
		url = M3_DEFAULT_ADMIN_URL + '?cmd=getwidgetinfo' + '&_page=' + page + '&_sub=' + sub;
		return url;
	};
	// 初期処理
	$(function(){
		var widgetWindow = '';
		// ウィジェット一覧(左パネル)
		widgetWindow += '<div id="m3slidepanel" class="m3panel_left m3-navbar-default" style="left:-240px; visibility:visible;">';
		widgetWindow += '<div class="m3panelopener m3topleft"><a href="#" rel="m3help" data-placement="bottom" data-container="body" title="ウィジェット一覧"><i class="fas fa-th-list fa-2x"></i></a></div>';
		widgetWindow += '<div class="m3paneltab">';
		widgetWindow += '<ul>';
		widgetWindow += '<li><a href="#m3paneltab_widget">ウィジェット</a></li>';
		widgetWindow += '</ul>';
		widgetWindow += '<div id="m3paneltab_widget">';
		widgetWindow += '<div class="m3message"></div>';
		widgetWindow += '<div id="m3paneltab_widget_list" class="m3widgetlist">';
		widgetWindow += '</div>';	// m3widgetlist end
		widgetWindow += '</div>';	// m3paneltab_widget end
		widgetWindow += '<div><a id="m3paneltab_edit" href="#" rel="m3help" data-container="body" title="一覧を編集"><i class="fas fa-edit"></i></a></div>';
		widgetWindow += '</div>';	// m3paneltab_wrap end
		widgetWindow += '</div>';	// m3panel_left end
		
		// その他ポジション(右パネル)
		widgetWindow += '<div id="m3slidepanel2" class="m3panel_right m3-navbar-default" style="right:-240px; visibility:visible;">';
		widgetWindow += '<div class="m3panelopener m3topright2"><a href="#" rel="m3help" data-placement="bottom" data-container="body" title="その他のポジション"><i class="fas fa-folder fa-2x"></i></a></div>';
		widgetWindow += '<div class="m3paneltab">';
		widgetWindow += '<ul>';
		widgetWindow += '<li><a href="#m3paneltab_position">その他ポジション</a></li>';
		widgetWindow += '</ul>';
		widgetWindow += '<div id="m3paneltab_position">';
		widgetWindow += '<div class="m3message"></div>';
		widgetWindow += '<div id="m3paneltab_widget_list2" class="m3widgetlist">';
		widgetWindow += M3_REST_POSITION_DATA;			// その他のポジションデータ
		widgetWindow += '</div>';	// m3widgetlist end
		widgetWindow += '</div>';	// m3paneltab_widget end
		widgetWindow += '</div>';	// m3paneltab_wrap end
		widgetWindow += '</div>';	// m3panel_right end
		
		// コンテキストメニュー
		widgetWindow += '<div class="m3_contextmenu" id="m3_widgetmenu" style="visibility:hidden;">';
		widgetWindow += '<ul>';
		widgetWindow += '<li id="m3_wconfig"><i class="fas fa-cog"></i>&nbsp;<span>ウィジェットの設定</span></li>';
		widgetWindow += '<li id="m3_wadjust"><i class="fas fa-window-maximize"></i>&nbsp;<span>タイトル・スタイル調整</span></li>';
		widgetWindow += '<li id="m3_wshared"><i class="fas fa-newspaper"></i>&nbsp;<span>グローバル属性</span></li>';
		widgetWindow += '<li id="m3_wdelete"><i class="fas fa-times-circle text-danger"></i>&nbsp;<span>このウィジェットを削除</span></li>';
		widgetWindow += '</ul>';
		widgetWindow += '</div>';
		
		// 画面リサイズボタン(右上)
		if (!(window == window.parent)){		// 親ウィンドウありの場合
			//widgetWindow += '<div class="m3resizer m3topright"><a href="#" rel="m3help" data-placement="bottom" data-container="body" title="画面の拡大縮小"><i class="glyphicon"></i></a></div>';
			widgetWindow += '<div class="m3resizer m3topright"><a href="#" rel="m3help" data-placement="bottom" data-container="body" title="画面の拡大縮小"><i class="fas fa-2x"></i></a></div>';
    	}
		
		$("body").append(widgetWindow);
		
		// パネルスライド可能にする
		$("body").css("position", "relative");
		
		// サイドパネル作成
		$('.m3panel_left').m3slidepanel({ "position" : "left", "type" : "push" });
		$('.m3panel_right').m3slidepanel({ "position" : "right", "type" : "push" });
		if (!M3_REST_POSITION_DATA) $('.m3panel_right').hide();		// データがない場合は非表示

		// タグ作成
		$(".m3panel_left ul").idTabs();
		
		// スライドメニュー高さ調整(左パネル)
		var pos = $('#m3paneltab_widget_list').position();
		//var slideMenuHeight = $(window).height() - pos.top - 30;	// スクロールバー部分調整
		var slideMenuHeight = $(window).height() - pos.top - 20;	// スクロールバー部分調整
		$('#m3paneltab_widget_list').height(slideMenuHeight);
		// スライドメニュー高さ調整(右パネル)
		pos = $('#m3paneltab_widget_list2').position();
		slideMenuHeight = $(window).height() - pos.top - 20;	// スクロールバー部分調整
		$('#m3paneltab_widget_list2').height(slideMenuHeight);
	
		// 画面リサイズボタン
		$(".m3resizer.m3topright a").click(function(){
			var openerCss = {};
			var iframeObj = parent.$('#layout_preview');
			var iconObj = $('i', this);
			
			if (iframeObj.hasClass('layout_top_border')){		// デフォルト(縮小)状態のとき
				// 画面を拡大
				parent.$("#layout_preview_outer").css('z-index', '1100');
				openerCss['top'] = '0px';
				
				iframeObj.removeClass('layout_top_border');
				//iconObj.removeClass('glyphicon-resize-full');
				//iconObj.addClass('glyphicon-resize-small');
				iconObj.removeClass('fa-expand-alt');
				iconObj.addClass('fa-compress-alt');
			} else {
				// 画面を縮小
				parent.$("#layout_preview_outer").css('z-index', '1');
				openerCss['top'] = '240px';		// 高さ調整
				
				iframeObj.addClass('layout_top_border');
				//iconObj.removeClass('glyphicon-resize-small');
				//iconObj.addClass('glyphicon-resize-full');
				iconObj.removeClass('fa-compress-alt');
				iconObj.addClass('fa-expand-alt');
			}
			
			// 画面をリサイズ
			parent.$("#layout_preview_outer").animate(openerCss, { 'dulation': 350, 'complete': function(){
				// スライドメニュー高さ調整(左パネル)
				var pos = $('#m3paneltab_widget_list').position();
				//var slideMenuHeight = $(window).height() - pos.top - 30;	// スクロールバー部分調整
				var slideMenuHeight = $(window).height() - pos.top - 20;	// スクロールバー部分調整
				$('#m3paneltab_widget_list').height(slideMenuHeight);
				// スライドメニュー高さ調整(右パネル)
				pos = $('#m3paneltab_widget_list2').position();
				slideMenuHeight = $(window).height() - pos.top - 20;	// スクロールバー部分調整
				$('#m3paneltab_widget_list2').height(slideMenuHeight);
			}});

			return false;		// クリックイベント終了
		});
		
		// 画面リサイズボタンアイコン初期化
		if (parent.$('#layout_preview').hasClass('layout_top_border')){		// デフォルト(縮小)状態のとき
			//$(".m3resizer.m3topright a i").addClass('glyphicon-resize-full');
			$(".m3resizer.m3topright a i").addClass('fa-expand-alt');
		} else {
			//$(".m3resizer.m3topright a i").addClass('glyphicon-resize-small');
			$(".m3resizer.m3topright a i").addClass('fa-compress-alt');
		}
		
		// 編集アイコン作成
		$('#m3paneltab_edit').click(function(){
			// 一覧の設定画面を表示
			m3ShowTaskWindow('editwidgetmenu');
			return false;
		});
		
		setupSortable();
		
		// ウィジェット一覧取得
		$.ajax({	url: createUrl() + '&task=list',
					type:		'get',
					success:	function(data, textStatus){
									$("#m3paneltab_widget_list").html(data);
									updateWidgetList();
				
									// ヘルプを設定
									m3SetHelp();
								},
					error:		function(request, textStatus, errorThrown){
									$(".m3message").text('通信エラー');
								}
		});
		$('div.m3_widgetpos_box').droppable({
			accept: 'dl.m3_widgetlist_item',
			out: function(e, ui){
				$('.m3_spacer').remove();
			}
		});
		
		// テキスト選択停止(Safari,Chromeのウィジェットドラッグ中のテキスト選択の問題を回避)
		document.onselectstart = function(){ return false; }
	});		// 初期処理終了
})(jQuery);
