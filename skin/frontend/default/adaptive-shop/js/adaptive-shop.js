jQuery(document).ready(function($){

    // Função para criar as colunas para os sub-itens do menu
    $.fn.extend({
        splitListMany: function(cols){
            var list = $(this);
            var listLen = $(this).length;
            var colSize;
            var columns;

            if ((cols == null)||(cols <= 0)||(columns >= listLen)) { columns = 2; }
            else if (cols >= (listLen/2)) { columns = Math.floor(listLen/2); }
            else { columns = cols; }

            if (listLen%columns > 0) { colSize = Math.ceil(listLen/columns); }
            else { colSize = listLen/columns; }

            for(var i=1; i <= columns; i++){
                list.slice((i-1)*colSize,i*colSize).wrapAll('<div class="lists list-'+i+'">');
            }
            return $(this);
        }
    });

    // Aplicação da função das colunas nos sub-itens do menu
    function submenuCols() {

        $('.submenu').each(function(){
            var li = $(this).find('li');

            if(li.parent().is( 'div' )) {
                $('.submenu > .lists').find('li').unwrap();
            }

            if($(window).width() > 768 ) {
                if($(this).hasClass('cols')){
                    var cols = $(this).data('cols');

                    if($(window).width() < 880 && cols == 4) {
                        li.splitListMany(3);
                    } else {
                        li.splitListMany(cols);
                    }
                }
            }
        });
    }

    submenuCols();


    function resetCollapse() {
        $('.content').removeAttr('style');
        $('.dropdown').removeClass('collapse-active');
    }

    // Collapse do menu mobile
    var collapseMenu = (function menuCollapse(){

        $('.dropdown .label').on('click',function (e) {
            var menuContent = $(this).siblings('.content');
            var closestDropdown = $(this).closest('.dropdown');
            if(menuContent.length) {
                e.preventDefault();

                if(!closestDropdown.hasClass('collapse-active')) {
                    closestDropdown.addClass('collapse-active');
                    $('.dropdown').not(closestDropdown).removeClass('collapse-active');
                    $('.content').slideUp();
                    menuContent.slideDown();
                } else {
                    closestDropdown.removeClass('collapse-active');
                    menuContent.slideUp();
                }
            }
        });

        if($(window).width() >= 768) {
            resetCollapse();
            $('body').removeClass('nav-open');
        }

        return menuCollapse;
    })();

    /*
     *	Define as larguras dos sub-itens do menu.
     *	É necessário pois a função de transição do mouseover colocar os sub-itens com position absolute
     */

    var submenuWidth = (function submenuWidth(element) {
        if($(window).width() >= 768){
            $('.submenu').not('.not-resize').each(function(){

                $(this).width(0);

                var itemLi = $(this).find('li');
                var itemList = $(this).find('.lists');

                var listsWidth = 0;

                itemList.each(function(index) {
                    listsWidth += parseInt($(this).width(), 10);
                });

                var maxLiWidth = Math.max.apply( null, itemLi.map( function () {
                    return $( this ).outerWidth( true );
                }).get());

                if($(this).hasClass('cols')) {
                    $(this).width(listsWidth + 2);
                } else {
                    $(this).width(maxLiWidth);
                }

            });
        } else {
            $('.submenu').not('.not-resize').width('auto');
        }

        return submenuWidth;
    })();


    $(window).on('resize', function(){
        submenuWidth();
        submenuCols();
    });

    // Abre o menu mobile
    $(document).on('click touch', '.nav-trigger', function(event){
        event.preventDefault();
        $('html').toggleClass('nav-open');
        resetCollapse();
    });


    /*
     * Funções para as transições dos sub-itens
     * url: https://codepen.io/skazee/pen/NbpaoG
     */

    function nutriiDropdown( element ) {
        this.element = element;
        this.mainNavigation = this.element.find('.main-nav');
        this.mainNavigationItems = this.mainNavigation.find('.has-dropdown');
        this.dropdownList = this.element.find('.dropdown-list');
        this.dropdownWrappers = this.dropdownList.find('.dropdown');
        this.dropdownItems = this.dropdownList.find('.content');
        this.dropdownBg = this.dropdownList.find('.bg-layer');
        this.mq = this.checkMq();
        this.bindEvents();
    }

    nutriiDropdown.prototype.checkMq = function() {
        //check screen size
        var self = this;
        return window.getComputedStyle(self.element.get(0), '::before').getPropertyValue('content').replace(/'/g, "").replace(/"/g, "").split(', ');
    };


    nutriiDropdown.prototype.bindEvents = function() {
        var self = this;
        //hover over an item in the main navigation
        this.mainNavigationItems.mouseenter(function(event){
            //hover over one of the nav items -> show dropdown
            self.showDropdown($(this));
        }).mouseleave(function(){
            setTimeout(function(){
                //if not hovering over a nav item or a dropdown -> hide dropdown
                if( self.mainNavigation.find('.has-dropdown:hover').length == 0 && self.element.find('.dropdown-list:hover').length == 0 ) self.hideDropdown();
            }, 50);
        });

        //hover over the dropdown
        this.dropdownList.mouseleave(function(){
            setTimeout(function(){
                //if not hovering over a dropdown or a nav item -> hide dropdown
                (self.mainNavigation.find('.has-dropdown:hover').length == 0 && self.element.find('.dropdown-list:hover').length == 0 ) && self.hideDropdown();
            }, 50);
        });

        //click on an item in the main navigation -> open a dropdown on a touch device
        this.mainNavigationItems.on('touchstart', function(event){
            var selectedDropdown = self.dropdownList.find('#'+$(this).data('content'));
            if( !self.element.hasClass('is-dropdown-visible') || !selectedDropdown.hasClass('active') ) {
                event.preventDefault();
                self.showDropdown($(this));
            }
        });

    };

    nutriiDropdown.prototype.showDropdown = function(item) {
        this.mq = this.checkMq();
        if( this.mq == 'desktop') {
            var self = this;
            var selectedDropdown = this.dropdownList.find('#'+item.data('content')),
                selectedDropdownHeight = selectedDropdown.innerHeight(),
                selectedDropdownWidth = selectedDropdown.children('.content').innerWidth(),
                selectedDropdownLeft = (item.hasClass('contact')) ? item.offset().left + item.outerWidth() - selectedDropdown.outerWidth() : item.offset().left;

            //update dropdown position and size
            this.updateDropdown(selectedDropdown, parseInt(selectedDropdownHeight), selectedDropdownWidth, parseInt(selectedDropdownLeft));
            //add active class to the proper dropdown item
            this.element.find('.active').removeClass('active');
            selectedDropdown.addClass('active').removeClass('move-left move-right').prevAll().addClass('move-left').end().nextAll().addClass('move-right');
            item.addClass('active');
            //show the dropdown wrapper if not visible yet
            if( !this.element.hasClass('is-dropdown-visible') ) {
                setTimeout(function(){
                    self.element.addClass('is-dropdown-visible');
                }, 10);
            }
        }
    };

    nutriiDropdown.prototype.updateDropdown = function(dropdownItem, height, width, left) {
        this.dropdownList.css({
            '-moz-transform': 'translateX(' + left + 'px)',
            '-webkit-transform': 'translateX(' + left + 'px)',
            '-ms-transform': 'translateX(' + left + 'px)',
            '-o-transform': 'translateX(' + left + 'px)',
            'transform': 'translateX(' + left + 'px)',
            'width': width+'px',
            'height': height+'px'
        });

        this.dropdownBg.css({
            '-moz-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
            '-webkit-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
            '-ms-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
            '-o-transform': 'scaleX(' + width + ') scaleY(' + height + ')',
            'transform': 'scaleX(' + width + ') scaleY(' + height + ')'
        });
    };

    nutriiDropdown.prototype.hideDropdown = function() {
        this.mq = this.checkMq();
        if( this.mq == 'desktop') {
            this.element.removeClass('is-dropdown-visible').find('.active').removeClass('active').end().find('.move-left').removeClass('move-left').end().find('.move-right').removeClass('move-right');
        }
    };

    nutriiDropdown.prototype.resetDropdown = function() {
        this.mq = this.checkMq();
        if( this.mq == 'mobile') {
            this.dropdownList.removeAttr('style');
        }

        if( this.mq == 'desktop') {
            resetCollapse();
        }
    };

    var nutriiDropdowns = [];
    if( $('.cd-nutrii-dropdown').length > 0 ) {
        $('.cd-nutrii-dropdown').each(function(){
            //create a nutriiDropdown object for each .cd-nutrii-dropdown
            nutriiDropdowns.push(new nutriiDropdown($(this)));
        });

        var resizing = false;
        var resizeTimer;

        //on resize, reset dropdown style property
        updateDropdownPosition();
        $(window).on('resize', function(){
            if( !resizing ) {
                resizing =  true;
                (!window.requestAnimationFrame) ? setTimeout(updateDropdownPosition, 300) : window.requestAnimationFrame(updateDropdownPosition);
            }
        });

        function updateDropdownPosition() {
            nutriiDropdowns.forEach(function(element){
                element.resetDropdown();
            });

            resizing = false;
        };
    }

});

jQuery(document).ready(function($) {
    jQuery('.question').click(function(event) {
        if (!jQuery(this).hasClass('active')) {
            jQuery('.answer').slideUp();
            jQuery('.question').removeClass('active');
            jQuery(this).parents('.faq').find('.answer').slideDown();
            jQuery(this).addClass('active');
        } else {
            jQuery('.answer').slideUp();
            jQuery('.question').removeClass('active');
            jQuery(this).removeClass('active');
        }
    });
});

jQuery(document).ready(function(){
    var baseUrl = location.protocol + '//' + location.host;

    var $containerLoginDesktop = jQuery('#login-desktop');
    var $containerLoginMobile = jQuery('#login-mobile');

    var anonymousUserTemplate = '';
    var authenticatedUserTemplate = '';

    var anonymousUserTemplateMobile = '';
    var authenticatedUserTemplateMobile = '';

    var $cartContainer = jQuery('#cart-desktop');
    var $cartList = jQuery('ul', $cartContainer);

    var cartCounterTemplate = '';
    var cartTotalTemplate = '';
    var cartItemTemplate = '';

    anonymousUserTemplate = '<a href="{{ baseUrl }}/customer/account/login/">Login</a> | <a href="{{ baseUrl }}/customer/account/create/">Cadastro</a>';
    authenticatedUserTemplate = '<a href="{{ baseUrl }}/customer/account/">Minha conta</a> | <a href="{{ baseUrl }}/customer/account/logout/">Sair</a>';

    anonymousUserTemplateMobile = '<a href="{{ baseUrl }}/customer/account/login/">Login</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="{{ baseUrl }}/customer/account/create/">Cadastro</a>';
    authenticatedUserTemplateMobile = '<a href="{{ baseUrl }}/customer/account/">Minha conta</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="{{ baseUrl }}/customer/account/logout/">Sair</a>';


    cartCounterTemplate += '<span class="cart">{{ items }}</span>';

    cartItemTemplate += '<li class="item">';
    cartItemTemplate += '    <img class="f-l radius" src="{{ productImage }}" width="50" height="50" alt="{{ productName }}">';
    cartItemTemplate += '    <a class="f-l" href="{{ productUrl }}">{{ productName }}</a>';
    cartItemTemplate += '    <span class="d-b f-l fs-14 cor-sec">{{ quantity }}x <span class="price">{{ price }}</span></span>';
    cartItemTemplate += '    <div class="icons">';
    cartItemTemplate += '        <a';
    cartItemTemplate += '            href="{{ deleteUrl }}" title="Excluir Este Item"';
    cartItemTemplate += '            onclick="return AEC.remove(this, dataLayer)"';
    cartItemTemplate += '            class="btn-remove trans opacity-1-5" data-id="{{ id }}"';
    cartItemTemplate += '            data-name="{{ productName }}"';
    cartItemTemplate += '            data-price="{{ price }}"';
    cartItemTemplate += '            data-category="{{ category }}"';
    cartItemTemplate += '            data-brand="{{ brand }}"';
    cartItemTemplate += '            data-quantity="{{ quantity }}"';
    cartItemTemplate += '            data-variant="{{ variant }}"';
    cartItemTemplate += '            data-event="removeFromCart"';
    cartItemTemplate += '        >X</a>';
    cartItemTemplate += '    </div>';
    cartItemTemplate += '</li>';

    cartTotalTemplate += '<li class="last">';
    cartTotalTemplate += '    <span class="f-l d-b cor">Subtotal: <span class="bold fs-14 cor"><span class="price">{{ grandTotal }}</span></span></span>';
    cartTotalTemplate += '    <button type="button" title="Fechar Pedido" class="f-r bt2 d-b cor-sec bold tt-u radius td-n" onclick="setLocation(\'{{ baseUrl }}/checkout/onepage/\')">Fechar Pedido</button>';
    cartTotalTemplate += '    <span><a class="f-r" href="{{ baseUrl }}/checkout/cart/" title="Editar carrinho">Editar carrinho</a></span>';
    cartTotalTemplate += '</li>';


    var render = function(template, data) {
        var output = template;

        jQuery.each(data, function(key, value) {
            output = output.replace(new RegExp('{{ ' + key + ' }}', 'g'), value);
        });

        return jQuery(output);
    };

    var updateUser = function(authenticated) {
        $containerLoginDesktop.html(render(authenticated ? authenticatedUserTemplate : anonymousUserTemplate, {baseUrl: baseUrl}));
    };

    var updateUserMobile = function(authenticated) {
        $containerLoginMobile.html(render(authenticated ? authenticatedUserTemplateMobile : anonymousUserTemplateMobile, {baseUrl: baseUrl}));
    };

    var updateFormKey = function(formKey) {
        jQuery('form[action*=form_key]').each(function() {
            jQuery(this).attr('action', jQuery(this).attr('action').replace(/form_key\/([a-zA-Z0-9]+)/, 'form_key/' + formKey));
        });
        jQuery('input[name*=form_key]').each(function() {
            jQuery(this).attr('value', formKey);
        });
    };


    var updateCart = function(cart) {
        if (cart.items.length === 0) {
            return;
        }

        $cartContainer.addClass('cart-item');
        ///$cartIcon.append(render(cartCounterTemplate, {items: cart.itemsCount}));

        $cartList.attr('id', 'cart-sidebar');
        $cartList.addClass('mini-products-list');
        $cartList.empty();

        jQuery.each(cart.items, function(key, item) {
            $cartList.append(render(cartItemTemplate, {
                id: item.id,
                quantity: item.quantity,
                price: item.price,
                productName: item.name,
                productImage: item.productImage,
                productUrl: item.productUrl,
                deleteUrl: item.deleteUrl
            }));
        });

        $cartList.append(render(cartTotalTemplate, {
            baseUrl: baseUrl,
            grandTotal: cart.grandTotal
        }));
        
        jQuery(".cart-item > a").addClass('full-cart').html(`<span class="itens-count">${cart.itemsCount}</span>`);
    };

    jQuery.getJSON(baseUrl + '/session.php', function(data) {
        updateUser(data.isLoggedIn);
        updateUserMobile(data.isLoggedIn);
        updateFormKey(data.formKey);


        updateCart(data.cart);
    });
});