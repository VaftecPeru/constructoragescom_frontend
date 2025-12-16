(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner(0);


    // Initiate the wowjs
    new WOW().init();


    // Header carousel
    $(".header-carousel").owlCarousel({
        animateOut: 'fadeOut',
        items: 1,
        margin: 0,
        stagePadding: 0,
        autoplay: true,
        smartSpeed: 1000,
        dots: false,
        loop: true,
        nav: true,
        navText: [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
    });


    // Service-carousel
    $(".service-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 2000,
        center: false,
        dots: false,
        loop: true,
        margin: 25,
        nav: true,
        navText: [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
        responsiveClass: true,
        responsive: {
            0: {
                items: 1
            },
            576: {
                items: 1
            },
            768: {
                items: 2
            },
            992: {
                items: 2
            },
            1200: {
                items: 2
            }
        }
    });


    // testimonial carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        center: false,
        dots: true,
        loop: true,
        margin: 25,
        nav: false,
        navText: [
            '<i class="fa fa-angle-right"></i>',
            '<i class="fa fa-angle-left"></i>'
        ],
        responsiveClass: true,
        responsive: {
            0: {
                items: 1
            },
            576: {
                items: 1
            },
            768: {
                items: 1
            },
            992: {
                items: 1
            },
            1200: {
                items: 2
            }
        }
    });


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 1500, 'easeInOutExpo');
        return false;
    });


})(jQuery);

(function () {
    // Mapeo de rutas: español -> inglés
    var esEnMap = {
        'nosotros.html': 'about.html',
        'servicios.html': 'service.html',
        'contacto.html': 'contact.html',
        'testimonios.html': 'testimonial.html',
        'proyectos.html': 'projects.html',
        'index.html': 'index.html'
    };

    // Mapeo de rutas: inglés -> español
    var enEsMap = {
        'about.html': 'nosotros.html',
        'service.html': 'servicios.html',
        'contact.html': 'contacto.html',
        'testimonial.html': 'testimonios.html',
        'projects.html': 'proyectos.html',
        'index.html': 'index.html'
    };

    // Rutas en español (excluye index porque es compartido)
    var spanishOnlyRoutes = ['nosotros.html', 'servicios.html', 'contacto.html', 'testimonios.html', 'proyectos.html'];
    // Rutas en inglés (excluye index porque es compartido)
    var englishOnlyRoutes = ['about.html', 'service.html', 'contact.html', 'testimonial.html', 'projects.html'];

    // Obtener nombre de archivo actual
    var getCurrentPage = function () {
        var path = window.location.pathname;
        var page = path.substring(path.lastIndexOf('/') + 1) || 'index.html';
        return page;
    };

    // Verificar si la página actual es una ruta en español
    var isSpanishRoute = function (page) {
        return spanishOnlyRoutes.indexOf(page) !== -1;
    };

    // Verificar si la página actual es una ruta en inglés  
    var isEnglishRoute = function (page) {
        return englishOnlyRoutes.indexOf(page) !== -1;
    };

    // Actualizar todos los enlaces de navegación según el idioma
    var updateNavLinks = function (lang) {
        var links = document.querySelectorAll('a[href]');
        links.forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href) return;

            // Separar el hash (#) del href si existe
            var hashIndex = href.indexOf('#');
            var hash = hashIndex !== -1 ? href.substring(hashIndex) : '';
            var baseHref = hashIndex !== -1 ? href.substring(0, hashIndex) : href;

            // Solo actualizar si es un enlace interno del sitio
            if (baseHref.indexOf('http') === -1 && baseHref.indexOf('mailto') === -1 && baseHref.indexOf('tel') === -1) {
                var newHref = baseHref;

                if (lang === 'es') {
                    // Cambiar a rutas en español
                    if (enEsMap[baseHref]) {
                        newHref = enEsMap[baseHref];
                    }
                } else {
                    // Cambiar a rutas en inglés
                    if (esEnMap[baseHref]) {
                        newHref = esEnMap[baseHref];
                    }
                }

                if (newHref !== baseHref) {
                    link.setAttribute('href', newHref + hash);
                }
            }
        });
    };

    var setLang = function (lang, shouldRedirect) {
        if (lang === 'en') {
            document.documentElement.classList.add('lang-en-active');
        } else {
            document.documentElement.classList.remove('lang-en-active');
        }
        try { localStorage.setItem('lang', lang); } catch (e) { }

        var toggle = document.querySelector('.topbar .dropdown-toggle small');
        if (toggle) { toggle.innerHTML = '<i class="fas fa-globe-europe text-primary me-2"></i> ' + (lang === 'en' ? 'English' : 'Español'); }

        var emailInput = document.querySelector('#subscribe-email');
        if (emailInput) { emailInput.placeholder = (lang === 'en' ? 'Email to subscribe' : 'Correo electrónico para suscribirse'); }
        var nameInput = document.getElementById('name');
        if (nameInput) { nameInput.placeholder = (lang === 'en' ? 'Your name' : 'Tu nombre'); }
        var emailField = document.getElementById('email');
        if (emailField) { emailField.placeholder = (lang === 'en' ? 'Your email' : 'Tu correo'); }
        var phoneInput = document.getElementById('phone');
        if (phoneInput) { phoneInput.placeholder = (lang === 'en' ? 'Phone' : 'Teléfono'); }
        var projectInput = document.getElementById('project');
        if (projectInput) { projectInput.placeholder = (lang === 'en' ? 'Project' : 'Proyecto'); }
        var subjectInput = document.getElementById('subject');
        if (subjectInput) { subjectInput.placeholder = (lang === 'en' ? 'Subject' : 'Asunto'); }
        var messageInput = document.getElementById('message');
        if (messageInput) { messageInput.placeholder = (lang === 'en' ? 'Leave a message here' : 'Deja un mensaje aquí'); }

        // Actualizar enlaces de navegación
        updateNavLinks(lang);

        // Redirigir a la ruta correspondiente si se cambia el idioma manualmente
        if (shouldRedirect) {
            var currentPage = getCurrentPage();
            var newPage;

            if (lang === 'es' && enEsMap[currentPage]) {
                newPage = enEsMap[currentPage];
            } else if (lang === 'en' && esEnMap[currentPage]) {
                newPage = esEnMap[currentPage];
            }

            if (newPage && newPage !== currentPage) {
                window.location.href = newPage + window.location.hash;
            }
        }
    };

    // Cargar idioma guardado
    var saved = null;
    try { saved = localStorage.getItem('lang'); } catch (e) { }

    var currentPage = getCurrentPage();

    // Si hay idioma guardado, aplicarlo
    if (saved) {
        setLang(saved, false);
    } else {
        // Detectar idioma según la ruta actual
        if (isSpanishRoute(currentPage)) {
            setLang('es', false);
        } else if (isEnglishRoute(currentPage)) {
            setLang('en', false);
        } else {
            // Por defecto español
            setLang('es', false);
        }
    }

    // Eventos de clic para cambiar idioma
    document.addEventListener('click', function (e) {
        var t = e.target;
        if (t && t.closest('.lang-switch-en')) { e.preventDefault(); setLang('en', true); }
        if (t && t.closest('.lang-switch-es')) { e.preventDefault(); setLang('es', true); }
    });
})();


