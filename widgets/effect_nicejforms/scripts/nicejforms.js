/**
 * @name NiceJForms
 * @description This a jQuery equivalent for Niceforms ( http://badboy.ro/articles/2007-01-30/niceforms/ ).  All the forms are styled with beautiful images as backgrounds and stuff. Enjoy them!
 * @param Hash hash A hash of parameters
 * @option integer selectRightSideWidth width of right side of the select
 * @option integer selectLeftSideWidth width of left side of the select 
 * @option integer selectAreaHeight
 * @option integer selectAreaOPtionsOverlap
 * @option imagesPath folder where custom form images are stored
 * @type jQuery
 * @cat Plugins/Interface/Forms
 * @author Lucian Lature ( lucian.lature@gmail.com )
 * @credits goes to Lucian Slatineanu ( http://www.badboy.ro )
 * @version 0.1
 *
 * modified by naoki on 2009.3.18
 */

jQuery.NiceJForms = {
	options : {
		selectWidth 	         : 200,
		selectRightSideWidth     : 21,
		selectLeftSideWidth      : 8,
		selectAreaHeight 	     : 21,
		selectAreaOptionsOverlap : 2,
		imagesPath               : "css/images/default/",
		className                : 'form' // default class name
		// other options here
	},
	
	selectText     : 'please select',
	preloads       : new Array(),
//	inputs         : new Array(),
	labels         : new Array(),
	textareas      : new Array(),
	selects        : new Array(),
	radios         : new Array(),
	checkboxes     : new Array(),
	texts          : new Array(),
	buttons        : new Array(),
	radioLabels    : new Array(),
	checkboxLabels : new Array(),
	hasImages      : true,
	
	keyPressed : function(event)
	{
		var pressedKey = event.charCode || event.keyCode || -1;
		
		switch (pressedKey)
		{
			case 40: //down
			if (this.parentNode){
				var fieldId = this.parentNode.parentNode.id.replace(/sarea/g, "");
				var linkNo = 0;
				var info = fieldId.split("-");
				var index = info[0];
				var no = info[1];
				for(var q = 0; q < selects[index][no].options.length; q++) {if(selects[index][no].options[q].selected) {linkNo = q;}}
				++linkNo;
				if(linkNo >= selects[index][no].options.length) {linkNo = 0;}
				selectMe(selects[index][no].id, linkNo, fieldId);
			}
			break;
		
		case 38: //up
			if (this.parentNode){
				var fieldId = this.parentNode.parentNode.id.replace(/sarea/g, "");
				var linkNo = 0;
				var info = fieldId.split("-");
				var index = info[0];
				var no = info[1];
				for(var q = 0; q < selects[index][no].options.length; q++) {if(selects[index][no].options[q].selected) {linkNo = q;}}
				--linkNo;
				if(linkNo < 0) {linkNo = selects[index][no].options.length - 1;}
				selectMe(selects[index][no].id, linkNo, fieldId);
			}
			break;
		default:
			break;
		}
	},
	
	build : function(options)
	{
		if (options)
			jQuery.extend(jQuery.NiceJForms.options, options);	
			
		if (window.event) {
			jQuery('body',document).bind('keyup', jQuery.NiceJForms.keyPressed);
		} else {
			jQuery(document).bind('keyup', jQuery.NiceJForms.keyPressed);
		}
		
		// test if images are disabled or not
		var testImg = document.createElement('img');
		$(testImg).attr("src", jQuery.NiceJForms.options.imagesPath + "blank.gif").attr("id", "imagineTest");
		jQuery('body').append(testImg);
		
		if(testImg.complete)
		{
			if(testImg.offsetWidth == '1') {jQuery.NiceJForms.hasImages = true;}
			else {jQuery.NiceJForms.hasImages = false;}
		}

		$(testImg).remove();
			
		if(jQuery.NiceJForms.hasImages)
		{
			var index = 0;
			
			// form class changed by naoki.
			var name = 'form.' + jQuery.NiceJForms.options.className;
			$(name).each( function()
				{
					el 				= jQuery(this);
					jQuery.NiceJForms.preloadImages();
					jQuery.NiceJForms.getElements(el, index);
					jQuery.NiceJForms.replaceRadios(index);
					jQuery.NiceJForms.replaceCheckboxes(index);
					jQuery.NiceJForms.replaceSelects(index);
					
					if (!$.browser.safari) {
						jQuery.NiceJForms.replaceTexts(index);
						jQuery.NiceJForms.replaceTextareas(index);
						jQuery.NiceJForms.buttonHovers(index);
					}
					index++;
				}
			);
		}	
	},
	
	preloadImages: function()
	{
		jQuery.NiceJForms.preloads = $.preloadImages(jQuery.NiceJForms.options.imagesPath + "button_left_xon.gif", jQuery.NiceJForms.options.imagesPath + "button_right_xon.gif", 
		jQuery.NiceJForms.options.imagesPath + "input_left_xon.gif", jQuery.NiceJForms.options.imagesPath + "input_right_xon.gif",
		jQuery.NiceJForms.options.imagesPath + "txtarea_bl_xon.gif", jQuery.NiceJForms.options.imagesPath + "txtarea_br_xon.gif", 
		jQuery.NiceJForms.options.imagesPath + "txtarea_cntr_xon.gif", jQuery.NiceJForms.options.imagesPath + "txtarea_l_xon.gif", jQuery.NiceJForms.options.imagesPath + "txtarea_tl_xon.gif", jQuery.NiceJForms.options.imagesPath + "txtarea_tr_xon.gif");
	},
	
	getElements: function(elm, index)
	{
		el = elm ? jQuery(elm) : jQuery(this);
		
		var r = 0; var c = 0; var t = 0; var rl = 0; var cl = 0; var tl = 0; var b = 0;
		
//		jQuery.NiceJForms.inputs = $('input', el);
		jQuery.NiceJForms.labels[index] = $('label', el);
		jQuery.NiceJForms.textareas[index] = $('textarea', el);
		jQuery.NiceJForms.selects[index] = $('select', el);
		jQuery.NiceJForms.radios[index] = $('input[type=radio]', el);
		jQuery.NiceJForms.checkboxes[index] = $('input[type=checkbox]', el);
		jQuery.NiceJForms.texts[index] = $('input[type=text]', el).add($('input[type=password]', el));		
		jQuery.NiceJForms.buttons[index] = $('input[type=submit]', el).add($('input[type=button]', el));
		jQuery.NiceJForms.checkboxLabels[index] = new Array();
		jQuery.NiceJForms.radioLabels[index] = new Array();
		
		jQuery.NiceJForms.labels[index].each(function(i){
			labelFor = $(jQuery.NiceJForms.labels[index][i]).attr("for");
			jQuery.NiceJForms.radios[index].each(function(q){
				if(labelFor == $(jQuery.NiceJForms.radios[index][q]).attr("id"))
				{
					if(jQuery.NiceJForms.radios[index][q].checked)
					{
						$(jQuery.NiceJForms.labels[index][i]).removeClass().addClass("chosen");	
					}
					
					jQuery.NiceJForms.radioLabels[index][rl] = jQuery.NiceJForms.labels[index][i];
					++rl;
				}
			})
			
			jQuery.NiceJForms.checkboxes[index].each(function(x){
				if(labelFor == $(this).attr("id"))
				{
					if(this.checked)
					{
						$(jQuery.NiceJForms.labels[index][i]).removeClass().addClass("chosen");	
					}
					jQuery.NiceJForms.checkboxLabels[index][cl] = jQuery.NiceJForms.labels[index][i];
					++cl;
				}
			})
		});
	},
	
	replaceRadios: function(index)
	{
		var self = this;
		
		jQuery.NiceJForms.radios[index].each(function(q){
		
			$(this).removeClass().addClass('outtaHere'); //.hide(); //.className = "outtaHere";
			
			var radioArea = document.createElement('div');
			//console.info($(radioArea));
			if(this.checked) {$(radioArea).removeClass().addClass("radioAreaChecked");} else {$(radioArea).removeClass().addClass("radioArea");};
			
			radioPos = jQuery.iUtil.getPosition(this);
			
			jQuery(radioArea)
				.attr({id: 'myRadio' + index + '-' + q})
				.css({left: radioPos.x + 'px', top: radioPos.y + 'px', margin : '1px'})
				.bind('click', {who: index + '-' + q}, function(e){self.rechangeRadios(e)})
				.insertBefore($(this));
			
			if (jQuery.NiceJForms.radioLabels[index][q]) $(jQuery.NiceJForms.radioLabels[index][q]).bind('click', {who: index + '-' + q}, function(e){self.rechangeRadios(e)});
			
			if (!$.browser.msie) {
				$(this).bind('focus', function(){self.focusRadios(q)}).bind('blur', function() {self.blurRadios(q)});
			}
			
			$(this).bind('click', {who: index + '-' + q}, function(e){self.radioEvent(e)});
		});
		
		return true;
	},
	
	changeRadios: function(who)
	{
		var self = this;
		var info = e.data.who.split("-");
		var index = info[0];
		var no = info[1];
		
		if(jQuery.NiceJForms.radios[index][no].checked) {
		
			jQuery.NiceJForms.radios[index].each(function(q){
				if($(this).attr("name") == $(jQuery.NiceJForms.radios[index][no]).attr("name"))
				{
					this.checked = false;
					$(jQuery.NiceJForms.radioLabels[index][q]).removeClass();	
				}
			});
			jQuery.NiceJForms.radios[index][no].checked = true;
			$(jQuery.NiceJForms.radioLabels[index][no]).addClass("chosen");
			
			self.checkRadios(e.data.who);
		}
	},
	
	rechangeRadios:function(e) 
	{
		var info = e.data.who.split("-");
		var index = info[0];
		var no = info[1];
		
		if(!jQuery.NiceJForms.radios[index][no].checked) {
			for(var q = 0; q < jQuery.NiceJForms.radios[index].length; q++) 
			{
				if(jQuery.NiceJForms.radios[index][q].name == jQuery.NiceJForms.radios[index][no].name) 
				{
					jQuery.NiceJForms.radios[index][q].checked = false; 
					//console.info(q);
					if (jQuery.NiceJForms.radioLabels[index][q]) jQuery.NiceJForms.radioLabels[index][q].className = "";
				}
			}
			$(jQuery.NiceJForms.radios[index][no]).attr('checked', true); 
			if (jQuery.NiceJForms.radioLabels[index][no]) jQuery.NiceJForms.radioLabels[index][no].className = "chosen";
			jQuery.NiceJForms.checkRadios(e.data.who);
		}
	},
	
	checkRadios: function(who)
	{
		var info = who.split("-");
		var index = info[0];
		var no = info[1];
		
		$('div').each(function(q){
			if($(this).is(".radioAreaChecked") && $(this).next().attr("name") == $(jQuery.NiceJForms.radios[index][no]).attr("name")) {$(this).removeClass().addClass("radioArea");}
		});
		$('#myRadio' + who).toggleClass("radioAreaChecked");
	},
	
	focusRadios: function(who) {
		$('#myRadio' + who).css({border: '1px dotted #333', margin: '0'}); return false;
	},
	
	blurRadios:function(who) {
		$('#myRadio' + who).css({border: 'none', margin: '1px'}); return false;
	},
	
	radioEvent: function(e) {
		var self = this;
		if (!e) var e = window.event;
		var info = e.data.who.split("-");
		var index = info[0];
		var no = info[1];
		
		if(e.type == "click") {
			for (var q = 0; q < jQuery.NiceJForms.radios[index].length; q++) {
				if(this == jQuery.NiceJForms.radios[index][q]) {
					self.changeRadios(q); break;
				}
			}
		}
	},
	
	replaceCheckboxes: function (index)
	{
		var self = this;
		
		jQuery.NiceJForms.checkboxes[index].each(function(q){
			//move the checkboxes out of the way
			$(jQuery.NiceJForms.checkboxes[index][q]).removeClass().addClass('outtaHere');
			//create div
			var checkboxArea = document.createElement('div');
			
			//console.info($(radioArea));
			if(jQuery.NiceJForms.checkboxes[index][q].checked) {$(checkboxArea).removeClass().addClass("checkboxAreaChecked");} else {$(checkboxArea).removeClass().addClass("checkboxArea");};
			
			checkboxPos = jQuery.iUtil.getPosition(jQuery.NiceJForms.checkboxes[index][q]);
			
			jQuery(checkboxArea)
				.attr({id: 'myCheckbox' + index + '-' + q})
				.css({
				left: checkboxPos.x + 'px', 
				top: checkboxPos.y + 'px',
				margin : '1px'
			})
			.bind('click', {who: index + '-' + q}, function(e){self.rechangeCheckboxes(e)})
			.insertBefore($(jQuery.NiceJForms.checkboxes[index][q]));
			
			if(!$.browser.safari)
			{
				$(jQuery.NiceJForms.checkboxLabels[index][q]).bind('click', {who: index + '-' + q}, function(e){self.changeCheckboxes(e)})
			}
			else {
				$(jQuery.NiceJForms.checkboxLabels[index][q]).bind('click', {who: index + '-' + q}, function(e){self.rechangeCheckboxes(e)})
			}
			
			if(!$.browser.msie)
			{
				$(jQuery.NiceJForms.checkboxes[index][q]).bind('focus', {who: index + '-' + q}, function(e){self.focusCheckboxes(e)});
				$(jQuery.NiceJForms.checkboxes[index][q]).bind('blur', {who: index + '-' + q}, function(e){self.blurCheckboxes(e)});
			}	
			
			//$(jQuery.NiceJForms.checkboxes[index][q]).keydown(checkEvent);
		});
		return true;
	},

	rechangeCheckboxes: function(e)
	{
		var info = e.data.who.split("-");
		var index = info[0];
		var no = info[1];
		var tester = false;

		if($(jQuery.NiceJForms.checkboxLabels[index][no]).is(".chosen")) {
			tester = false;
			$(jQuery.NiceJForms.checkboxLabels[index][no]).removeClass();
		}
		else if(jQuery.NiceJForms.checkboxLabels[index][no].className == "") {
			tester = true;
			$(jQuery.NiceJForms.checkboxLabels[index][no]).addClass("chosen");
		}
		jQuery.NiceJForms.checkboxes[index][no].checked = tester;
		jQuery.NiceJForms.checkCheckboxes(e.data.who, tester);
	},

	checkCheckboxes: function(who, action)
	{
		var what = $('#myCheckbox' + who);
		if(action == true) {$(what).removeClass().addClass("checkboxAreaChecked");}
		if(action == false) {$(what).removeClass().addClass("checkboxArea");}
	},

	focusCheckboxes: function(who) 
	{
		var what = $('#myCheckbox' + who);
		$(what).css(
					{
						border : "1px dotted #333", 
						margin : "0"
					});	
		return false;
	},

	changeCheckboxes: function(e)
	{
		var info = e.data.who.split("-");
		var index = info[0];
		var no = info[1];

		//console.log('changeCheckboxes who is ' + who);
		if($(jQuery.NiceJForms.checkboxLabels[index][no]).is(".chosen")) {
			jQuery.NiceJForms.checkboxes[index][no].checked = true;
			$(jQuery.NiceJForms.checkboxLabels[index][no]).removeClass();
			jQuery.NiceJForms.checkCheckboxes(e.data.who, false);
		}
		else if(jQuery.NiceJForms.checkboxLabels[index][no].className == "") {
			jQuery.NiceJForms.checkboxes[index][no].checked = false;
			$(jQuery.NiceJForms.checkboxLabels[index][no]).toggleClass("chosen");
			jQuery.NiceJForms.checkCheckboxes(e.data.who, true);
		}
	},

	blurCheckboxes: function(who) 
	{
		var what = $('#myCheckbox' + who);
		$(what).css(
					{
						border : 'none', 
						margin : '1px'
					});	
		return false;
	},
	
	replaceSelects: function(index)
	{
		var self = this;
		
		jQuery.NiceJForms.selects[index].each(function(q){
			//create and build div structure
			var selectArea = document.createElement('div');
			var left = document.createElement('div');
			var right = document.createElement('div');
			var center = document.createElement('div');
			var button = document.createElement('a');
			var text = document.createTextNode(jQuery.NiceJForms.selectText);
			var widthStr = this.className.replace(/width_/g, "");
			if (!widthStr) widthStr = jQuery.NiceJForms.options.selectWidth;
			var selectWidth = parseInt(widthStr);
			
			jQuery(center)
				.attr({id:'mySelectText' + index + '-' + q})
				.css({width: selectWidth - 10 + 'px'});
			jQuery(selectArea)
				.attr({id:'sarea' + index + '-' + q})
				.css({
					width: selectWidth + jQuery.NiceJForms.options.selectRightSideWidth + jQuery.NiceJForms.options.selectLeftSideWidth + 'px'
				})
				.addClass("selectArea");
				
			jQuery(button)
				.css({
				width      : selectWidth + jQuery.NiceJForms.options.selectRightSideWidth + jQuery.NiceJForms.options.selectLeftSideWidth + 'px',
				marginLeft : - selectWidth - jQuery.NiceJForms.options.selectLeftSideWidth + 'px',
				cursor: 'pointer'
				})
				.addClass("selectButton")
				.bind('click', {who: index + '-' + q}, function(e){self.showOptions(e)})
				.keydown(jQuery.NiceJForms.keyPressed);
			
			jQuery(left).addClass("left");	
			jQuery(right).addClass("right").append(button);	
			jQuery(center).addClass("center").append(text);	
			
			jQuery(selectArea).append(left).append(right).append(center).insertBefore(this);
			//hide the select field
			$(this).hide();
			//insert select div
			//build & place options div
			var optionsDiv = document.createElement('div');
			selectAreaPos = jQuery.iUtil.getPosition(selectArea);
			
			jQuery(optionsDiv)
				.attr({id:"optionsDiv" + index + '-' + q})
				.css({
					width : selectWidth + 1 + 'px', 
					left  : selectAreaPos.x + 'px', 
					top   : selectAreaPos.y + jQuery.NiceJForms.options.selectAreaHeight - jQuery.NiceJForms.options.selectAreaOptionsOverlap + 'px'
				})
				.addClass("optionsDivInvisible");
			
			//get select's options and add to options div
			$(jQuery.NiceJForms.selects[index][q]).children().each(function(w){
				var optionHolder = document.createElement('p');
				var optionLink = document.createElement('a');
				var optionTxt = document.createTextNode(jQuery.NiceJForms.selects[index][q].options[w].text);
				
				jQuery(optionLink)
					.attr({href:'#'})
					.css({cursor:'pointer'})
					.append(optionTxt)
					.bind('click', {who: index + '-' + q, id:jQuery.NiceJForms.selects[index][q].id, option:w, select: index + '-' + q}, function(e){self.showOptions(e);self.selectMe(jQuery.NiceJForms.selects[index][q].id, w, index + '-' + q)});
				
				jQuery(optionHolder).append(optionLink);
				jQuery(optionsDiv).append(optionHolder);
				
				//check for pre-selected items
				if(jQuery.NiceJForms.selects[index][q].options[w].selected) {self.selectMe($(jQuery.NiceJForms.selects[index][q]).attr("id"), w, index + '-' + q);}
			});
			
			jQuery('body').append(optionsDiv);
		});
	},

	selectMe: function(selectFieldId, linkNo, selectNo) {
		selectField = $('#' + selectFieldId);
		sFoptions = selectField.children();
		var valueChanged = false;
		selectField.children().each(function(k){
			if(k == linkNo){
				if (sFoptions[k].selected == "") valueChanged = true;;
				sFoptions[k].selected="selected";
			} else {
				sFoptions[k].selected = "";
			}
		});
		
		textVar = $("#mySelectText" + selectNo);
		var newText = document.createTextNode($(sFoptions[linkNo]).text());
		textVar.empty().append(newText);
		
		if (valueChanged) selectField.change();
	}, 

	showOptions: function(e) {
		var self = this;
		$("#optionsDiv"+e.data.who).toggleClass("optionsDivVisible").toggleClass("optionsDivInvisible").mouseout(function(e){self.hideOptions(e)});
	},
	
	hideOptions: function(e) {
		if (!e) var e = window.event;
		var reltg = (e.relatedTarget) ? e.relatedTarget : e.toElement;
		if(((reltg.nodeName != 'A') && (reltg.nodeName != 'DIV')) || ((reltg.nodeName == 'A') && (reltg.className=="selectButton") && (reltg.nodeName != 'DIV'))) {this.className = "optionsDivInvisible";};
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	},
	
	replaceTexts: function(index) {
		jQuery.NiceJForms.texts[index].each(function(q){
			$(jQuery.NiceJForms.texts[index][q]).css({width:this.size * 10 + 'px'});
			var txtLeft = new Image();
			jQuery(txtLeft)
				.attr({src:jQuery.NiceJForms.options.imagesPath + "input_left.gif"})
				.addClass("inputCorner");
			
			var txtRight = new Image();
			jQuery(txtRight)
				.attr({src:jQuery.NiceJForms.options.imagesPath + "input_right.gif"})
				.addClass("inputCorner");
			
			$(jQuery.NiceJForms.texts[index][q]).before(txtLeft).after(txtRight).addClass("textinput");
			
			//create hovers
			$(jQuery.NiceJForms.texts[index][q]).focus(function(){$(this).addClass("textinputHovered");$(this).prev().attr('src', jQuery.NiceJForms.options.imagesPath + "input_left_xon.gif");$(this).next().attr('src', jQuery.NiceJForms.options.imagesPath + "input_right_xon.gif");});
			
			$(jQuery.NiceJForms.texts[index][q]).blur(function() {$(this).removeClass().addClass("textinput");$(this).prev().attr('src', jQuery.NiceJForms.options.imagesPath + "input_left.gif");$(this).next().attr('src', jQuery.NiceJForms.options.imagesPath + "input_right.gif");});
		});
	},
	
	replaceTextareas: function(index)
	{
		jQuery.NiceJForms.textareas[index].each(function(q){
			
			var where = $(this).parent();
			var where2 = $(this).prev();
			
			// ##### textarea removed problem fixed by naoki. #####
			var insertPos = document.createElement('span');
			$(this).before(jQuery(insertPos));
			
			$(this).css({width: $(this).attr("cols") * 10 + 'px', height: $(this).attr("rows") * 10 + 'px'});
			//create divs
			var container = document.createElement('div');
			jQuery(container)
				.css({width: jQuery.NiceJForms.textareas[index][q].cols * 10 + 20 + 'px', height: jQuery.NiceJForms.textareas[index][q].rows * 10 + 20 + 'px'})
				.addClass("txtarea");
			
			var topRight = document.createElement('div');
			jQuery(topRight).addClass("tr");
			
			var topLeft = new Image();
			jQuery(topLeft).attr({src: jQuery.NiceJForms.options.imagesPath + 'txtarea_tl.gif'}).addClass("txt_corner");
			
			var centerRight = document.createElement('div');
			jQuery(centerRight).addClass("cntr");
			var centerLeft = document.createElement('div');
			jQuery(centerLeft).addClass("cntr_l");
			
			if(!$.browser.msie) {jQuery(centerLeft).height(jQuery.NiceJForms.textareas[index][q].rows * 10 + 10 + 'px')}
			else {jQuery(centerLeft).height(jQuery.NiceJForms.textareas[index][q].rows * 10 + 12 + 'px')};
			
			var bottomRight = document.createElement('div');
			jQuery(bottomRight).addClass("br");
			var bottomLeft = new Image();
			jQuery(bottomLeft).attr({src: jQuery.NiceJForms.options.imagesPath + 'txtarea_bl.gif'}).addClass('txt_corner');
			
			//assemble divs
			jQuery(topRight).append(topLeft);
			jQuery(centerRight).append(centerLeft).append(jQuery.NiceJForms.textareas[index][q]);
			jQuery(bottomRight).append(bottomLeft);
			jQuery(container).append(topRight).append(centerRight).append(bottomRight);
			
			// ##### textarea removed problem fixed by naoki. #####
			//jQuery(where2).before(container);
			jQuery(insertPos).after(container);
			
			//create hovers
			$(jQuery.NiceJForms.textareas[index][q]).focus(function(){$(this).prev().removeClass().addClass("cntr_l_xon"); $(this).parent().removeClass().addClass("cntr_xon"); $(this).parent().prev().removeClass().addClass("tr_xon"); $(this).parent().prev().children(".txt_corner").attr('src', jQuery.NiceJForms.options.imagesPath + "txtarea_tl_xon.gif"); $(this).parent().next().removeClass().addClass("br_xon"); $(this).parent().next().children(".txt_corner").attr('src', jQuery.NiceJForms.options.imagesPath + "txtarea_bl_xon.gif")});
			$(jQuery.NiceJForms.textareas[index][q]).blur(function(){$(this).prev().removeClass().addClass("cntr_l"); $(this).parent().removeClass().addClass("cntr"); $(this).parent().prev().removeClass().addClass("tr"); $(this).parent().prev().children(".txt_corner").attr('src', jQuery.NiceJForms.options.imagesPath + "txtarea_tl.gif"); $(this).parent().next().removeClass().addClass("br"); $(this).parent().next().children(".txt_corner").attr('src', jQuery.NiceJForms.options.imagesPath + "txtarea_bl.gif")});
		});
	},
	
	buttonHovers: function(index) {
		jQuery.NiceJForms.buttons[index].each(function(i){
			$(this).addClass("buttonSubmit");
			var buttonLeft = document.createElement('img');
			jQuery(buttonLeft).attr({src: jQuery.NiceJForms.options.imagesPath + "button_left.gif"}).addClass("buttonImg");
			
			$(this).before(buttonLeft);
			
			var buttonRight = document.createElement('img');
			jQuery(buttonRight).attr({src: jQuery.NiceJForms.options.imagesPath + "button_right.gif"}).addClass("buttonImg");
			
			if($(this).next()) {$(this).after(buttonRight)}
			else {$(this).parent().append(buttonRight)};
			
			$(this).hover(
				function(){$(this).attr("class", $(this).attr("class") + "Hovered"); $(this).prev().attr("src", jQuery.NiceJForms.options.imagesPath + "button_left_xon.gif"); $(this).next().attr("src", jQuery.NiceJForms.options.imagesPath + "button_right_xon.gif")},
				function(){$(this).attr("class", $(this).attr("class").replace(/Hovered/g, "")); $(this).prev().attr("src", jQuery.NiceJForms.options.imagesPath + "button_left.gif"); $(this).next().attr("src", jQuery.NiceJForms.options.imagesPath + "button_right.gif")}
			);
		});
	}
}

