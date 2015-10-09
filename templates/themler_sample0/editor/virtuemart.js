
/* Script: /components/com_virtuemart/assets/js/vmprices.js */
if(typeof Virtuemart === "undefined")
	{
		var Virtuemart = {
			setproducttype : function (form, id) {
				form.view = null;
				var $ = jQuery, datas = form.serialize();
				var prices = form.parent(".productdetails").find(".product-price");
				if (0 == prices.length) {
					prices = jQuery("#productPrice" + id);
				}
				datas = datas.replace("&view=cart", "");
				prices.fadeTo("fast", 0.75);
                //encodeURIComponent(datas);
                jQuery.getJSON(window.vmSiteurl + 'index.php?option=com_virtuemart&nosef=1&view=productdetails&task=recalculate&virtuemart_product_id='+id+'&format=json' + window.vmLang, datas,
					function (datas, textStatus) {
						prices.fadeTo("fast", 1);
						// refresh price
						for (var key in datas) {
							var value = datas[key];
							if (value!=0) prices.find("span.Price"+key).show().html(value);
							else prices.find(".Price"+key).html(0).hide();
						}
					});
				return false; // prevent reload
			},
			productUpdate : function() {

                // This Event Gets Fired As Soon As The New Product
                // Was Added To The Cart
                // This Way Third Party Developer Can Include Their Own
                // Add To Cart Module And Listen To The Event: "updateVirtueMartCartModule"
                jQuery('body').trigger('updateVirtueMartCartModule');

			},
			sendtocart : function (form){

				if (Virtuemart.addtocart_popup ==1) {
					Virtuemart.cartEffect(form) ;
				} else {
					form.append('<input type="hidden" name="task" value="add" />');
					form.submit();
				}
			},
			cartEffect : function(form) {

                var $ = jQuery ;
                $.ajaxSetup({ cache: false });
                var dat = form.serialize();

                if(usefancy){
                    $.fancybox.showActivity();
                }

                $.getJSON(vmSiteurl+'index.php?option=com_virtuemart&nosef=1&view=cart&task=addJS&format=json'+vmLang, dat,
                function(datas, textStatus) {

                    if(datas.stat ==1){

                        var txt = datas.msg;
                    } else if(datas.stat ==2){
                        var txt = datas.msg +"<H4>"+form.find(".pname").val()+"</H4>";
                    } else {
                        var txt = "<H4>"+vmCartError+"</H4>"+datas.msg;
                    }
                    if(usefancy){
                        $.fancybox({
                                "titlePosition" : 	"inside",
                                "transitionIn"	:	"fade",
                                "transitionOut"	:	"fade",
                                "changeFade"    :   "fast",
                                "type"			:	"html",
                                "autoCenter"    :   true,
                                "closeBtn"      :   false,
                                "closeClick"    :   false,
                                "content"       :   txt
                            }
                        );
                    } else {
                        $.facebox.settings.closeImage = closeImage;
                        $.facebox.settings.loadingImage = loadingImage;
                        //$.facebox.settings.faceboxHtml = faceboxHtml;
                        $.facebox({ text: txt }, 'my-groovy-style');
                    }


                    Virtuemart.productUpdate();
                });

                $.ajaxSetup({ cache: true });
			},
			product : function(carts) {
				carts.each(function(){
					var cart = jQuery(this),
					step=cart.find('input[name="quantity"]'),
					addtocart = cart.find('input.addtocart-button'),
					plus   = cart.find('.quantity-plus'),
					minus  = cart.find('.quantity-minus'),
					select = cart.find('select:not(.no-vm-bind)'),
					radio = cart.find('input:radio:not(.no-vm-bind)'),
					virtuemart_product_id = cart.find('input[name="virtuemart_product_id[]"]').val(),
					quantity = cart.find('.quantity-input');

                    var Ste = parseInt(step.val());
                    //Fallback for layouts lower than 2.0.18b
                    if(isNaN(Ste)){
                        Ste = 1;
                    }
					addtocart.click(function(e) {
						Virtuemart.sendtocart(cart);
						return false;
					});
					plus.click(function() {
						var Qtt = parseInt(quantity.val());
						if (!isNaN(Qtt)) {
							quantity.val(Qtt + Ste);
						Virtuemart.setproducttype(cart,virtuemart_product_id);
						}
						
					});
					minus.click(function() {
						var Qtt = parseInt(quantity.val());
						if (!isNaN(Qtt) && Qtt>Ste) {
							quantity.val(Qtt - Ste);
						} else quantity.val(Ste);
						Virtuemart.setproducttype(cart,virtuemart_product_id);
					});
					select.change(function() {
						Virtuemart.setproducttype(cart,virtuemart_product_id);
					});
					radio.change(function() {
						Virtuemart.setproducttype(cart,virtuemart_product_id);
					});
					quantity.keyup(function() {
						Virtuemart.setproducttype(cart,virtuemart_product_id);
					});
				});

			}
		};
		jQuery.noConflict();
		jQuery(document).ready(function($) {

			Virtuemart.product($("form.product"));

			$("form.js-recalculate").each(function(){
				if ($(this).find(".product-fields").length && !$(this).find(".no-vm-bind").length) {
					var id= $(this).find('input[name="virtuemart_product_id[]"]').val();
					Virtuemart.setproducttype($(this),id);

				}
			});
		});
	}

