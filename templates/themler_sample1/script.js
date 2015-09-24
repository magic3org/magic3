(function (jQuery, $) {
var PREVIEW = false;

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
        $(window).resize(function(){
            $('.bd-row-auto-height').equalColumnsHeight();
        });
    });
})(jQuery);

// IE10+ flex fix
if (1-'\0') {

    var fixHeight = function fixHeight() {
        jQuery('[class*=" bd-layoutitemsbox"].bd-flex-wide, [class^="bd-layoutitemsbox"].bd-flex-wide').each(function () {
            var content = jQuery(this);
            var wrapper = content.children('.bd-fix-flex-height');
            if (!wrapper.length) {
                content.wrapInner('<div class="bd-fix-flex-height"></div>');
            }
            var height = wrapper.outerHeight(true);
            content.css({
                '-ms-flex-preferred-size': height + 'px',
                'flex-basis': height + 'px'
            });
        });

        setTimeout(fixHeight, 500);
    };

    jQuery(fixHeight);
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

(function ($) {
    'use strict';
    var row = [],
        getOffset = function (el) {
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
        getCollapsedMargin = function (el) {
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
        childFilter = function () {
            return classRE.test(this.className);
        },
        calcOrder = function (items) {
            var roots = items;
            while (roots.eq(0).children().length === 1) {
                roots = roots.children();
            }
            var childrenClasses = [];
            var childrenWeights = {};
            var getNextWeight = function (children, i, l) {
                for (var j = i + 1; j < l; j++) {
                    var cls = children[j].className.replace(classRE, '$1');
                    if (childrenClasses.indexOf(cls) !== -1) {
                        return childrenWeights[cls];
                    }
                }
                return 100; //%
            };
            roots.each(function (i, root) {
                var children = $(root).children().filter(childFilter);
                var previousWeight = 0;
                for (var c = 0, l = children.length; c < l; c++) {
                    var cls = children[c].className.replace(classRE, '$1');
                    if (childrenClasses.indexOf(cls) === -1) {
                        var nextWeight = getNextWeight(children, c, l);
                        childrenWeights[cls] = previousWeight + (nextWeight - previousWeight) / 10; //~max unique child
                        childrenClasses.push(cls);
                    }
                    previousWeight = childrenWeights[cls];
                }
            });
            childrenClasses.sort(function (a, b) {
                return childrenWeights[a] > childrenWeights[b];
            });
            return childrenClasses;
        };
    var calcRow = function (helpNodes, last, order) {

        $(row).css({'overflow': 'visible', 'height': 'auto'}).toggleClass('last-row', last);

        if (row.length > 0) {
            var roots = $(row);
            roots.removeClass('last-col').last().addClass('last-col');
            while (roots.eq(0).children().length === 1) {
                roots = roots.children();
            }
            var cls = '';
            var maxOffset = 0;
            var calcMaxOffsets = function (i, root) {
                var el = $(root).children().filter('.' + cls + ':visible:first');
                if (el.length < 1 || el.css('position') === 'absolute') {
                    return;
                }
                var offset = getOffset(el);
                if (offset > maxOffset) {
                    maxOffset = offset;
                }
            };
            var setMaxOffsets = function (i, root) {
                var el = $(root).children().filter('.' + cls + ':visible:first');
                if (el.length < 1 || el.css('position') === 'absolute') {
                    return;
                }
                var offset = getOffset(el);
                var fix = maxOffset - offset - getCollapsedMargin(el);
                if (fix > 0) {
                    var helpNode = document.createElement('div');
                    helpNode.setAttribute('style', 'height:' + fix + 'px');
                    helpNode.className = 'bd-empty-grid-item';
                    helpNodes.push(helpNode);
                    el.before(helpNode);
                }
            };
            for (var c = 0; c < order.length; c++) {
                maxOffset = 0;
                cls = order[c];
                roots.each(calcMaxOffsets).each(setMaxOffsets);
            }
            var hMax = 0;
            $.each(roots, function (i, e) {
                var h = $(e).outerHeight();
                if (hMax < h) {
                    hMax = h;
                }
            });
            $.each(roots, function (i, e) {
                var el = $(e);
                var fix = hMax - el.outerHeight();
                if (fix > 0) {
                    var helpNode = document.createElement('div');
                    helpNode.setAttribute('style', 'height:' + fix + 'px');
                    helpNode.className = 'bd-empty-grid-item';
                    helpNodes.push(helpNode);
                    el.append(helpNode);
                }
            });
        }
        row = [];
    };
    var itemsRE = new RegExp('.*(separated-item[^\\s]+).*'),
        resize =  function () {
            var grid = $('.separated-grid');
            grid.each(function (i, gridElement) {
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
                        })
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
                items.each(function (i, gridItem) {
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
                $(notDisplayed).each(function (i, e) {
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
        lazy = function(){
            clearTimeout(timeoutLazy);
            timeoutLazy = setTimeout(resize, 100);
        },
        interval =function (){
            lazy();
            setTimeout(interval, 1000);
        };
    $(window).resize(lazy);
    $(interval);
})(jQuery);


(function ($) {
    'use strict';
    $(document).ready(function () {
        if ("undefined" !== typeof parent.AppController) return;
        var controls = $('[data-autoplay=true]');
        $(controls).each(function (index, item) {
            item.src = item.src + "&autoplay=1";
        });
    });

})(jQuery);

jQuery(function ($) {
    'use strict';

    $(document).on('click.themler', '[data-responsive-menu] li > a:not([data-toggle="collapse"])', function responsiveClick() {
        var itemLink = $(this);
        var menu = itemLink.closest('[data-responsive-menu]');
        var responsiveBtn = menu.find('.collapse-button');
        var responsiveLevels = menu.data('responsiveLevels');

        if (responsiveBtn.length &&
                !responsiveBtn.is(':visible') ||
                (responsiveLevels !== 'expand on click' && responsiveLevels !== '') ||
                !menu.data('responsiveMenu')) {
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
            submenu.addClass('show');
            itemLink.addClass('active');
        }
        return false;
    });
});

jQuery(function ($) {
    'use strict';

    $('body')
        .on('click.themler', '[data-url] a', function (e) {
            e.stopPropagation();
        })
        .on('click.themler', '[data-url]', function () {
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

    $(document).on('mouseenter', 'ul.nav > li, .nav ul > li', function calcSubmenuDirection() {
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
    $(window).on('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(window.tabCollapseResize, 25);
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
    var panels = $('.bd-accordion .bd-container-49').parent();
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


jQuery(function($) {
    'use strict';
    $('.add_to_cart_button').filter(function(){
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

(function ($) {
    'use strict';
    window.eventSetProductType = function(event) {

        var form = event.data.form,
            product = event.data.product,
            prices = product.find(".product-prices");

        if (prices.length === 0) {
            return false;
        }

        if (!event.data.form) {
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
            url: window.vmSiteurl + "index.php?&option=com_virtuemart&view=productdetails&task=recalculate&format=json&nosef=1" + window.vmLang,
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

    window.onEventSetProductType = function(product) {
        product.attr('data-updating-content', true);
        $("form.product", product).each(function() {
            var form = $(this),
                select = form.find('select:not(.no-vm-bind)'),
                selectOutForm = $('.product-field[data-cart-attribute="1"] select:not(.no-vm-bind)', product),
                radio = form.find('input:radio:not(.no-vm-bind)'),
                radioOutForm = $('.product-field[data-cart-attribute="1"] input:radio:not(.no-vm-bind)', product),
                virtuemart_product_id = form.find('input[name="virtuemart_product_id[]"]').val();

            $(select).off('change', window.eventSetProductType);
            $(select).on('change', {form : form, product : product}, window.eventSetProductType);
            $(selectOutForm).off('change', window.eventSetProductType);
            $(selectOutForm).on('change', {form : form, product : product}, window.eventSetProductType);

            $(radio).off('change', window.eventSetProductType);
            $(radio).on('change', {form : form, product : product}, window.eventSetProductType);
            $(radioOutForm).off('change', window.eventSetProductType);
            $(radioOutForm).on('change', {form : form, product : product}, window.eventSetProductType);
        });
    };
})(jQuery);

})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-2 img:not(.no-lightbox)').init();
});
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

        jQuery('.left-button .bd-carousel-3', slider)
            .attr('href', '#')
            .click(function() {
                slider.carousel('prev');
                return false;
        });

        jQuery('.right-button .bd-carousel-3', slider)
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
                var grid = $('.bd-grid-55');
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
                var grid = $('.bd-grid-55');
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

        var leftButton = $('.left-button', slider);
        var rightButton = $('.right-button', slider);

        
            var blockSelector = '.bd-block',
                blockHeaderSelector = '.bd-container-58';
            if ($(blockSelector, this).length > 0 && $(blockHeaderSelector, this).length > 0)
            {
                var block = $(blockSelector, this),
                    blockHeader = block.find(blockHeaderSelector),
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
jQuery(function ($) {
    'use strict';

    $('.bd-smoothscroll-3 a[href^="#"]:not([data-toggle="collapse"])').on('click', function(e) {
        var animationTime = parseInt($('.bd-smoothscroll-3').data('animationTime'), 10) || 0;
        var target = this.hash;
        var link = $(this);
        e.preventDefault();

        $('body').data('scroll-animating', true);
        var targetElement = $(target || 'body');

        link.trigger($.Event('theme.smooth-scroll.before'));

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



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-4 img:not(.no-lightbox)').init();
});
})(window._$, window._$);
(function (jQuery, $) {



jQuery(function () {
    'use strict';
    new window.ThemeLightbox('.bd-postcontent-3 img:not(.no-lightbox)').init();
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