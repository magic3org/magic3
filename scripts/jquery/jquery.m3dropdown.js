/**
 * Magic3管理画面用ドロップダウンメニューjqueryプラグイン
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
/*
 * jQuery dropdown: A simple dropdown plugin
 *
 * Copyright 2013 Cory LaViska for A Beautiful Site, LLC. (http://abeautifulsite.net/)
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
*/
//if (jQuery) (function ($) {
;(function($){
    $.extend($.fn, {
        dropdown: function (method, data) {

            switch (method) {
                case 'show':
                    show(null, $(this));
                    return $(this);
                case 'hide':
                    hide();
                    return $(this);
                case 'attach':
                    return $(this).attr('data-dropdown', data);
                case 'detach':
                    hide();
                    return $(this).removeAttr('data-dropdown');
                case 'disable':
                    return $(this).addClass('m3dropdown-disabled');
                case 'enable':
                    hide();
                    return $(this).removeClass('m3dropdown-disabled');
            }

        }
    });

    function show(event, object) {

        var trigger = event ? $(this) : object,
			dropdown = $(trigger.attr('data-dropdown')),
			isOpen = trigger.hasClass('m3dropdown-open');

        // In some cases we don't want to show it
        if (event) {
            if ($(event.target).hasClass('m3dropdown-ignore')) return;

            event.preventDefault();
            event.stopPropagation();
        } else {
            if (trigger !== object.target && $(object.target).hasClass('m3dropdown-ignore')) return;
        }
        hide();

        if (isOpen || trigger.hasClass('m3dropdown-disabled')) return;

        // Show it
        trigger.addClass('m3dropdown-open');
        dropdown
			.data('m3dropdown-trigger', trigger)
			.show();

        // Position it
        position();

        // Trigger the show callback
        dropdown
			.trigger('show', {
				dropdown: dropdown,
				trigger: trigger
			});

    }

    function hide(event) {

        // In some cases we don't hide them
        var targetGroup = event ? $(event.target).parents().addBack() : null;

        // Are we clicking anywhere in a dropdown?
        if (targetGroup && targetGroup.is('.m3dropdown')) {
            // Is it a dropdown menu?
            if (targetGroup.is('.m3dropdown-menu')) {
                // Did we click on an option? If so close it.
                if (!targetGroup.is('A')) return;
            } else {
                // Nope, it's a panel. Leave it open.
                return;
            }
        }

        // Hide any dropdown that may be showing
        $(document).find('.m3dropdown:visible').each(function () {
            var dropdown = $(this);
            dropdown
				.hide()
				.removeData('m3dropdown-trigger')
				.trigger('hide', { dropdown: dropdown });
        });

        // Remove all dropdown-open classes
        $(document).find('.m3dropdown-open').removeClass('m3dropdown-open');

    }

    function position() {

        var dropdown = $('.m3dropdown:visible').eq(0),
			trigger = dropdown.data('m3dropdown-trigger'),
			hOffset = trigger ? parseInt(trigger.attr('data-horizontal-offset') || 0, 10) : null,
			vOffset = trigger ? parseInt(trigger.attr('data-vertical-offset') || 0, 10) : null;

        if (dropdown.length === 0 || !trigger) return;

        // Position the dropdown relative-to-parent...
        if (dropdown.hasClass('m3dropdown-relative')) {
            dropdown.css({
                left: dropdown.hasClass('m3dropdown-anchor-right') ?
					trigger.position().left - (dropdown.outerWidth(true) - trigger.outerWidth(true)) - parseInt(trigger.css('margin-right'), 10) + hOffset :
					trigger.position().left + parseInt(trigger.css('margin-left'), 10) + hOffset,
                top: trigger.position().top + trigger.outerHeight(true) - parseInt(trigger.css('margin-top'), 10) + vOffset
            });
        } else {
            // ...or relative to document
            dropdown.css({
                left: dropdown.hasClass('m3dropdown-anchor-right') ?
					trigger.offset().left - (dropdown.outerWidth() - trigger.outerWidth()) + hOffset : trigger.offset().left + hOffset,
                top: trigger.offset().top + trigger.outerHeight() + vOffset
            });
        }
    }

    $(document).on('click.dropdown', '[data-dropdown]', show);
    $(document).on('click.dropdown', hide);
    $(window).on('resize', position);

})(jQuery);
