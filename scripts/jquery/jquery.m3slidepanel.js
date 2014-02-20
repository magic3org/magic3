/**
 * Magic3管理画面用スライドパネルjqueryプラグイン
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
if (typeof Object.create !== 'function'){
	Object.create = function(obj){
		function F(){};
		F.prototype = obj;
		return new F();
	};
}
;(function($){
	$.fn.m3slidepanel = function(options){
		return this.each(function(){
			var spanel = Object.create(m3SlidePanel);

			// オブジェクト初期化
			spanel.init(options, this);

			// タグに対応付け
			$.data(this, 'm3slidepanel', spanel);
		});
	};

	// デフォルト値
	$.fn.m3slidepanel.options = {
		position : "left",		//left, right, top, bottom
		type : "slide",			//slide or push
		easing : "easeOutQuad",
		animSpeed : 350,
		openerClass : 'm3panelopener'
	};
	
	var m3SlidePanel = {
		init: function( options, elem ) {
			var self = this;

			self.elem = elem;
			self.$elem = $( elem );
			self.options = $.extend({}, $.fn.m3slidepanel.options, options);

			// パネル幅または高さ
			if (self.options.position == 'left' || self.options.position == 'right'){
				self.containerWidth = self.$elem.width();		
			} else {
				self.containerWidth = self.$elem.height();
			}
			
			self.display(); 
			self.open();
		},
		display: function(){
			var self = this;
			var inlineCss = {};
			inlineCss[self.options.position] = -self.containerWidth + 'px';		// パネル幅
			inlineCss['visibility'] = 'visible';

			self.$elem.css(inlineCss);
		},
		open: function(){
			var self = this;
			var inlineCss = {};
			var openerCss = {};
			var openerSelecter = '.' + self.options.openerClass;

			$(openerSelecter + ' a').on('click',function(e){
				if(self.$elem.hasClass('opened')){
					inlineCss[self.options.position] =  -self.containerWidth + 'px';	// パネル幅
					openerCss[self.options.position] =  '0px';		// オープナー初期値

					self.$elem.animate(inlineCss, self.options.animSpeed);
					$(openerSelecter).animate(openerCss, self.options.animSpeed);

					// BODYを戻す
					if (self.options.type == "push"){
						$('body').animate(openerCss, self.options.animSpeed);
					}

					self.$elem.removeClass('opened');
				}else{
					inlineCss[self.options.position] =  '0px';
					openerCss[self.options.position] =  self.containerWidth + 'px';

					self.$elem.animate(
							inlineCss,
							self.options.animSpeed,
							self.options.easing
					);
					
					$(openerSelecter).animate(
							openerCss,
							self.options.animSpeed,
							self.options.easing
						);

					// BODYを移動
					if (self.options.type == "push"){
						$('body').animate(
							openerCss,
							self.options.animSpeed,
							self.options.easing
						);
					}

					self.$elem.addClass('opened');
				}
				e.preventDefault();
			});
		}
	};
})(jQuery);
