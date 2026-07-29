/**
 * JNC GreaseCycling Landing Page JS
 * Handles responsive interactions, navigation transitions, and viewport scroll active state tracking.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Smooth Scroll Hash Nav Offset Adjuster
    const navLinks = document.querySelectorAll('.nav-menu .nav-link');
    const headerHeight = document.querySelector('.header').offsetHeight;

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerHeight;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // 2. Dynamic Nav Intersection Observer (Highlights active menu link when scrolled to section)
    const sections = document.querySelectorAll('section[id]');
    const observerOptions = {
        root: null,
        rootMargin: `-${headerHeight}px 0px -40% 0px`,
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${id}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(section => {
        observer.observe(section);
    });

    console.log('JNC GreaseCycling app core initialized successfully.');
});
