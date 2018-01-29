(function (jQuery, $) {
var PREVIEW = true;

/* global define */
/* exported productsGridEqualHeight, initSlider */

(function ($) {
    'use strict';

    $.fn.equalImageHeight = function () {
        return this.each(function() {
            var maxHeight = 0;

            $(this).children('a').children('img').each(function(index, child) {
                var h = $(child).height();
                maxHeight = h > maxHeight ? h : maxHeight;
                $(child).css('height', ''); // clears previous value
            });

            $(this).children('a').each(function(index, child) {
                $(child).height(maxHeight);
            });

        });
    };

    $.fn.equalColumnsHeight = function () {
        function off() {
            /* jshint validthis: true */
            this.onload = null;
            this.onerror = null;
            this.onabort = null;
        }

        function on(dfd) {
            /* jshint validthis: true */
            off.bind(this)();
            dfd.resolve();
        }

        return this.each(function() {
            var loadPromises = [];

            $(this).find('img').each(function () {
                if (this.complete) return;
                var deferred = $.Deferred();
                this.onload = on.bind(this, deferred);
                this.onerror = on.bind(this, deferred);
                this.onabort = on.bind(this, deferred);
                loadPromises.push(deferred.promise());
            });

            $.when.apply($, loadPromises).done((function () {
                var cols =  $(this).children('[class*="col-"]').children('[class*="bd-layoutcolumn-"]').css('height', '');
                var indexesForEqual = [];
                var colsWidth = 0;
                var containerWidth = parseInt($(this).css('width'), 10);
                $(cols).each((function (key, column) {
                    colsWidth += parseInt($(column).parent().css('width'), 10);
                    if ((containerWidth + cols.length) >= colsWidth) { // col.length fixes width round in FF
                        indexesForEqual.push(key);
                    }
                }).bind(this));

                var maxHeight = 0;
                indexesForEqual.forEach(function (index) {
                    if (maxHeight < parseInt($(cols[index]).parent().css('height'), 10)) {
                        maxHeight = parseInt($(cols[index]).parent().css('height'), 10);
                    }
                });

                indexesForEqual.forEach(function (index) {
                    $(cols[index]).css('height', maxHeight);
                });
            }).bind(this));
        });
    };

    $(function(){
        $('.bd-row-auto-height').equalColumnsHeight();
        var timeout;
        $(window).resize(function(e, param) {
            clearTimeout(timeout);
            if (param && param.force) {
                $('.bd-row-auto-height').equalColumnsHeight();
            } else {
                timeout = setTimeout(function () { $('.bd-row-auto-height').equalColumnsHeight(); }, 100);
            }
        });
    });
})(jQuery);

// IE10+ flex fix
if (1-'\0') {

    var fixHeight = function fixHeight() {
        jQuery('.bd-row-flex > [class*="col-"] > [class*="bd-layoutcolumn-"] > .bd-vertical-align-wrapper, ' +
                '[class*="bd-layoutitemsbox-"].bd-flex-wide').each(function () {

            var content = jQuery(this);
            var wrapper = content.children('.bd-fix-flex-height');
            if (!wrapper.length) {
                content.wrapInner('<div class="bd-fix-flex-height clearfix"></div>');
            }
            var height = wrapper.outerHeight(true);
            content.removeAttr('style');
            content.css({
                '-ms-flex-preferred-size': height + 'px',
                'flex-basis': height + 'px'
            });
        });

        setTimeout(fixHeight, 500);
    };

    var fixMinHeight = function () {
        jQuery('.bd-stretch-inner').wrap('<div class="bd-flex-vertical"></div>');
    };

    jQuery(fixHeight);
    jQuery(fixMinHeight);
}

/*!
 * jQuery Cookie Plugin v1.4.0
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    var pluses = /\+/g;

    function encode(s) {
        return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
        return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
        return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }

        try {
            s = decodeURIComponent(s.replace(pluses, ' '));
        } catch(e) {
            return;
        }

        try {
            return config.json ? JSON.parse(s) : s;
        } catch(e) {}
    }

    function read(s, converter) {
        var value = config.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }

    var config = $.cookie = function (key, value, options) {

        // Write
        if (value !== undefined && !$.isFunction(value)) {
            options = $.extend({}, config.defaults, options);

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            return (document.cookie = [
                encode(key), '=', stringifyCookieValue(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '',
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        var result = key ? undefined : {};
        var cookies = document.cookie ? document.cookie.split('; ') : [];

        for (var i = 0, l = cookies.length; i < l; i++) {
            var parts = cookies[i].split('=');
            var name = decode(parts.shift());
            var cookie = parts.join('=');

            if (key && key === name) {
                result = read(cookie, value);
                break;
            }

            if (!key && (cookie = read(cookie)) !== undefined) {
                result[name] = cookie;
            }
        }

        return result;
    };

    config.defaults = {};

    $.removeCookie = function (key, options) {
        if ($.cookie(key) !== undefined) {
            $.cookie(key, '', $.extend({}, options, { expires: -1 }));
            return true;
        }
        return false;
    };

}));

window.initSlider = function initSlider(selector, leftButtonSelector, rightButtonSelector, navigatorSelector, indicatorsSelector, carouselInterval, carouselPause, carouselWrap, carouselRideOnStart) {
    'use strict';
    jQuery(selector + '.carousel.slide .carousel-inner > .item:first-child').addClass('active');

    function setSliderInterval() {
        jQuery(selector + '.carousel.slide').carousel({interval: carouselInterval, pause: carouselPause, wrap: carouselWrap});
        if (!carouselRideOnStart) {
            jQuery(selector + '.carousel.slide').carousel('pause');
    }
    }

    /* 'active' must be always specified, otherwise slider would not be visible */
    jQuery(selector + '.carousel.slide .' + leftButtonSelector + ' a' + navigatorSelector).attr('href', '#');
    jQuery(selector + '.carousel.slide .' + leftButtonSelector + ' a' + navigatorSelector).click(function() {
        setSliderInterval();
        jQuery(selector + '.carousel.slide').carousel('prev');
        return false;
    });

    jQuery(selector + '.carousel.slide .' + rightButtonSelector + ' a' + navigatorSelector).attr('href', '#');
    jQuery(selector + '.carousel.slide .' + rightButtonSelector + ' a' + navigatorSelector).click(function() {
        setSliderInterval();
        jQuery(selector + '.carousel.slide').carousel('next');
        return false;
    });

    jQuery(selector + '.carousel.slide').on('slid.bs.carousel', function () {
        var indicators = jQuery(indicatorsSelector, this);
        indicators.find('.active').removeClass('active');
        var activeSlide = jQuery(this).find('.item.active');
        var activeIndex = activeSlide.parent().children().index(activeSlide);
        var activeItem = indicators.children()[activeIndex];
        jQuery(activeItem).children('a').addClass('active');
    });

    setSliderInterval();
};

jQuery(function ($) {
    'use strict';

    $('.collapse-button').each(function () {
        var button = $(this);
        var collapse = button.siblings('.collapse');

        collapse.on('show.bs.collapse', function () {
            if (button.parent().css('position') === 'absolute') {
                var right = collapse.width() - button.width();
                if (button.hasClass('bd-collapse-right')) {
                    $(this).css({
                        'position': 'relative',
                        'right': right
                    });
                } else {
                    $(this).css({
                        'position': '',
                        'right': ''
                    });
                }
            }
        });
    });
});

(function SeparatedGrid($) {
    'use strict';
    var row = [],
        getOffset = function getOffset(el) {
            var isInline = false;
            el.css('position', 'relative');
            if (el.css('display') === 'inline') {
                el.css('display', 'inline-block');
                isInline = true;
            }
            var offset = el.position().top;
            if (isInline) {
                el.css('display', 'inline');
            }
            return offset;
        },
        getCollapsedMargin = function getCollapsedMargin(el) {
            if (el.css('display') === 'block') {
                var m0 = parseFloat(el.css('margin-top'));
                if (m0 > 0) {
                    var p = el.prev();
                    var prop = 'margin-bottom';
                    if (p.length < 1) {
                        p = el.parent();
                        prop = 'margin-top';
                    }
                    if (p.length > 0 && p.css('display') === 'block') {
                        var m = parseFloat(p.css(prop));
                        if (m > 0) {
                            return Math.min(m0, m);
                        }
                    }
                }
            }
            return 0;
        },
        classRE = new RegExp('.*(bd-\\S+[-\\d]*).*'),
        childFilter = function childFilter() {
            return classRE.test(this.className);
        },
        calcOrder = function calcOrder(items) {
            var roots = items;
            while (roots.eq(0).children().length === 1) {
                roots = roots.children();
            }
            var childrenClasses = [];
            var childrenWeights = {};
            var getNextWeight = function getNextWeight(children, i, l) {
                for (var j = i + 1; j < l; j++) {
                    var cls = children[j].className.replace(classRE, '$1');
                    if (childrenClasses.indexOf(cls) !== -1) {
                        return childrenWeights[cls];
                    }
                }
                return 100; //%
            };
            roots.each(function calcWeight(i, root) {
                var children = $(root).children(childFilter);
                var previousWeight = 0;
                for (var c = 0, l = children.length; c < l; c++) {
                    var cls = children[c].className.replace(classRE, '$1');
					if (!cls || cls.length < 1)
					{
						continue;
					}
                    if (childrenClasses.indexOf(cls) === -1) {
                        var nextWeight = getNextWeight(children, c, l);
                        childrenWeights[cls] = previousWeight + (nextWeight - previousWeight) / 10; //~max unique child
                        childrenClasses.push(cls);
                    }
                    previousWeight = childrenWeights[cls];
                }
            });
            childrenClasses.sort(function sortWeight(a, b) {
                return childrenWeights[a] > childrenWeights[b];
            });
            return childrenClasses;
        };
    var calcRow = function calcRow(helpNodes, last, order) {

        $(row).css({'overflow': 'visible', 'height': 'auto'}).toggleClass('last-row', last);

        if (row.length > 0) {
            var roots = $(row);
            roots.removeClass('last-col').last().addClass('last-col');
            while (roots.eq(0).children().length === 1) {
                roots = roots.children();
            }

            var createHelpNode = function createHelpNode(fix){
                var helpNode = document.createElement('div');
                helpNode.setAttribute('style', 'height:' + fix + 'px');
                helpNode.className = 'bd-empty-grid-item';
                helpNodes.push(helpNode);
                return helpNode;
            };
            var cls = '';
            var maxOffset = 0;
            var calcMaxOffsets = function calcMaxOffsets(i, root) {
                var el = $(root).children('.' + cls + ':visible:first');
                if (el.length < 1 || el.css('position') === 'absolute') {
                    return;
                }
                var offset = getOffset(el);
                if (offset > maxOffset) {
                    maxOffset = offset;
                }
            };
            var setMaxOffsets = function setMaxOffsets(i, root) {
                var el = $(root).children('.' + cls + ':visible:first');
                if (el.length < 1 || el.css('position') === 'absolute') {
                    return;
                }
                var offset = getOffset(el);
                var fix = maxOffset - offset - getCollapsedMargin(el);
                if (fix > 0) {
                    el.before(createHelpNode(fix));
                }
            };
            for (var c = 0; c < order.length; c++) {
                maxOffset = 0;
                cls = order[c];
                roots.each(calcMaxOffsets);
                maxOffset = Math.ceil(maxOffset);
                roots.each(setMaxOffsets);
            }
            var hMax = 0;
            $.each(roots, function calcMaxHeight(i, e) {
                var h = $(e).outerHeight();
                if (hMax < h) {
                    hMax = h;
                }
            });
            hMax = Math.ceil(hMax);
            $.each(roots, function setMaxHeight(i, e) {
                var el = $(e);
                var fix = hMax - el.outerHeight();
                if (fix > 0) {
                    el.append(createHelpNode(fix));
                }
            });
        }
        row = [];
    };
    var itemsRE = new RegExp('.*(separated-item[^\\s]+).*'),
        resize =  function resize() {
            var grid = $('.separated-grid');
            grid.each(function eachGrid(i, gridElement) {
                var g = $(gridElement);
                if (!g.is(':visible')) {
                    return;
                }
                if (!gridElement._item || !gridElement._item.length || !gridElement._item.is(':visible')){
                    gridElement._item = g.find('div[class*=separated-item]:visible:first');
                    if (!gridElement._item.length){
                        return;
                    }
                    gridElement._items =  g.find(
                        'div.' + gridElement._item.attr('class').replace(itemsRE, '$1')
                    ).filter(function () {
                            return $(this).parents('.separated-grid')[0] === gridElement;
                    });
                }
                var items = gridElement._items;
                if (!items.length){
                    return;
                }
                var h = 0;
                for (var k = 0; k < items.length; k++) {
                    var el = $(items[k]);
                    var _h = el.height();
                    if (el.is('.first-col')) {
                        h = _h;
                    }
                    if (h !== _h) {
                        gridElement._height = 0;
                    }
                }


                if (g.innerHeight() === gridElement._height && g.innerWidth() === gridElement._width) {
                    return;
                }

                var windowScrollTop = $(window).scrollTop();
                items.css({'overflow': 'hidden', 'height': '10px'}).removeClass('last-row');
                if (gridElement._helpNodes) {
                    $(gridElement._helpNodes).remove();
                }
                gridElement._helpNodes = [];
                var firstLeft = items.position().left;
                var order = calcOrder(items);
                var notDisplayed = [];
                var lastItem = null;
                items.each(function eachItems(i, gridItem) {
                    var item = $(gridItem);
                    var p = item;
                    do {
                        if (p.css('display') === 'none') {
                            p.data('style', p.attr('style')).css('display', 'block');
                            notDisplayed.push(p[0]);
                        }
                        p = p.parent();

                    } while (p.length > 0 && p[0] !== gridElement && !item.is(':visible'));
                    var first = firstLeft >= item.position().left;
                    if (first && row.length > 0) {
                        calcRow(gridElement._helpNodes, lastItem && lastItem.parentNode !== gridItem.parentNode, order);
                    }
                    row.push(gridItem);
                    item.toggleClass('first-col', first);
                    if (i === items.length - 1) {
                        calcRow(gridElement._helpNodes, true, order);
                    }
                    lastItem = gridItem;
                });
                $(notDisplayed).each(function eachHidden(i, e) {
                    var el = $(e);
                    var css = el.data('style');
                    el.removeData('style');
                    if ('undefined' !== typeof css) {
                        el.attr('style', css);
                    } else {
                        el.removeAttr('style');
                    }
                });
                gridElement._width = g.innerWidth();
                gridElement._height = g.innerHeight();
                $(window).scrollTop(windowScrollTop);
                $(window).off('resize', lazy);
                $(window).resize();
                $(window).on('resize', lazy);
            });
        },
        timeoutLazy,
        lazy = function lazy(e, param){
            clearTimeout(timeoutLazy);
            if (param && param.force) {
                resize();
            } else {
                timeoutLazy = setTimeout(resize, 100);
            }
        },
        interval =function interval(){
            lazy();
            setTimeout(interval, 1000);
        };
    $(window).resize(lazy);
    $(interval);
})(jQuery);

(function ($) {
    'use strict';
    $(document).ready(function () {
        try {
            if ("undefined" !== typeof parent.AppController) return;
            var controls = $('[data-autoplay=true]');
            $(controls).each(function (index, item) {
                item.src = item.src + "&autoplay=1";
            });
        } catch (e) {}
    });
})(jQuery);

jQuery(function ($) {
    'use strict';

    $(document).on('click', '[data-responsive-menu] li > a:not([data-toggle="collapse"])', function responsiveClick(e) {
        var itemLink = $(this);
        var menu = itemLink.closest('[data-responsive-menu]');
        var responsiveBtn = menu.find('.collapse-button');
        var responsiveLevels = menu.data('responsiveLevels');

        if (responsiveBtn.length && !responsiveBtn.is(':visible') ||
                responsiveLevels !== 'expand on click' && responsiveLevels !== '' ||
                !menu.data('responsiveMenu') ||
                menu.is('[class*="bd-vmenu-"]') && $('body').width() > 767) {
            return true;
        }

        var submenu = itemLink.siblings();
        if (!submenu.length) return true;
        if (submenu.css('visibility') === 'visible') {
            submenu.removeClass('show');
            submenu.find('.show').removeClass('show');
            itemLink.removeClass('active');
        } else {
            itemLink
                .closest('li')
                .siblings('li')
                .find('ul').parent()
                .removeClass('show');
            itemLink
                .closest('li')
                .siblings('li')
                .children('a')
                .removeClass('active');
            submenu.addClass('show');
            itemLink.addClass('active');
        }
        e.preventDefault();
    });
});

jQuery(function ($) {
    'use strict';

    $('body')
        .on('click.themler', '.bd-overSlide[data-url] a, .bd-slide[data-url] a', function (e) {
            e.stopPropagation();
        })
        .on('click.themler', '.bd-overSlide[data-url], .bd-slide[data-url]', function () {
            var elem = $(this),
                url = elem.data('url'),
                target = elem.data('target');
            window.open(url, target);
        });
});

jQuery(function ($) {
    'use strict';
    var leftClass = 'bd-popup-left';
    var rightClass = 'bd-popup-right';

    $(document).on('mouseenter touchstart', 'ul.nav > li, .nav ul > li', function calcSubmenuDirection() {
        var popup = $(this).children('[class$="-popup"], [class*="-popup "]');
        if (popup.length) {
            popup.removeClass(leftClass + ' ' + rightClass);
            var dir = '';
            if (popup.parents('.' + leftClass).length) {
                dir = leftClass;
            } else if (popup.parents('.' + rightClass).length) {
                dir = rightClass;
            }
            if (dir) {
                popup.addClass(dir);
            } else {
                var left = popup.offset().left;
                var width = popup.outerWidth();
                if (left < 0) {
                    popup.addClass(rightClass);
                } else if (left + width > $(window).width()) {
                    popup.addClass(leftClass);
                }
            }
        }
    });
});

jQuery(function ($) {
    'use strict';

    window.tabCollapseResize = function () {
        $('.tabbable').each(function () {
            var tabbable = $(this);
            var tabMenu = tabbable.children('.nav-tabs');
            var tabs = tabMenu.children('li');
            var tabContent = tabbable.children('.tab-content');
            var panels = tabContent.find('.tab-pane');

            if (!tabs.filter('.active').length) {
                tabs.first().addClass('active');
                panels.removeClass('active').first().addClass('active');
            }

            if (!tabbable.data('responsive')) {
                if (tabContent.children('.accordion').length) {
                    tabContent.children('.accordion').children().first().unwrap();
                }
                tabContent.find('.accordion-item').remove();
                panels.each(function () {
                    var wrapper = $(this).children('.accordion-wrap');
                    if (wrapper.children().length) {
                        wrapper.children().first().unwrap();
                    } else {
                        wrapper.remove();
                    }
                });
                return;
            }

            var cls = tabMenu.siblings('.accordion').children('.accordion-content').attr('class');
            var wrapper = tabContent.find('.accordion-wrap');
            if (wrapper.length) {
                tabContent.find('.accordion-wrap').toggleClass(cls, tabContent.find('.accordion-item:visible').length > 0);
                return;
            }

            var accordion = tabbable.children('.accordion');

            accordion.show();
            var accordionTpl = accordion.clone();
            accordion.hide();

            var itemTpl = accordion.find('.accordion-item').clone();
            var contentTpl = accordion.find('.accordion-content').clone();
            accordionTpl.empty();

            tabs.each(function () {
                var tab = $(this);
                var tablink = tab.find('[data-toggle="tab"]');
                var currentId = tablink.attr('href');
                var panel = panels.filter(currentId);

                var collapseLink = $('<a></a>');
                collapseLink.html(tablink.html());
                collapseLink.attr('data-toggle', 'collapse');
                collapseLink.attr('data-target', currentId);

                panel.before(itemTpl.clone().append(collapseLink));
                panel.wrapInner(contentTpl.clone().addClass('accordion-wrap').toggleClass(cls, collapseLink.is(':visible')));

                panel.addClass('collapse');
                if (panel.is('.active')) {
                    panel.addClass('in');
                    collapseLink.addClass('active');
                }

                /* Collapse */

                panel.on('show.bs.collapse', function () {
                    var actives = panels.filter('.in');
                    panels.filter('.collapsing:not(.active)').addClass('bd-collapsing');
                    if (actives && actives.length) {
                        var activesData = actives.data('bs.collapse');
                        if (!activesData || !activesData.transitioning) {
                            actives.collapse('hide');
                            if (!activesData) actives.data('bs.collapse', null);
                        }
                    }
                    panel.css('display', 'block');

                    collapseLink.addClass('active');
                });

                panel.on('shown.bs.collapse', function () {
                    tab.addClass('active');
                    panel.addClass('active');

                    panel.css('display', '');
                    panel.filter('.bd-collapsing').removeClass('bd-collapsing').collapse('hide');
                });

                panel.on('hide.bs.collapse', function () {
                    collapseLink.removeClass('active');
                });

                panel.on('hidden.bs.collapse', function () {
                    tab.removeClass('active');
                    panel.removeClass('active');
                    panel.css('height', '');
                });

                /* Tabs */

                tablink.on('show.bs.tab', function () {
                    panels.removeClass('in');
                    tabContent.find('.accordion > .accordion-item > a').removeClass('active');

                    panel.css('height', '');
                    panel.addClass('in');
                    collapseLink.addClass('active');
                });
            });

            tabContent.wrapInner(accordionTpl);
        });
    };

    var resizeTimeout;
    $(window).on('resize', function (e, param) {
        clearTimeout(resizeTimeout);
        if (param && param.force) {
            window.tabCollapseResize();
        } else {
            resizeTimeout = setTimeout(window.tabCollapseResize, 25);
        }
    });

    window.tabCollapseResize();
});

jQuery(function ($) {
    'use strict';

    var resizeHandler = function () {
        $('.carousel.adjust-slides').each(function () {
            var inner = $(this).find('.carousel-inner'),
                items = inner.children('.item').addClass('clearfix').css('width', '100%');
            var maxH = 0;
            if (items.length > 1) {
                var windowScrollTop = $(window).scrollTop();
                items.css('min-height', '0').each(function(){
                    maxH = Math.max(maxH, parseFloat(getComputedStyle(this).height));
                }).css('min-height', maxH);
                inner.css('height', maxH);
                if ($(window).scrollTop() !== windowScrollTop){
                    $(window).scrollTop(windowScrollTop);
                }
            }
        });
        setTimeout(resizeHandler, 100);
    };
    resizeHandler();
});



jQuery(function ($) {
    'use strict';
    var panels = $('.bd-accordion .bd-container-78').parent();
    panels.on('show.bs.collapse', function () {
        var actives = panels.filter('.in');

        $(this).prev().children('a').addClass('active');
        actives.prev().children('a').removeClass('active');

        if (actives && actives.length) {
            var hasData = actives.data('bs.collapse');
            if (!hasData || !hasData.transitioning) {
                actives.collapse('hide');
                if (!hasData) actives.data('bs.collapse', null);
            }
        }
    });
    panels.on('hidden.bs.collapse', function () {
        $(this).prev().children('a').removeClass('active');
    });
});

(function ($) {
    window.themeVirtuemart = {};

    window.themeVirtuemart.addToCart = function (product) {

        var buyButtons = $('.add_to_cart_button');
        if (product) {
            buyButtons = product.find('.add_to_cart_button');
        }
        buyButtons.filter(function() {
            return '#' === $(this).attr('href');
        }).click(function (e) {
                e.preventDefault();
                var link = $(this),
                    formClone = $(this).parent('.product').clone(),
                    vmsiteurl = link.attr('data-vmsiteurl'),
                    vmlang = link.attr('data-vmlang'),
                    success = link.attr('data-vmsuccessmsg'),
                    carts = $('div[data-cart-position]'),
                    cartCustomFields = $('.product-field[data-cart-attribute="1"] *[name]'),
                    cloneCartCustomFields = cartCustomFields.clone(),
                    cart, position, style, id, url, datas;

                if (formClone.length) {
                    cartCustomFields.each(function(i) {
                        var element = this;
                        cloneCartCustomFields.eq(i).val($(element).val());
                    });
                    formClone.append(cloneCartCustomFields);
                    datas = formClone.serialize();
                    url = vmsiteurl + 'index.php?option=com_virtuemart&nosef=1&view=cart&task=addJS&format=json' + vmlang;
                    $.getJSON(url, datas, function(datas, textStatus) {
                        link.html(success);
                        if (carts.length > 0) {
                            carts.each(function () {
                                position = $(this).attr('data-cart-position') || '';
                                style = $(this).attr('data-style') || '';
                                id = $(this).attr('data-id') || '';
                                cart = $(this);
                                if ('' !== position) {
                                    url = vmsiteurl + 'index.php';
                                    (function(url, style, cart ) {
                                        $.ajax({
                                            url: url,
                                            type : 'get',
                                            data: {
                                                tmpl : 'modrender',
                                                modulename : 'mod_virtuemart_cart',
                                                modulestyle : style,
                                                moduleid : id,
                                                is_preview : PREVIEW ? 'on' : 'off'
                                            },
                                            dataType: 'html',
                                            success: function (data) {
                                                if (data)
                                                    cart.replaceWith(data);
                                            },
                                            error: function (xhr, status) {}
                                        });
                                    })(url, style, cart);
                                }
                            });
                        }
                    });
                }
                return false;
            });
    }

    window.themeVirtuemart.setProductTypeFacade = function(event) {
        window.themeVirtuemart.setProductType(event.data.productItem);
    }

    window.themeVirtuemart.setProductType = function(product) {
        'use strict';
        var prices = product.find('.product-prices'),
            form = product.find('form.product'),
            virtuemart_product_id = product.find('input[name="virtuemart_product_id[]"]');

        if (!prices.length) {
            return false;
        }

        var formClone = form.clone(),
            cartCustomFields = $('.product-field[data-cart-attribute="1"] *[name]', product),
            cloneCartCustomFields = cartCustomFields.clone();

        cartCustomFields.each(function(i) {
            var element = this;
            cloneCartCustomFields.eq(i).val($(element).val());
        });
        formClone.append(cloneCartCustomFields);
        var datas = formClone.serialize();
        datas = datas.replace("&view=cart", "");

        prices.fadeTo("fast", 0.75);
        $.ajax({
            type: "POST",
            cache: false,
            dataType: "json",
            url: window.vmSiteurl + "index.php?&option=com_virtuemart&view=productdetails&task=recalculate&format=json&nosef=1" +
                window.vmLang + (virtuemart_product_id.length ? 'virtuemart_product_id=' + virtuemart_product_id.val() : ''),
            data: datas
        }).done(
            function (data, textStatus) {
                prices.fadeTo("fast", 1);
                // refresh price
                for (var key in data) {
                    var value = data[key];
                    if (key !== 'messages' && value !== 0) {
                        prices.find("span."+key).show().html(value);
                    }
                }
            }
        );

        return false; // prevent reload
    };

    window.themeVirtuemart.product = function(items) {
        'use strict';
        items.each(function() {
            var productItem     = $(this),
                quantityForm    = productItem.find('input[name="quantity[]"]'),
                plus            = productItem.find('.quantity-plus'),
                minus           = productItem.find('.quantity-minus'),
                quantityInput   = productItem.find('.quantity-input'),
                form            = productItem.find('form.product');

            productItem.children().eq(0).attr('data-updating-content', true);

            var Ste = parseInt(quantityForm.val());
            if(isNaN(Ste)){
                Ste = 1;
            }

            plus.click(function() {
                var Qtt = parseInt(quantityInput.val());
                if (!isNaN(Qtt)) {
                    quantityInput.val(Qtt + Ste);
                    quantityForm.val(Qtt + Ste);
                    window.themeVirtuemart.setProductType(productItem);
                }

            });

            minus.click(function() {
                var Qtt = parseInt(quantityInput.val());
                if (!isNaN(Qtt) && Qtt>Ste) {
                    quantityInput.val(Qtt - Ste);
                    quantityForm.val(Qtt - Ste);
                } else {
                    quantityInput.val(Ste);
                    quantityForm.val(Ste);
                }
                window.themeVirtuemart.setProductType(productItem);
            });

            form.each(function() {
                var form = $(this),
                    select = form.find('select:not(.no-vm-bind)'),
                    selectOutForm = $('.product-field[data-cart-attribute="1"] select:not(.no-vm-bind)', productItem),
                    radio = form.find('input:radio:not(.no-vm-bind)'),
                    radioOutForm = $('.product-field[data-cart-attribute="1"] input:radio:not(.no-vm-bind)', productItem),
                    virtuemart_product_id = form.find('input[name="virtuemart_product_id[]"]').val();

                $(select).off('change', window.themeVirtuemart.setProductTypeFacade);
                $(select).on('change', {productItem : productItem}, window.themeVirtuemart.setProductTypeFacade);
                $(selectOutForm).off('change', window.themeVirtuemart.setProductTypeFacade);
                $(selectOutForm).on('change', {productItem : productItem}, window.themeVirtuemart.setProductTypeFacade);

                $(radio).off('change', window.themeVirtuemart.setProductTypeFacade);
                $(radio).on('change', {productItem : productItem}, window.themeVirtuemart.setProductTypeFacade);
                $(radioOutForm).off('change', window.themeVirtuemart.setProductTypeFacade);
                $(radioOutForm).on('change', {productItem : productItem}, window.themeVirtuemart.setProductTypeFacade);
            });

        });
    }
})(jQuery);

jQuery(function($) {
    'use strict';
    window.themeVirtuemart.addToCart();
    var items = $('.vm-product-item');
    if (items.length) {
        window.themeVirtuemart.product(items);
        if (1 === items.length) {
            setInterval(function() {
                var item = items;
                var attr = item.children().eq(0).attr('data-updating-content');
                if (typeof attr === 'undefined') {
                    window.themeVirtuemart.addToCart(item);
                    window.themeVirtuemart.product(item);
                }
            }, 350);
        }
    }
});

jQuery(function ($) {
    'use strict';
    if (PREVIEW) {
        var search = $('form[name*="search"]');
        search.submit(function() {
            return false;
        });

        $('#form-login').submit(function() {
            return false;
        });
        var logout = $('#form-login > input[type*="submit"]');
        logout.attr('link-disable', true);

        $('#checkoutForm').submit(function() {
            return false;
        });
        var checkout = $('#checkoutFormSubmit');
        checkout.attr('link-disable', true);

        var removeLinks = $('.removelink').filter(function () {
            if((this.getAttribute('name') + '').indexOf('delete.') === 0) return true;
            else return false;
        });
        removeLinks.attr('link-disable', true);

        var versions = $('.edit.item-page a[title=\'Versions\']');
        versions.attr('link-disable', true);
    }
});


// Fixing conflict Mootools.fx slide with Bootstap Carousel
if ('undefined' !== typeof jQuery && 'undefined' !== typeof MooTools) {
    Element.implement({
        slide: function (how, mode) {
            return this;
        },
        hide: function () {
            return this;
        },
        show: function () {
            return this;
        }
    });
}

})(window._$, window._$);
(function (jQuery, $) {
(function ($) {
    'use strict';

    window.initAffix = function initAffix(selector) {
        $('.bd-affix-fake').prev(':not([data-fix-at-screen])').next().remove();

        $(selector).each(function () {
            var element = $(this),
                offset = {},
                cachedOffset = null;

            element.off('.affix');
            element.removeAttr('style');
            element.removeClass($.fn.affix.Constructor.RESET);
            element.removeData('bs.affix');

            offset.top = function () {
                var hasAffix = element.hasClass('affix');

                if (cachedOffset === null && hasAffix) {
                    element.removeClass('affix');
                }

                if (!hasAffix) {
                    var elTop = element.offset().top,
                        offset = parseInt(element.data('offset'), 10) || 0,
                        clipAtControl = element.data('clipAtControl'),
                        fixAtScreen = element.data('fixAtScreen'),
                        elHeight = element.outerHeight();

                    var ev = $.Event('affix-calc.theme.affix');
                    element.trigger(ev);
                    ev.offset = ev.offset || 0;
                    offset += ev.offset;

                    if (clipAtControl === 'bottom') {
                        elTop += elHeight;
                    }

                    if (fixAtScreen === 'bottom') {
                        elTop += offset;
                        elTop -= $(window).height();
                    }

                    if (fixAtScreen === 'top') {
                        elTop -= offset;
                    }

                    cachedOffset = elTop;
                }

                if (cachedOffset === null && hasAffix) {
                    element.addClass('affix');
                }

                return cachedOffset;
            };

            element.on('affix.bs.affix', function (e) {
                var el = $(this),
                    fake = el.next('.bd-affix-fake');

                if (!fake.is(':visible')) {
                    e.preventDefault();
                    return;
                }

                if (['absolute', 'fixed'].indexOf(el.css('position')) === -1) {
                    fake.css('height', el.outerHeight(true));
                }

                // fix affix position
                var body = $('body');
                var bodyWidth = body.outerWidth() || 1;
                var elWidth = el.outerWidth();
                var elLeft = el.offset().left;
                el.css('width', (el.outerWidth() / bodyWidth * 100) + '%');

                if (bodyWidth / 2 > (elLeft + elWidth / 2)) {
                    el.css('left', (elLeft / bodyWidth * 100) + '%');
                    el.css('right', 'auto');
                } else {
                    el.css('right', ((bodyWidth - elLeft - elWidth) / bodyWidth * 100) + '%');
                    el.css('left', 'auto');
                }

                var offset = parseInt(element.data('offset'), 10) || 0;
                var ev = $.Event('affix-calc.theme.affix');
                el.trigger(ev);
                ev.offset = ev.offset || 0;
                offset += ev.offset;

                if (element.data('fixAtScreen') === 'bottom') {
                    el.css('top', 'auto');
                    el.css('bottom', offset + 'px');
                } else {
                    el.css('top', offset + 'px');
                    el.css('bottom', 'auto');
                }
            });

            element.on('affixed-top.bs.affix', function () {
                $(this).next('.bd-affix-fake').removeAttr('style');
                $(this).removeAttr('style');
            });

            if (!element.next('.bd-affix-fake').length) {
                element.after('<div class="bd-affix-fake"></div>');
            }

            $('body').trigger($.Event('affix-init.theme.affix'), [element]);

            element.affix({
                'offset': offset
            });

            element.affix('checkPosition');
        });
    };

    $(function ($) {
        var affixTimeout;

        $(window).on('resize', function (e, param) {
            clearTimeout(affixTimeout);
            if (param && param.force) {
                window.initAffix('[data-affix]');
            } else {
                affixTimeout = setTimeout(function () {
                    window.initAffix('[data-affix]');
                }, 100);
            }
        });

        window.initAffix('[data-affix]');
    });
})(jQuery);
})(window._$, window._$);
(function (jQuery, $) {
(function($){
    'use strict';
    /*jshint -W004 */
    /**
     * Copyright 2012, Digital Fusion
     * Licensed under the MIT license.
     * http://teamdf.com/jquery-plugins/license/
     *
     * @author Sam Sehnert
     * @desc A small plugin that checks whether elements are within
     *       the user visible viewport of a web browser.
     *       only accounts for vertical position, not horizontal.
     */
    var $w = $(window);
    $.fn.visible = function(partial,hidden,direction){

        if (this.length < 1)
            return;

        var $t        = this.length > 1 ? this.eq(0) : this,
            t         = $t.get(0),
            vpWidth   = $w.width(),
            vpHeight  = $w.height(),
            direction = (direction) ? direction : 'both',
            clientSize = hidden === true ? t.offsetWidth * t.offsetHeight : true;

        if (typeof t.getBoundingClientRect === 'function'){

            // Use this native browser method, if available.
            var rec = t.getBoundingClientRect(),
                tViz = rec.top    >= 0 && rec.top    <  vpHeight,
                bViz = rec.bottom >  0 && rec.bottom <= vpHeight,
                lViz = rec.left   >= 0 && rec.left   <  vpWidth,
                rViz = rec.right  >  0 && rec.right  <= vpWidth,
                vVisible   = partial ? tViz || bViz : tViz && bViz,
                hVisible   = partial ? lViz || rViz : lViz && rViz;

            if(direction === 'both')
                return clientSize && vVisible && hVisible;
            else if(direction === 'vertical')
                return clientSize && vVisible;
            else if(direction === 'horizontal')
                return clientSize && hVisible;
        } else {

            var viewTop         = $w.scrollTop(),
                viewBottom      = viewTop + vpHeight,
                viewLeft        = $w.scrollLeft(),
                viewRight       = viewLeft + vpWidth,
                offset          = $t.offset(),
                _top            = offset.top,
                _bottom         = _top + $t.height(),
                _left           = offset.left,
                _right          = _left + $t.width(),
                compareTop      = partial === true ? _bottom : _top,
                compareBottom   = partial === true ? _top : _bottom,
                compareLeft     = partial === true ? _right : _left,
                compareRight    = partial === true ? _left : _right;

            if(direction === 'both')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop)) && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
            else if(direction === 'vertical')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
            else if(direction === 'horizontal')
                return !!clientSize && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
        }
    };

    $(onLoad);
    $(window).on('scroll', animateOnScroll);

    function onLoad() {
        var hoverEffects = $('.animated[data-animation-event="hover"]');
        if (hoverEffects.length) {
            hoverEffects.each(function () {
                var effect = $(this);
                effect.on('mouseover', animateOnHover(effect));
            });
        }

        subscribeOnSlideEvents('slideout');
        subscribeOnSlideEvents('slidein');

        var slideInEffects = $('.animated[data-animation-event="slidein"]');
        if (slideInEffects.length) {
            var carousels = getCarousels(slideInEffects);

            if (carousels.length) {
                carousels.forEach(function (_class) {
                    var _carousel = $('.' + _class.trim().replace(/\s{2,}/g, ' ').replace(/\s/g, '.'));
                    _carousel.trigger('slid.bs.carousel');
                });
            }
        }

        var onLoadEffects = $('.animated[data-animation-event="onload"]');
        if (onLoadEffects.length) {
            onLoadEffects.each(function () {
                applyAnimation($(this));
            });
        }
        var onLoadIntervalEffects = $('.animated[data-animation-event="onloadinterval"]');
        if (onLoadIntervalEffects.length) {
            onLoadIntervalEffects.each(function () {
                var effect = $(this);
                if (effect.visible(true)) {
                    var animationClass = effect.data('animation-name');
                    if (animationClass) {
                        effect.addClass(animationClass);
                    }
                }

                var duration = isNaN(parseFloat(effect.attr('data-animation-duration'))) ? 0 : parseFloat(effect.attr('data-animation-duration'));
                var delay = isNaN(parseFloat(effect.attr('data-animation-delay'))) ? 0 : parseFloat(effect.attr('data-animation-delay'));
                setInterval(function () {
                    effect.removeClass(animationClass);
                    setTimeout(function (){
                        effect.addClass(animationClass);
                    }, 50);
                }, duration + delay);
            });
        }

    }
    function applyAnimation(effect) {
        if (effect.visible(true)) {
            var animationClass = effect.data('animation-name');
            if (animationClass) {
                effect.addClass(animationClass);
            }

            if (effect.attr('data-animation-infinited') === 'true') {
                effect.addClass('infinite');
            }
        }
    }

    function animateOnScroll() {
        var scrollEffects = $('.animated[data-animation-event="scroll"]');

        if (scrollEffects) {
            scrollEffects.each(function () {
                applyAnimation($(this));
            });
        }
        var scrollLoopEffects = $('.animated[data-animation-event="scrollloop"]');
        if (scrollLoopEffects) {
            scrollLoopEffects.each(function () {
                var effect = $(this);
                if (effect.visible(false)) {
                    effect.removeClass(effect.data('animation-name'));
                }
                applyAnimation(effect);
            });
        }
    }

    function animateOnHover(dom) {
        return (function() {
            var animationClass = dom.attr('data-animation-name');
            if (animationClass && !dom.is('.' + animationClass)) {
                if (dom.attr('data-animation-infinited') === 'false') {
                    var duration = dom.attr('data-animation-duration');
                    setTimeout(function () {
                        dom.removeClass(animationClass);
                    }, isNaN(parseFloat(duration)) ? 1000 : parseFloat(duration));
                } else {
                    dom.addClass('infinite');
                }
                dom.addClass(animationClass);
            }
        });
    }

    function subscribeOnSlideEvents(eventName) {
        var slideEffects = $('.animated[data-animation-event="' + eventName + '"]');
        var carouselEvent = eventName === 'slideout' ? 'slide' : 'slid';
        if (slideEffects.length) {
            var carousels = getCarousels(slideEffects);

            if (carousels.length) {
                carousels.forEach(function (_class) {
                    var _carousel = $('.' + _class.trim().replace(/\s{2,}/g, ' ').replace(/\s/g, '.'));
                    _carousel.on(carouselEvent + '.bs.carousel', eventName === 'slidein' ? animateOnSlideIn(_carousel) : animateOnSlideOut(_carousel));
                    var slidinTargets = _carousel.find('.animated[data-animation-event="' + eventName + '"]');
                    slidinTargets.each(function() {
                        var target = $(this);
                        if (target.attr('data-animation-display') && target.attr('data-animation-display') === 'none') {
                            target.css('display', 'none');
                        }
                    });
                    _carousel.on('slide.bs.carousel', function (event) {
                        var target = $(event.relatedTarget);
                        var effectsInSlider = target.find('.animated[data-animation-event="' + eventName + '"]');
                        if (effectsInSlider.length) {
                            var maxDuration = eventName === 'slideout' ? getMaxDuration(effectsInSlider) : 0;
                            effectsInSlider.each(function () {
                                var effect = $(this);
                                if (needToHide(effect)) {
                                    if (eventName === 'slideout') {
                                        setTimeout(function () {
                                            effect.css('display', '');
                                        }, maxDuration);
                                    }
                                    if (eventName === 'slidein') {
                                        effect.css('display', 'none');
                                    }
                                }
                            });
                        }
                    });
                });
            }
        }
    }



    function animateOnSlideOut(carousel) {
        return (function() {
            var moveSlide = false;
            return function (event) {
                var effects = getActiveSlideEffects(carousel, 'slideout');
                if (effects.length) {
                    if (!moveSlide) {
                        event.isDefaultPrevented = function () {
                            return true;
                        };

                        animateFunc(carousel, 'slideout');

                        var eventDirection = event.direction === 'left' ? 'next' : 'prev';
                        var maxDuration = getMaxDuration(effects);
                        setTimeout(function () {
                            moveSlide = true;
                            carousel.carousel(eventDirection);
                        }, maxDuration);
                    } else {
                        moveSlide = false;
                    }
                }
            };
        })();
    }

    function animateOnSlideIn(carousel) {
        return (function () {
            animateFunc(carousel, 'slidein');
        });
    }

    function animateFunc(carousel, eventName){
        var effects = getActiveSlideEffects(carousel, eventName);
        var maxDuration = getMaxDuration(effects);
        effects.each(function () {
            var effect = $(this);
            var animationClass = effect.attr('data-animation-name');
            if (animationClass) {
                if (effect.attr('data-animation-infinited') === 'false') {
                    if (!effect.is('.' + animationClass)) {
                        setTimeout(function () {
                            effect.removeClass(animationClass);
                        }, maxDuration + 100);
                    }
                } else {
                    effect.addClass('infinite');
                }
                if (needToHide(effect)) {
                    if (eventName === 'slideout') {
                        setTimeout(function () {
                            effect.css('display', 'none');
                        }, maxDuration);
                    }
                    if (eventName === 'slidein') {
                        effect.css('display', '').find('.animated[data-animation-event="slideout"]').css('display','');
                    }
                }
                effect.addClass(animationClass);
            }
        });
    }

    function needToHide(effect) {
        var hide = true;
        var animationName = effect.attr('data-animation-name');
        var visibleAnimations = ['bounce', 'flash', 'pulse', 'rubber', 'band','snake','swing','tada','wobble', 'slideindown' , 'slideinleft' , 'slideinright', 'slideinup',
            'slideoutdown', 'slideoutleft', 'slideoutright', 'slideoutup'];
        hide = visibleAnimations.indexOf(animationName.toLowerCase()) === -1;
        return hide;
    }

    function getCarousels(effects) {
        var carousels = [];
        effects.each(function () {
            var effect = $(this);
            var carousel = $('.carousel').has(effect);
            if (carousel.length && carousels.indexOf(carousel.attr('class')) === -1) {
                carousels.push(carousel.attr('class'));
            }
        });
        return carousels;
    }

    function getMaxDuration(effects) {
        var maxDuration = 0;
        effects.each(function () {
            var effect = $(this);
            var duration = isNaN(parseFloat(effect.attr('data-animation-duration'))) ? 0 : parseFloat(effect.attr('data-animation-duration')),
                delay = isNaN(parseFloat(effect.attr('data-animation-delay'))) ? 0 : parseFloat(effect.attr('data-animation-delay'));
            var animationTime = duration + delay;

            maxDuration = maxDuration < animationTime ? animationTime : maxDuration;
        });
        return maxDuration;
    }

    function getActiveSlideEffects(carousel, event) {
        var activeSlide = carousel.find('.carousel-inner > .active');
        var effects = activeSlide.find('.animated[data-animation-event="' + event + '"]');
        return effects;
    }

})(jQuery);
})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-2 img:not(.no-lightbox)').init();
});
})(window._$, window._$);
(function (jQuery, $) {
(function ($) {
    'use strict';
    $(onLoad);

    var timeout;
    $(window).on('resize', function (event, param) {
        clearTimeout(timeout);
        if (param && param.force) {
            applyImageScalling();
        } else {
            timeout = setTimeout(function () {
                applyImageScalling();
            }, 100);
        }
    });

    function onLoad() {
        $(".bd-imagescaling-animated").each(function () {
            var c = $(this);
            var img = c.find('img');
            if (img.length) {
                scaling(c, img);
                img.bind('load', function () {
                    scaling(c, img);
                });
            }
        });
    }

    function applyImageScalling() {
        $(".bd-imagescaling-animated").each(function () {
            var c = $(this);
            var img = c.find('img');
            if (img.length) {
                scaling(c, img);
            }
        });
    }

    function scaling(c, img) {
        if (!c.length) return;

        var imgSrc = img.attr('src') || '',
            imgClass = img.attr('class') || '';

        var imgWrapper = img.parent('.bd-imagescaling-img');

        if (!imgWrapper.length || imgClass) {
            if (img.parent().is('.bd-imagescaling-img')) {
                img.unwrap();
            }
            imgWrapper = img.wrap('<div class="' + imgClass + ' bd-imagescaling-img"></div>').parent();
            img.removeClass();
        }

        if (imgSrc.indexOf('.') === 0) {
            imgSrc = combineUrl(window.location.href, imgSrc);
        }

        if (imgWrapper.siblings('.bd-parallax-image-wrapper').length === 0) {
            imgWrapper.css('background-image', 'url(' + imgSrc + ')');
        }
    }

    function combineUrl(base, relative) {
        if (!relative){
            return base;
        }
        var stack = base.split("/"),
            parts = relative.split("/");
        stack.pop();

        for (var i = 0; i < parts.length; i++) {
            if (parts[i] === ".")
                continue;
            if (parts[i] === "..")
                stack.pop();
            else
                stack.push(parts[i]);
        }
        return stack.join("/");
    }
})(jQuery);

})(window._$, window._$);
(function (jQuery, $) {

window.ThemeLightbox = (function ($) {
    'use strict';
    return (function ThemeLightbox(selectors) {
        var selector = selectors;
        var images = $(selector);
        var current;
        var close = function () {
            $(".bd-lightbox").remove();
        };
        this.init = function () {

            $(selector).mouseup(function (e) {
                if (e.which && e.which !== 1) {
                    return;
                }
                current = images.index(this);
                var imgContainer = $('.bd-lightbox');
                if (imgContainer.length === 0) {
                    imgContainer = $('<div class="bd-lightbox">').css('line-height', $(window).height() + "px")
                        .appendTo($("body"));
                    var closeBtn = $('<div class="close"><div class="cw"> </div><div class="ccw"> </div><div class="close-alt">&#10007;</div></div>');
                    closeBtn.appendTo(imgContainer);
                    closeBtn.click(close);
                    showArrows();
                    var scrollDelay = 100;
                    var lastScroll = 0;
                    imgContainer.bind('mousewheel DOMMouseScroll', function (e) {
                        var date  =  new Date();
                        if (date.getTime() > lastScroll + scrollDelay) {
                            lastScroll = date.getTime();
                            var orgEvent = window.event || e.originalEvent;
                            var delta = (orgEvent.wheelDelta ? orgEvent.wheelDelta : orgEvent.detail * -1) > 0 ? 1 : -1;
                            move(current + delta);
                        }
                        e.preventDefault();
                    }).mousedown(function (e) {
                            // close on middle button click
                            if (e.which === 2) {
                                close();
                            }
                            e.preventDefault();
                     });
                }
                move(current);
            });
        };

        function move(index) {

            if (index < 0 || index >= images.length) {
                return;
            }

            showError(false);

            current = index;

            $(".bd-lightbox .lightbox-image:not(.active)").remove();

            var active = $(".bd-lightbox .active");
            var target = $('<img class="lightbox-image" alt="" src="' + getFullImgSrc($(images[current])) + '" />').click(function () {
                if ($(this).hasClass("active")) {
                    move(current + 1);
                }
            });

            if (active.length > 0) {
                active.after(target);
            } else {
                $(".bd-lightbox").append(target);
            }

            showArrows();
            showLoader(true);

            $(".bd-lightbox").add(target);

            target.load(function () {
                showLoader(false);
                active.removeClass("active");
                target.addClass("active");
            });

            target.error(function () {
                showLoader(false);
                active.removeClass("active");
                target.addClass("active");
                target.attr("src", $(images[current]).attr("src"));
                target.unbind('error');
            });
        }

        function showArrows() {
            if ($(".bd-lightbox .arrow").length === 0) {
                var topOffset = $(window).height() / 2 - 40;
                $(".bd-lightbox")
                    .append(
                        $('<div class="arrow left"><div class="arrow-t ccw"> </div><div class="arrow-b cw"> </div><div class="arrow-left-alt">&#8592;</div></div>')
                            .css("top", topOffset)
                            .click(function () {
                                move(current - 1);
                            })
                    )
                    .append(
                        $('<div class="arrow right"><div class="arrow-t cw"> </div><div class="arrow-b ccw"> </div><div class="arrow-right-alt">&#8594;</div></div>')
                            .css("top", topOffset)
                            .click(function () {
                                move(current + 1);
                            })
                    );
            }

            if (current === 0) {
                $(".bd-lightbox .arrow.left").addClass("disabled");
            } else {
                $(".bd-lightbox .arrow.left").removeClass("disabled");
            }

            if (current === images.length - 1) {
                $(".bd-lightbox .arrow.right").addClass("disabled");
            } else {
                $(".bd-lightbox .arrow.right").removeClass("disabled");
            }
        }

        function showError(enable) {
            if (enable) {
                $(".bd-lightbox").append($('<div class="lightbox-error">The requested content cannot be loaded.<br/>Please try again later.</div>')
                    .css({ "top": $(window).height() / 2 - 60, "left": $(window).width() / 2 - 170 }));
            } else {
                $(".bd-lightbox .lightbox-error").remove();
            }
        }

        function showLoader(enable) {
            if (!enable) {
                $(".bd-lightbox .loading").remove();
            }
            else {
                $('<div class="loading"> </div>').css({ "top": $(window).height() / 2 - 16, "left": $(window).width() / 2 - 16 }).appendTo($(".bd-lightbox"));
            }
        }

        function getFullImgSrc(image) {
            var largeImage = '';
            var parentLink = image.parent('a');
            if (parentLink.length) {
                parentLink.click(function (e) {
                    e.preventDefault();
                    });
                largeImage = parentLink.attr('href');
            } else {
                var src = image.attr("src");
                var fileName = src.substring(0, src.lastIndexOf('.'));
                var ext = src.substring(src.lastIndexOf('.'));
                largeImage = fileName + "-large" + ext;
            }
            return largeImage;
        }
    });
})(jQuery);


jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-lightbox, .lightbox').init();
});
})(window._$, window._$);
(function (jQuery, $) {
(function ($) {
    'use strict';

    // http://paulirish.com/2011/requestanimationframe-for-smart-animating/
    // http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating
    // requestAnimationFrame polyfill by Erik M?ller. fixes from Paul Irish and Tino Zijdel
    // MIT license

    if (!/Android|BlackBerry|iPad|iPhone|iPod|Windows Phone/i.test(navigator.userAgent || navigator.vendor || window.opera)) {

        (function () {
            var lastTime = 0;
            var vendors = ['ms', 'moz', 'webkit', 'o'];
            for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
                window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
                window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] ||
                    window[vendors[x] + 'CancelRequestAnimationFrame'];
            }

            if (!window.requestAnimationFrame)
                window.requestAnimationFrame = function (callback) {
                    var currTime = new Date().getTime();
                    var timeToCall = Math.max(0, 16 - (currTime - lastTime));
                    var id = window.setTimeout(function () {
                            callback(currTime + timeToCall);
                        },
                        timeToCall);
                    lastTime = currTime + timeToCall;
                    return id;
                };

            if (!window.cancelAnimationFrame)
                window.cancelAnimationFrame = function (id) {
                    clearTimeout(id);
                };
        }());

        var transform = ['transform', 'msTransform', 'webkitTransform', 'mozTransform', 'oTransform'];

        $(function () {
            onLoad();
        });

        var timeout;
        $(window).on('resize', function (e, param) {
            clearTimeout(timeout);
            if (param && param.force) {
                onResize();
            } else {
                timeout = setTimeout(onResize, 100);
            }
        });

        $(window).on('scroll', function () {
            window.requestAnimationFrame(function () {
                onScroll();
            });
        });
    }

    function onLoad() {
        var elements = document.getElementsByClassName('bd-parallax-bg-effect');
        if (elements.length && window._smoothWheelInstance) {
            window._smoothWheelInstance();
        }

        [].forEach.call(elements, function (element) {
            var that = element,
                controlClass = that.getAttribute('data-control-selector').replace(/\./g, ''),
                controls = document.getElementsByClassName(controlClass),
                isSlider = /bd-slider-\d+($|\s)/g.test(controlClass) || getClassName(controls[0]).indexOf('bd-slider') !== -1,
                isColumn = /bd-layoutcolumn-\d+($|\s)/g.test(controlClass);

            var activeDoms = [], wrapperDiv;

            if (isSlider) {
                controls = findByClass(controls[0], 'bd-slide');
                if (controls.length) {
                    [].forEach.call(controls, function (slide) {
                        activeDoms = findTopLevelDoms(slide, 'bd-parallax-image-wrapper', controlClass);
                        if (!activeDoms.length) {
                            slide.style.backgroundImage = 'none';
                            slide.style.backgroundColor = 'transparent';
                            wrapperDiv = document.createElement('div');
                            wrapperDiv.className = 'bd-parallax-image-wrapper';
                            wrapperDiv.innerHTML = '<div class="bd-parallax-image"></div>';
                            slide.insertBefore(wrapperDiv, slide.firstChild);
                        }
                    });
                }
            }
            else if (isColumn) {
                if (controls.length) {
                    activeDoms = findTopLevelDoms(that, 'bd-parallax-image-wrapper', controlClass);
                    if (!activeDoms.length) {
                        var effectClone = that.cloneNode(true);
                        effectClone.innerHTML = '';

                        var columnNode = controls[0].parentNode;
                        $(columnNode).unwrap();
                        columnNode.insertBefore(effectClone, columnNode.firstChild);

                        wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'bd-parallax-image-wrapper';
                        wrapperDiv.innerHTML = '<div class="bd-parallax-image"></div>';

                        effectClone.insertBefore(wrapperDiv, effectClone.firstChild);
                    }
                }
            }
            else {
                if (controls.length) {
                    activeDoms = findTopLevelDoms(that, 'bd-parallax-image-wrapper', controlClass);
                    if (!activeDoms.length) {
                        wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'bd-parallax-image-wrapper';
                        wrapperDiv.innerHTML = '<div class="bd-parallax-image"></div>';
                        that.insertBefore(wrapperDiv, that.firstChild);
                    }
                }
            }

            if (controls.length) {
                [].forEach.call(controls, function (control) {
                    var parallaxWrapper = isColumn ? findByClass(control.parentElement, 'bd-parallax-image-wrapper')[0] : findTopLevelDoms(control, 'bd-parallax-image-wrapper', controlClass)[0],
                        parallaxImg = parallaxWrapper.getElementsByClassName('bd-parallax-image')[0],
                        controlOffset = $(that).offset().top,
                        controlHeight = that.clientHeight,
                        viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

                    if (control.style.backgroundImage === 'none') {
                        control.style.backgroundImage = '';
                    }

                    if (control.style.backgroundColor === 'transparent') {
                        control.style.backgroundColor = '';
                    }

                    var backgroundStyles = getComputedStyle(control);

                    if (backgroundStyles.position === 'static') {
                        control.style.position = 'relative';
                    }

                    if (backgroundStyles.backgroundImage !== 'none' && parallaxImg.style.backgroundImage !== backgroundStyles.backgroundImage) {
                        parallaxImg.style.backgroundImage = backgroundStyles.backgroundImage;
                    }

                    if (backgroundStyles.backgroundColor !== 'transparent' && parallaxImg.style.backgroundColor !== backgroundStyles.backgroundColor) {
                        parallaxImg.style.backgroundColor = backgroundStyles.backgroundColor;
                    }

                    control.style.backgroundImage = 'none';
                    control.style.backgroundColor = 'transparent';
                    parallaxImg.style.backgroundRepeat = backgroundStyles.backgroundRepeat;
                    parallaxImg.style.backgroundPosition = backgroundStyles.backgroundPosition;

                    if (isSlider) {
                        parallaxWrapper.style.setProperty('z-index', '-2', 'important');
                    }

                    if (isColumn) {
                        var containerStyles = getComputedStyle(parallaxWrapper);
                        parallaxImg.style.setProperty('min-width', containerStyles.width, 'important');
                        //parallaxImg.style.setProperty('min-height', Math.min(viewPortHeight, 3 * parseInt(containerStyles.height)) + 'px', 'important');
                    }

                    var positionDifference,
                        controlBottom = controlOffset + controlHeight;

                    if (controlOffset >= viewPortHeight / 2) {
                        //var additionalSpace = controlOffset < viewPortHeight ? (viewPortHeight - controlOffset) / 2 : 0;
                        positionDifference = -viewPortHeight / 2 /*+ additionalSpace*/ + (getCompatibleScrollTop() + viewPortHeight - controlOffset) / 2;
                    }
                    else {
                        positionDifference = /*-controlOffset / 2*/ +(getCompatibleScrollTop() - controlOffset) / 2;
                    }
                    if (getCompatibleScrollTop() + viewPortHeight > controlOffset && getCompatibleScrollTop() < controlBottom) {
                        var transformProperty = getSupportedPropertyName(transform);
                        if (transformProperty) {
                            parallaxImg.style[transformProperty] = 'translate3d(0, ' + positionDifference + 'px, 0)';
                        }
                    }
                });
            }
        });
    }

    function onResize() {
        var elements = document.getElementsByClassName('bd-parallax-bg-effect');
        if (elements.length && window._smoothWheelInstance) {
            window._smoothWheelInstance();
        }

        [].forEach.call(elements, function (element) {
            var that = element,
                controlClass = that.getAttribute('data-control-selector').replace(/\./g, ''),
                controls = document.getElementsByClassName(controlClass),
                isSlider = /bd-slider-\d+($|\s)/g.test(controlClass) || getClassName(controls[0]).indexOf('bd-slider') !== -1,
                isColumn = /bd-layoutcolumn-\d+($|\s)/g.test(controlClass);

            var activeDoms = [], wrapperDiv;

            if (isSlider) {
                controls = findByClass(controls[0], 'bd-slide');
                if (controls.length) {
                    [].forEach.call(controls, function (slide) {
                        activeDoms = findTopLevelDoms(slide, 'bd-parallax-image-wrapper', controlClass);
                        if (!activeDoms.length) {
                            slide.style.backgroundImage = 'none';
                            slide.style.backgroundColor = 'transparent';
                            wrapperDiv = document.createElement('div');
                            wrapperDiv.className = 'bd-parallax-image-wrapper';
                            wrapperDiv.innerHTML = '<div class="bd-parallax-image"></div>';
                            slide.insertBefore(wrapperDiv, slide.firstChild);
                        }
                    });
                }
            }
            else if (isColumn) {
                if (controls.length) {
                    activeDoms = findTopLevelDoms(that, 'bd-parallax-image-wrapper', controlClass);
                    if (!activeDoms.length) {
                        var effectClone = that.cloneNode(true);
                        effectClone.innerHTML = '';

                        var columnNode = controls[0].parentNode;
                        $(columnNode).unwrap();
                        columnNode.insertBefore(effectClone, columnNode.firstChild);

                        wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'bd-parallax-image-wrapper';
                        wrapperDiv.innerHTML = '<div class="bd-parallax-image"></div>';

                        effectClone.insertBefore(wrapperDiv, effectClone.firstChild);
                    }
                }
            }
            else {
                if (controls.length) {
                    activeDoms = findTopLevelDoms(that, 'bd-parallax-image-wrapper', controlClass);
                    if (!activeDoms.length) {
                        wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'bd-parallax-image-wrapper';
                        wrapperDiv.innerHTML = '<div class="bd-parallax-image"></div>';
                        that.insertBefore(wrapperDiv, that.firstChild);
                    }
                }
            }

            if (controls.length) {
                [].forEach.call(controls, function (control) {
                    var parallaxWrapper = isColumn ? findByClass(control.parentElement, 'bd-parallax-image-wrapper')[0] : findTopLevelDoms(control, 'bd-parallax-image-wrapper', controlClass)[0],
                        parallaxImg = parallaxWrapper.getElementsByClassName('bd-parallax-image')[0],
                        controlOffset = $(that).offset().top,
                        controlHeight = that.clientHeight,
                        viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

                    if (control.style.backgroundImage === 'none') {
                        control.style.backgroundImage = '';
                    }

                    if (control.style.backgroundColor === 'transparent') {
                        control.style.backgroundColor = '';
                    }

                    var backgroundStyles = getComputedStyle(control);

                    if (backgroundStyles.position === 'static') {
                        control.style.position = 'relative';
                    }

                    if (backgroundStyles.backgroundImage !== 'none' && parallaxImg.style.backgroundImage !== backgroundStyles.backgroundImage) {
                        parallaxImg.style.backgroundImage = backgroundStyles.backgroundImage;
                    }

                    if (backgroundStyles.backgroundColor !== 'transparent' && parallaxImg.style.backgroundColor !== backgroundStyles.backgroundColor) {
                        parallaxImg.style.backgroundColor = backgroundStyles.backgroundColor;
                    }

                    control.style.backgroundImage = 'none';
                    control.style.backgroundColor = 'transparent';
                    parallaxImg.style.backgroundRepeat = backgroundStyles.backgroundRepeat;
                    parallaxImg.style.backgroundPosition = backgroundStyles.backgroundPosition;

                    if (isSlider) {
                        parallaxWrapper.style.setProperty('z-index', '-2', 'important');
                    }

                    if (isColumn) {
                        var containerStyles = getComputedStyle(parallaxWrapper);
                        parallaxImg.style.setProperty('min-width', containerStyles.width, 'important');
                        //parallaxImg.style.setProperty('min-height', Math.min(viewPortHeight, 3 * parseInt(containerStyles.height)) + 'px', 'important');
                    }

                    if (isSlider && control.className.indexOf('active') !== -1) {
                        that.setAttribute('data-sliderTop', $(parallaxImg).offset().top);
                        that.setAttribute('data-imageHeight', parallaxImg.clientHeight);
                    }

                    var positionDifference,
                        imageOffset = isSlider ? parseFloat(that.getAttribute('data-sliderTop')) : $(parallaxImg).offset().top,
                        controlBottom = controlOffset + controlHeight,
                        imageBottom = imageOffset + viewPortHeight,
                        visibleBottom = imageBottom > controlBottom ? controlBottom : imageBottom,
                        spaceArea = controlBottom - visibleBottom;

                    if (spaceArea > 0) {
                        var scaledSize = ((viewPortHeight + spaceArea) / viewPortHeight) * 100;
                        parallaxImg.style.height = scaledSize + 'vh';
                    }

                    var imageHeight = isSlider ? parseFloat(that.getAttribute('data-imageHeight')) : parallaxImg.clientHeight;
                    if (controlOffset >= imageHeight / 2) {
                        //var additionalSpace = controlOffset < viewPortHeight ? (viewPortHeight - controlOffset) / 2 : 0;
                        positionDifference = -imageHeight / 2 /*+ additionalSpace*/ + (getCompatibleScrollTop() + viewPortHeight - controlOffset) / 2;
                    }
                    else {
                        positionDifference = /*-controlOffset / 2*/ +(getCompatibleScrollTop() - controlOffset) / 2;
                    }
                    if (getCompatibleScrollTop() + viewPortHeight > controlOffset && getCompatibleScrollTop() < controlBottom) {
                        var transformProperty = getSupportedPropertyName(transform);
                        if (transformProperty) {
                            parallaxImg.style[transformProperty] = 'translate3d(0, ' + positionDifference + 'px, 0)';
                        }
                    }
                });
            }
        });
    }

    function onScroll() {
        [].forEach.call(document.getElementsByClassName('bd-parallax-bg-effect'), function (element) {
            var that = element,
                controlClass = that.getAttribute('data-control-selector').replace(/\./g, ''),
                controls = document.getElementsByClassName(controlClass),
                isSlider = /bd-slider-\d+($|\s)/g.test(controlClass) || getClassName(controls[0]).indexOf('bd-slider') !== -1,
                isColumn = /bd-layoutcolumn-\d+($|\s)/g.test(controlClass);

            if (isSlider) {
                controls = findByClass(controls[0], 'bd-slide');
            }

            if (controls.length) {
                [].forEach.call(controls, function (control) {
                    var viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0),
                        controlOffset = $(that).offset().top,
                        controlHeight = that.clientHeight,
                        controlBottom = controlOffset + controlHeight;

                    if (getCompatibleScrollTop() + viewPortHeight > controlOffset && getCompatibleScrollTop() < controlBottom) {
                        var parallaxWrapper = isColumn ? findByClass(control.parentElement, 'bd-parallax-image-wrapper')[0] : findTopLevelDoms(control, 'bd-parallax-image-wrapper', controlClass)[0],
                            parallaxImg = parallaxWrapper.getElementsByClassName('bd-parallax-image')[0],
                            positionDifference;

                        if (isSlider && control.className.indexOf('active') !== -1) {
                            that.setAttribute('data-imageHeight', parallaxImg.clientHeight);
                        }

                        var imageHeight = isSlider ? parseFloat(that.getAttribute('data-imageHeight')) : parallaxImg.clientHeight;
                        if (controlOffset >= imageHeight / 2) {
                            //var additionalSpace = controlOffset < viewPortHeight ? (viewPortHeight - controlOffset) / 2 : 0;
                            positionDifference = -imageHeight / 2 /*+ additionalSpace*/ + (getCompatibleScrollTop() + viewPortHeight - controlOffset) / 2;
                        }
                        else {
                            positionDifference = /*-controlOffset / 2*/ +(getCompatibleScrollTop() - controlOffset) / 2;
                        }

                        var transformProperty = getSupportedPropertyName(transform);
                        if (transformProperty) {
                            parallaxImg.style[transformProperty] = 'translate3d(0, ' + positionDifference + 'px, 0)';
                        }
                    }
                });
            }
        });
    }

    function getClassName(element) {
        var className = element ? element.className : null;
        if (className) {
            if (typeof className === 'string') {
                return className;
            } else if (typeof className === 'object' && 'baseVal' in className) { // for SVG elements
                return className.baseVal;
            }
        }
        return '';
    }

    function getSupportedPropertyName(properties) {
        for (var i = 0; i < properties.length; i++) {
            if (typeof document.body.style[properties[i]] !== 'undefined') {
                return properties[i];
            }
        }
        return null;
    }

    function getCompatibleScrollTop() {
        if ("undefined" !== typeof window.scrollY) {
            return window.scrollY;
        }
        else {
            return document.documentElement.scrollTop;
        }
    }

    function findByClass(parentElement, searchClassName) {
        return [].slice.call(parentElement.getElementsByTagName('*')).filter(function (value) {
            var className = getClassName(value);
            return (' ' + className + ' ').indexOf(' ' + searchClassName + ' ') !== -1;
        });
    }

    function findTopLevelDoms(element, searchClassName, controlClassName) {
        var isEffectDom = function (domElement) {
                return getClassName(domElement).indexOf('bd-parallax-bg-effect') !== -1 && domElement.getAttribute('data-control-selector') === '.' + controlClassName;
            },
            findDom = function (domElement) {
                return [].slice.call(domElement.getElementsByClassName(searchClassName)).filter(function (value) {
                    return value.parentNode === domElement;
                });
            };

        var foundDom = findDom(element);
        if (foundDom.length === 0) {
            while (!isEffectDom(element) && element) {
                element = element.parentElement;
            }
        }

        return foundDom.length ? foundDom : findDom(element);
    }
})(jQuery);
})(window._$, window._$);
window.ProductOverview_Class = "bd-productoverview";
(function (jQuery, $) {
jQuery(function($) {
    'use strict';
    function makeCloudZoom1() {
        if ($('.bd-productimage-6 a').length > 0) {
            $('.bd-productimage-6 a').attr('id', 'cloud-zoom-effect-1').addClass('cloud-zoom');
            $('.bd-productimage-6 a').attr('rel', "position:'right', adjustX:0, adjustY:0, tint:'#ffffff', softFocus:1, smoothMove:1, tintOpacity:0.5");

            if ('undefined' !== typeof window.ProductOverview_Class && 'undefined' !== typeof window.ImageThumbnails_Class) {
                var parent = $('.bd-productimage-6')
                            .closest('[class*=" ' + window.ProductOverview_Class + '"], [class^="' + window.ProductOverview_Class + '"]'),
                    thumbnails = $('[class*=" ' + window.ImageThumbnails_Class + '"], [class^="' + window.ImageThumbnails_Class + '"]', parent);

                if (thumbnails.length > 0) {
                    $('a', thumbnails).each(function () {
                        var thumbnail = $(this),
                            rel = thumbnail.attr('rel'),
                            relAttr = (rel === '' ? '' : rel + ',') + "useZoom: 'cloud-zoom-effect-1'";
                        thumbnail.attr('rel', relAttr).addClass('cloud-zoom-gallery');
                    });
                }
            }

            var parent = $(".bd-productimage-6").parents().filter(function (key, value) {
                return parseInt($(value).css('z-index'), 10).toString() !== 'NaN';
            });

            var minZIndex = 100;
            var zIndex = parent.length > 0 ? parseInt($(parent[0]).css('z-index'), 10) + 1 : 1;
            zIndex = zIndex < minZIndex ? minZIndex : zIndex;

            $('<style type="text/css"> .bd-productimage-6 .mousetrap { z-index: ' + zIndex + '!important;} </style>').appendTo("head");

            $('#cloud-zoom-effect-1, .cloud-zoom-gallery').CloudZoom();
        }
    }
    makeCloudZoom1();
    var resizeTimeout;
    $(window).resize(function(){
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(makeCloudZoom1, 25);
    });
});
})(window._$, window._$);
(function (jQuery, $) {
window.ImageThumbnails_Class = 'bd-imagethumbnails';
jQuery(function () {
    'use strict';
    /* 'active' must be always specified, otherwise slider would not be visible */
    jQuery('.bd-imagethumbnails-1.carousel.slide').each(function () {
        var slider = jQuery(this);
        if (!slider || !slider.length){
            return;
        }

        slider.data('resize', function () {
            jQuery('.carousel-inner', slider).equalImageHeight();
        });

        slider.data('resize')();

        jQuery('.bd-left-button .bd-carousel-3', slider)
            .attr('href', '#')
            .click(function() {
                slider.carousel('prev');
                return false;
        });

        jQuery('.bd-right-button .bd-carousel-3', slider)
            .attr('href', '#')
            .click(function() {
                slider.carousel('next');
                return false;
        });
    });
});
})(window._$, window._$);
(function (jQuery, $) {
jQuery(function ($) {
    $('.review-rating .bd-rating').mousemove( function(e){
        var t = event.target || event.srcElement;
        if ('span' === t.tagName.toLowerCase()) {
            $(event.target).addClass('active');
            $(event.target).prevAll().addClass('active');
            $(event.target).nextAll().removeClass('active');
            $('#vote').val($(event.target).prevAll().length + 1);
        }
    });
});
})(window._$, window._$);
(function (jQuery, $) {
jQuery(function($) {
    'use strict';

    var activeLayoutType = $.cookie('layoutType') || 'grid',
        activeLayoutTypeSelector = $.cookie('layoutSelector') || '.separated-item-5.grid';

    var layoutTypes = [];
    
        layoutTypes.push({
            name:'bd-griditemgrid',
            iconUrl: '',
            iconDataId: '3368',
            iconClassNames: 'bd-icon-64'
        });
        layoutTypes.push({
            name:'bd-griditemlist',
            iconUrl: '',
            iconDataId: '3383',
            iconClassNames: 'bd-icon-65'
        });
    if (typeof window.buildTypeSelector === 'function') {
        window.buildTypeSelector(layoutTypes, $('.bd-productsgridbar-28'));
    }

    
        $(document).on('click', '.bd-products i[data-layout-name="bd-griditemgrid"]', function (e) {
            if (activeLayoutType !== 'grid') {
                var grid = $('.bd-grid-53');
                grid.find('.separated-item-5.grid').css('display', 'block');
                grid.find(activeLayoutTypeSelector).css('display', 'none');
                activeLayoutType = 'grid';
                activeLayoutTypeSelector = '.separated-item-5.grid';

                $.cookie('layoutType', activeLayoutType, { path: '/' });
                $.cookie('layoutSelector', activeLayoutTypeSelector, { path: '/' });
            }
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        $(document).on('click', '.bd-products i[data-layout-name="bd-griditemlist"]', function (e) {
            if (activeLayoutType !== 'list') {
                var grid = $('.bd-grid-53');
                grid.find('.separated-item-6.list').css('display', 'block');
                grid.find(activeLayoutTypeSelector).css('display', 'none');
                activeLayoutType = 'list';
                activeLayoutTypeSelector = '.separated-item-6.list';

                $.cookie('layoutType', activeLayoutType, { path: '/' });
                $.cookie('layoutSelector', activeLayoutTypeSelector, { path: '/' });
            }
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

});
})(window._$, window._$);
(function (jQuery, $) {
buildTypeSelector = function(layouts, parent) {
    layouts.map(function(layout){
        var a = document.createElement('a'),
            i = document.createElement('i');
        jQuery(i).addClass(layout.iconClassNames).addClass('data-control-id-' + layout.iconDataId);
        jQuery(i).attr('data-layout-name', layout.name);
        jQuery(a).attr('href', '##').append(i);
        jQuery(a).each(function() { this.style.textDecoration = 'none'; });
        (parent || jQuery).find('.bd-typeselector-1').append(a);
    });
}
})(window._$, window._$);
(function (jQuery, $) {
jQuery(function ($) {
    'use strict';

    function getFloat(value){
        return parseFloat(value.replace('px', ''))  ;
    }

    $('.bd-productsslider-1').each(function () {
        var slider = $(this).find('.carousel.slide');
        slider.carousel({ interval: 3000, pause: "hover", wrap: true});

        var leftButton = $('.bd-left-button', slider);
        var rightButton = $('.bd-right-button', slider);

        
            var blockSelector = '.bd-block-5',
                blockHeaderSelector = '.bd-block-5 .bd-blockheader';
            if ($(blockSelector, this).length > 0 && $(blockHeaderSelector, this).length > 0)
            {
                var block = $(blockSelector, this),
                    blockHeader = block.find('*').filter(blockHeaderSelector),
                    blockHeaderTitle = blockHeader.children('h4');

                blockHeader.css('min-height', '35px');
                blockHeader.css('position', 'relative');

                var navigationWrapper = $('<div class="bd-top-navigation-wrapper"></div>');
                blockHeaderTitle.addClass('bd-top-navigation');
                blockHeaderTitle.append(navigationWrapper);

                leftButton.appendTo(navigationWrapper);
                rightButton.appendTo(navigationWrapper);
            }

        leftButton.find('.bd-carousel-1').click(function() {
            slider.carousel('prev');
            return false;
        }).attr('href', '#');

        rightButton.find('.bd-carousel-1').click(function() {
            slider.carousel('next');
            return false;
        }).attr('href', '#');

        
    });
});
})(window._$, window._$);
(function (jQuery, $) {
jQuery(function ($) {
    'use strict';

    $('[data-smooth-scroll]').on('click', 'a[href^="#"]:not([data-toggle="collapse"])', function (e) {
        var animationTime = parseInt($(e.delegateTarget).data('animationTime'), 10) || 0;
        var target = this.hash;
        var link = $(this);
        e.preventDefault();

        $('body').data('scroll-animating', true);
        var targetElement = $(target || 'body');

        link.trigger($.Event('theme.smooth-scroll.before'));

        if (!targetElement || !targetElement.length)
            return;

        $('html, body').animate({
            scrollTop: targetElement.offset().top
        }, animationTime, function() {
            $('body').data('scroll-animating', false);
            window.location.hash = target;
            if (targetElement.is(':not(body)') && $('body').data('bs.scrollspy')) {
                link.parent('li').siblings('li').children('a').removeClass('active');
                link.addClass('active');
            }
            link.trigger($.Event('theme.smooth-scroll.after'));
        });
    });
});
})(window._$, window._$);
(function (jQuery, $) {
function SmoothWheel() {
    'use strict';

    this.options = {
        animtime: 500,
        stepsize: 150,
        pulseAlgorithm: false,
        pulseScale: 6,
        keyboardsupport: true,
        arrowscroll: 50,
        useOnWebKit: true,
        useOnMozilla: true,
        useOnIE: true
    };

    var that = this;

    /*global Date */
    function ssc_init() {

        if (!document.body) return;
        var e = document.body;
        var t = document.documentElement;
        var n = window.innerHeight;
        var r = e.scrollHeight;
        ssc_root = document.compatMode.indexOf("CSS") >= 0 ? t : e;
        ssc_activeElement = e;
        ssc_initdone = true;
        if (top !== self) {
            ssc_frame = true;
        } else if (r > n && (e.offsetHeight <= n || t.offsetHeight <= n)) {
            ssc_root.style.height = "auto";
            if (ssc_root.offsetHeight <= n) {
                var i = document.createElement("div");
                i.style.clear = "both";
                e.appendChild(i);
            }
        }
        if (!ssc_fixedback) {
            e.style.backgroundAttachment = "scroll";
            t.style.backgroundAttachment = "scroll";
        }
        if (that.options.keyboardsupport) {
            ssc_addEvent("keydown", ssc_keydown);
        }
    }

    function ssc_scrollArray(e, t, n, r) {
        r || (r = 1e3);
        ssc_directionCheck(t, n);
        ssc_que.push({
            x: t,
            y: n,
            lastX: t < 0 ? 0.99 : -0.99,
            lastY: n < 0 ? 0.99 : -0.99,
            start: +(new Date())
        });
        if (ssc_pending) {
            return;
        }
        var i = function() {
            var s = +(new Date());
            var o = 0;
            var u = 0;
            for (var a = 0; a < ssc_que.length; a++) {
                var f = ssc_que[a];
                var l = s - f.start;
                var c = l >= that.options.animtime;
                var h = c ? 1 : l / that.options.animtime;
                if (that.options.pulseAlgorithm) {
                    h = ssc_pulse(h);
                }
                var p = f.x * h - f.lastX >> 0;
                var d = f.y * h - f.lastY >> 0;
                o += p;
                u += d;
                f.lastX += p;
                f.lastY += d;
                if (c) {
                    ssc_que.splice(a, 1);
                    a--;
                }
            }
            if (t) {
                var v = e.scrollLeft;
                e.scrollLeft += o;
                if (o && e.scrollLeft === v) {
                    t = 0;
                }
            }
            if (n) {
                var m = e.scrollTop;
                e.scrollTop += u;
                if (u && e.scrollTop === m) {
                    n = 0;
                }
            }
            if (!t && !n) {
                ssc_que = [];
            }
            if (ssc_que.length) {
                setTimeout(i, r / ssc_framerate + 1);
            } else {
                ssc_pending = false;
            }
        };
        setTimeout(i, 0);
        ssc_pending = true;
    }

    function ssc_wheel(e) {
        if (!ssc_initdone) {
            ssc_init();
        }
        var t = e.target;
        var n = ssc_overflowingAncestor(t);
        if (!n || e.defaultPrevented || ssc_isNodeName(ssc_activeElement, "embed") || ssc_isNodeName(t, "embed") && /\.pdf/i.test(t.src)) {
            return true;
        }
        var r = e.wheelDeltaX || e.deltaX || 0;
        var i = e.wheelDeltaY || e.deltaY || 0;
        if (n.nodeName === 'BODY' && (currentBrowser === 'firefox' || currentBrowser === "msie" || currentBrowser === "netscape")) {
            n = document.documentElement;
            r = -r;
            i = -i;
            if (currentBrowser === 'firefox') {
                r *= 40;
                i *= 40;
            }
        }

        if (!r && !i) {
            i = e.wheelDelta || 0;
        }
        if (Math.abs(r) > 1.2) {
            r *= that.options.stepsize / 120;
        }
        if (Math.abs(i) > 1.2) {
            i *= that.options.stepsize / 120;
        }
        ssc_scrollArray(n, -r, -i);
        e.preventDefault();
    }

    function ssc_keydown(e) {
        var t = e.target;
        var n = e.ctrlKey || e.altKey || e.metaKey;
        if (/input|textarea|embed/i.test(t.nodeName) || t.isContentEditable || e.defaultPrevented || n) {
            return true;
        }
        if (ssc_isNodeName(t, "button") && e.keyCode === ssc_key.spacebar) {
            return true;
        }
        var r, i = 0,
            s = 0;
        var o = ssc_overflowingAncestor(ssc_activeElement);
        var u = o.clientHeight;
        if (o === document.body) {
            u = window.innerHeight;
        }
        switch (e.keyCode) {
            case ssc_key.up:
                s = -that.options.arrowscroll;
                break;
            case ssc_key.down:
                s = that.options.arrowscroll;
                break;
            case ssc_key.spacebar:
                r = e.shiftKey ? 1 : -1;
                s = -r * u * 0.9;
                break;
            case ssc_key.pageup:
                s = -u * 0.9;
                break;
            case ssc_key.pagedown:
                s = u * 0.9;
                break;
            case ssc_key.home:
                s = -o.scrollTop;
                break;
            case ssc_key.end:
                var a = o.scrollHeight - o.scrollTop - u;
                s = a > 0 ? a + 10 : 0;
                break;
            case ssc_key.left:
                i = -that.options.arrowscroll;
                break;
            case ssc_key.right:
                i = that.options.arrowscroll;
                break;
            default:
                return true;
        }
        ssc_scrollArray(o, i, s);
        e.preventDefault();
    }

    function ssc_mousedown(e) {
        ssc_activeElement = e.target;
    }

    function ssc_setCache(e, t) {
        for (var n = e.length; n--;) ssc_cache[ssc_uniqueID(e[n])] = t;
        return t;
    }

    function ssc_overflowingAncestor(e) {
        var t = [];
        var n = ssc_root.scrollHeight;
        do {
            var r = ssc_cache[ssc_uniqueID(e)];
            if (r) {
                return ssc_setCache(t, r);
            }
            t.push(e);
            if (n === e.scrollHeight) {
                if (!ssc_frame || ssc_root.clientHeight + 10 < n) {
                    return ssc_setCache(t, document.body);
                }
            } else if (e.clientHeight + 10 < e.scrollHeight) {
                overflow = getComputedStyle(e, "").getPropertyValue("overflow");
                if (overflow === "scroll" || overflow === "auto") {
                    return ssc_setCache(t, e);
                }
            }
        } while ((e = e.parentNode));
    }

    function ssc_addEvent(e, t, n) {
        window.addEventListener(e, t, n || false);
    }

    function ssc_removeEvent(e, t, n) {
        window.removeEventListener(e, t, n || false);
    }

    function ssc_isNodeName(e, t) {
        return e.nodeName.toLowerCase() === t.toLowerCase();
    }

    function ssc_directionCheck(e, t) {
        e = e > 0 ? 1 : -1;
        t = t > 0 ? 1 : -1;
        if (ssc_direction.x !== e || ssc_direction.y !== t) {
            ssc_direction.x = e;
            ssc_direction.y = t;
            ssc_que = [];
        }
    }

    function ssc_pulse_(e) {
        var t, n, r;
        e = e * that.options.pulseScale;
        if (e < 1) {
            t = e - (1 - Math.exp(-e));
        } else {
            n = Math.exp(-1);
            e -= 1;
            r = 1 - Math.exp(-e);
            t = n + r * (1 - n);
        }
        return t * ssc_pulseNormalize;
    }

    function ssc_pulse(e) {
        if (e >= 1) return 1;
        if (e <= 0) return 0;
        if (ssc_pulseNormalize === 1) {
            ssc_pulseNormalize /= ssc_pulse_(1);
        }
        return ssc_pulse_(e);
    }
    var overflow = '';
    var ssc_framerate = 150;
    var ssc_pulseNormalize = 1;
    var ssc_frame = false;
    var ssc_direction = {
        x: 0,
        y: 0
    };
    var ssc_initdone = false;
    var ssc_fixedback = true;
    var ssc_root = document.documentElement;
    var ssc_activeElement;
    var ssc_key = {
        left: 37,
        up: 38,
        right: 39,
        down: 40,
        spacebar: 32,
        pageup: 33,
        pagedown: 34,
        end: 35,
        home: 36
    };
    var ssc_que = [];
    var ssc_pending = false;
    var ssc_cache = {};
    var currentBrowser = '';

    setInterval(function() {
        ssc_cache = {};
    }, 10 * 1e3);

    var ssc_uniqueID = function() {
        var e = 0;
        return function(t) {
            return t.ssc_uniqueID || (t.ssc_uniqueID = e++);
        };
    }();

    jQuery(document).ready(function() {
        function t() {
            var e = navigator.appName,
                tReg = navigator.userAgent,
                n;
            var r = tReg.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
            if (r && (n = tReg.match(/version\/([\.\d]+)/i)) !== null) r[2] = n[1];
            r = r ? [r[1], r[2]] : [e, navigator.appVersion, "-?"];
            return r[0];
        }
        currentBrowser = t().toLowerCase();

        var webKit = 'safari;chrome';
        var IE = 'netscape;msie';
        var mozilla = 'firefox';

        var browserName = [
            (that.options.useOnMozilla ? mozilla : ''),
            (that.options.useOnWebKit ? webKit : ''),
            (that.options.useOnIE ? IE : '')
        ].join(';');

        var neededBrowser = browserName.indexOf(currentBrowser) !== -1;

        if (neededBrowser) {
            ssc_addEvent("mousedown", ssc_mousedown);
            if (currentBrowser === 'firefox' || currentBrowser === "msie" || currentBrowser === "netscape") {
                ssc_addEvent("wheel", ssc_wheel);
            } else {
                ssc_addEvent("mousewheel", ssc_wheel);
            }
            ssc_addEvent("load", ssc_init);
        }
    });

    this.update = function update(newOptions) {
        if (!that.options.keyboardsupport) {
            ssc_removeEvent("keydown", ssc_keydown);
        }
        $.extend(this.options, newOptions);
    };

}

(function () {
    'use strict';

    var _instance;

    window._smoothWheelInstance = function () {
        if (!_instance) {
            _instance = new SmoothWheel();
        }

        return _instance;
    };
})();
})(window._$, window._$);
(function (jQuery, $) {
(function ($) {
    'use strict';

    var timeout;
    $(window).on('resize', function (e, param) {
        clearTimeout(timeout);
        if (param && param.force) {
            stretchToBottom();
        } else {
            timeout = setTimeout(stretchToBottom, 25);
        }
    });

    $(stretchToBottom);

    function stretchToBottom() {
        var bh, mh = 0;
        var parent,
            html = document.documentElement,
            prevHeight = html.style.height;

        html.style.height = '100%';

        var c = $('.bd-stretch-to-bottom');
        var target = c.find(c.data('controlSelector'))
            .add(c.find(c.data('controlSelector') + ' .bd-stretch-inner').first());

        if (target.length === 0) {
            return;
        }

        target.removeAttr('style');
        bh = $('body').height();

        var prevMargin = 0;
        $('body').children().each(function() {
            var $node = $(this);
            if ($node.css('float') !== 'left' && $node.css('float') !== 'right' &&
                $node.css('position') !== 'absolute' && $node.css('position') !== 'fixed') {

                if (!prevMargin) {
                    mh += parseFloat($node.css('margin-top'));
                } else {
                    mh += Math.max(parseFloat($node.css('margin-bottom')), prevMargin);
                }

                mh += $node.outerHeight();

                prevMargin = parseFloat($node.css('margin-bottom'));

                if ($.contains(this, target[0]) || this === target[0]) {
                    parent = $node;
                }
            }
        });

        if (mh < bh && parent) {
            var r = bh - mh;
            target.css('min-height', (target.outerHeight(true) + r) + 'px');
        }

        html.style.height = prevHeight;
    }

})(jQuery);
})(window._$, window._$);
(function (jQuery, $) {

jQuery(function ($) {
    'use strict';
    // hide #back-top first
    $(".bd-backtotop-1").hide();
    // fade in #back-top
    $(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.bd-backtotop-1').fadeIn().css('display', 'block');
            } else {
                $('.bd-backtotop-1').fadeOut();
            }
        });
    });
});

})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-4 img:not(.no-lightbox)').init();
});
})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-1 img:not(.no-lightbox)').init();
});
})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-5 img:not(.no-lightbox)').init();
});
})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-6 img:not(.no-lightbox)').init();
});
})(window._$, window._$);