/* Script: /components/com_virtuemart/assets/js/vmsite.js */
/**
 * vmsite.js: General Javascript Library for VirtueMart Frontpage
 *
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author Patrick Kohl
 * @author Max Milbers
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

(function($){
	var undefined,
	methods = {
		list: function(options) {
			var dest = options.dest;
			var ids = options.ids;
			var prefix = options.prefiks;
            methods.update(this,dest,ids,prefix);
			$(this).change( function() { methods.update(this,dest,ids,prefix)});

		},
		update: function(org,dest,ids,prefix) {
			var opt = $(org),
				optValues = opt.val() || [],
				byAjax = [] ;
			if (!$.isArray(optValues)) optValues = jQuery.makeArray(optValues);
			if ( typeof  oldValues !== "undefined") {
				//remove if not in optValues
				$.each(oldValues, function(key, oldValue) {
					if ( ($.inArray( oldValue, optValues )) < 0 ) $("#"+prefix+"group"+oldValue).remove();
				});
			}
			//push in 'byAjax' values and do it in ajax
			$.each(optValues, function(optkey, optValue) {
				if( opt.data( 'd'+optValue) === undefined ) byAjax.push( optValue );
			});

			if (byAjax.length >0) {
				$.getJSON('index.php?option=com_virtuemart&view=state&format=json&virtuemart_country_id=' + byAjax,
						function(result){

						var virtuemart_state_id = jQuery('#'+prefix+'virtuemart_state_id');
						var status = virtuemart_state_id.attr('required');
						
						if(status == 'required') {
							if( result[byAjax].length > 0 ) {
								virtuemart_state_id.attr('required','required');
							} else {
								virtuemart_state_id.removeAttr('required');
							}
						}

                        jQuery.each(result, function(key, value) {
							if (value.length >0) {
								opt.data( 'd'+key, value );	
							} else { 
								opt.data( 'd'+key, 0 );		
							}
						});
						methods.addToList(opt,optValues,dest,prefix);
						if ( typeof  ids !== "undefined") {
							var states =  ids.length ? ids.split(',') : [] ;
							$.each(states, function(k,id) {
								$(dest).find('[value='+id+']').attr("selected","selected");
							});
						}
                        jQuery(dest).trigger("liszt:updated"); //in new chosen this is chosen:updated
					}
				);
			} else {
				methods.addToList(opt,optValues,dest,prefix)
				$(dest).trigger("liszt:updated");
			}
			oldValues = optValues ;
			
		},
		addToList: function(opt,values,dest,prefix) {
			$.each(values, function(dataKey, dataValue) { 
				var groupExist = $("#"+prefix+"group"+dataValue+"").size();
				if ( ! groupExist ) {
					var datas = opt.data( 'd'+dataValue );
					if (datas.length >0) {
					var label = opt.find("option[value='"+dataValue+"']").text();
					var group ='<optgroup id="'+prefix+'group'+dataValue+'" label="'+label+'">';
					$.each( datas  , function( key, value) {
						if (value) group +='<option value="'+ value.virtuemart_state_id +'">'+ value.state_name +'</option>';
					});
					group += '</optgroup>';
					$(dest).append(group);
					
					}
				}
			});
		}
	};

	$.fn.vm2front = function( method ) {

		if ( methods[method] ) {
		  return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on Vm2 front jQuery library' );
		}    
	
	};
})(jQuery)

jQuery(document).ready(function(){
    jQuery("body").live("updateVirtueMartCartModule", function(e) {
        jQuery("#vmCartModule").updateVirtueMartCartModule();
    });
});

//<![CDATA[
	Virtuemart.updateChosenDropdownLayout = function() {
		var vm2string = {editImage: 'edit image',select_all_text: 'COM_VIRTUEMART_DRDOWN_SELALL',select_some_options_text: 'COM_VIRTUEMART_DRDOWN_AVA2ALL'};
		jQuery(function($) {
			$(".vm-chzn-select").chosen({enable_select_all: true,select_all_text : vm2string.select_all_text,select_some_options_text:vm2string.select_some_options_text,disable_search_threshold: 5});
		});
	}
	Virtuemart.updateChosenDropdownLayout();
//]]>
				
//<![CDATA[
		jQuery( function($) {
			$("#virtuemart_country_id").vm2front("list",{dest : "#virtuemart_state_id",ids : "48",prefiks : ""});
		});
//]]>
		
//<![CDATA[
		jQuery( function($) {
			$("#shipto_virtuemart_country_id").vm2front("list",{dest : "#shipto_virtuemart_state_id",ids : "",prefiks : "shipto_"});
		});
//]]>
		
jQuery(document).ready(function(){
    jQuery("body").live("updateVirtueMartCartModule", function(e) {
        jQuery("#vmCartModule").updateVirtueMartCartModule();
    });
});
