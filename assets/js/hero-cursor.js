/**
 * Custom cursor for the home page hero: a dot that tracks the mouse exactly
 * and a ring that trails behind it, growing/filling over the CTA button
 * (magnetic feel) and rippling on click.
 *
 * Only activates on fine-pointer devices (real mice - never touch, so
 * nothing breaks on phones/tablets) and only when the visitor hasn't asked
 * for reduced motion. Purely decorative: it never intercepts clicks, never
 * touches focus/keyboard behaviour, and the link underneath works exactly
 * as it would without this script.
 */
(function () {
    'use strict';

    var hero = document.querySelector('.hero-banner');
    if (!hero) return;
    if (!window.matchMedia('(pointer: fine)').matches) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var dot = document.createElement('div');
    dot.className = 'hero-cursor-dot';
    var ring = document.createElement('div');
    ring.className = 'hero-cursor-ring';
    hero.appendChild(dot);
    hero.appendChild(ring);
    hero.classList.add('has-custom-cursor');

    var mouseX = 0;
    var mouseY = 0;
    var ringX = 0;
    var ringY = 0;
    var visible = false;

    hero.addEventListener('mousemove', function (e) {
        var rect = hero.getBoundingClientRect();
        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
        dot.style.transform = 'translate(' + mouseX + 'px,' + mouseY + 'px)';
        if (!visible) {
            visible = true;
            dot.style.opacity = '1';
            ring.style.opacity = '1';
        }
    });

    hero.addEventListener('mouseleave', function () {
        visible = false;
        dot.style.opacity = '0';
        ring.style.opacity = '0';
    });

    (function trail() {
        ringX += (mouseX - ringX) * 0.15;
        ringY += (mouseY - ringY) * 0.15;
        ring.style.transform = 'translate(' + ringX + 'px,' + ringY + 'px)';
        window.requestAnimationFrame(trail);
    })();

    // Magnetic hover: the ring fills and the button drifts slightly toward
    // the cursor, snapping back on mouseleave.
    var magnets = hero.querySelectorAll('a');
    magnets.forEach(function (el) {
        el.addEventListener('mouseenter', function () {
            ring.classList.add('is-active');
        });
        el.addEventListener('mouseleave', function () {
            ring.classList.remove('is-active');
            el.style.transform = '';
        });
        el.addEventListener('mousemove', function (e) {
            var r = el.getBoundingClientRect();
            var relX = e.clientX - r.left - r.width / 2;
            var relY = e.clientY - r.top - r.height / 2;
            el.style.transform = 'translate(' + relX * 0.25 + 'px,' + relY * 0.25 + 'px)';
        });
    });

    // Click ripple.
    hero.addEventListener('click', function (e) {
        var rect = hero.getBoundingClientRect();
        var ripple = document.createElement('span');
        ripple.className = 'hero-cursor-ripple';
        ripple.style.left = (e.clientX - rect.left) + 'px';
        ripple.style.top = (e.clientY - rect.top) + 'px';
        hero.appendChild(ripple);
        ripple.addEventListener('animationend', function () {
            ripple.remove();
        });
    });
})();