jQuery.preloadImages = function()
{
	var imgs = new Array();
	for(var i = 0; i<arguments.length; i++)
	{
		imgs[i] = jQuery("<img>").attr("src", arguments[i]);
	}
	
	return imgs;
}

jQuery.iUtil = {
	getPosition : function(e)
	{
		var x = 0;
		var y = 0;
		var restoreStyle = false;
		var es = e.style;
		if (jQuery(e).css('display') == 'none') {
			oldVisibility = es.visibility;
			oldPosition = es.position;
			es.visibility = 'hidden';
			es.display = 'block';
			es.position = 'absolute';
			restoreStyle = true;
		}
		var el = e;
		while (el){
			x += el.offsetLeft + (el.currentStyle && !jQuery.browser.opera ?parseInt(el.currentStyle.borderLeftWidth)||0:0);
			y += el.offsetTop + (el.currentStyle && !jQuery.browser.opera ?parseInt(el.currentStyle.borderTopWidth)||0:0);
			el = el.offsetParent;
		}
		el = e;
		while (el && el.tagName  && el.tagName.toLowerCase() != 'body')
		{
			x -= el.scrollLeft||0;
			y -= el.scrollTop||0;
			el = el.parentNode;
		}
		if (restoreStyle) {
			es.display = 'none';
			es.position = oldPosition;
			es.visibility = oldVisibility;
		}
		return {x:x, y:y};
	},
	getPositionLite : function(el)
	{
		var x = 0, y = 0;
		while(el) {
			x += el.offsetLeft || 0;
			y += el.offsetTop || 0;
			el = el.offsetParent;
		}
		return {x:x, y:y};
	}
};