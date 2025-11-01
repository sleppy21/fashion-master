jQuery(function($) {
    "use strict";
    
    const $document = $(document);
    const $body = $("body");
    const $footer = $("footer, .footer, .mobile-cart-footer__content");
    const $wrapper = $(".offcanvas-menu-wrapper");
    const $overlay = $(".offcanvas-menu-overlay");
    
    function openOffcanvas() {
        $wrapper.addClass("active");
        $overlay.addClass("active");
        $footer.hide();
        $body.addClass("offcanvas-active");
    }
    
    function closeOffcanvas() {
        $wrapper.removeClass("active");
        $overlay.removeClass("active");
        $footer.show();
        $body.removeClass("offcanvas-active");
    }
    
    $document.on("click", ".canvas__open", function(e) {
        e.preventDefault();
        e.stopPropagation();
        openOffcanvas();
    });

    $(".offcanvas__close").on("click", function(e) {
        e.preventDefault();
        closeOffcanvas();
    });

    $overlay.on("click", function(e) {
        e.preventDefault();
        closeOffcanvas();
    });

    $document.on("click", ".offcanvas-menu-toggle", function(e) {
        e.preventDefault();
        const $this = $(this);
        const $submenu = $this.next(".offcanvas-submenu");
        
        $this.toggleClass("active");
        $submenu.slideToggle(300);
        
        $(".offcanvas-menu-toggle").not($this).removeClass("active");
        $(".offcanvas-submenu").not($submenu).slideUp(300);
    });

    $("#offcanvas-user-profile").on("click", function() {
        closeOffcanvas();
        $("#userAccountModal").modal("show");
    });

    $("#favorites-link-mobile").on("click", function(e) {
        e.preventDefault();
        closeOffcanvas();
        $("#favoritesModal").modal("show");
    });

    $wrapper.on("transitionend", function() {
        if (!$wrapper.hasClass("active")) {
            $footer.show();
        }
    });
});
