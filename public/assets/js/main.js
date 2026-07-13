/*
FILE: public/assets/js/main.js
RESPONSIBLE MEMBER: HYUGA
FEATURE: Animasi (AOS), navbar scroll, menu mobile, Swiper, FAQ accordion
*/
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // HYUGA - AOS init (animasi scroll halus ala shadcn)
    if (window.AOS) {
      AOS.init({ duration: 700, easing: 'ease-out-cubic', once: true, offset: 60 });
    }

    // HYUGA - Navbar shadow saat scroll
    const navbar = document.getElementById('navbar');
    const onScroll = () => {
      if (!navbar) return;
      if (window.scrollY > 8) navbar.classList.add('nav-scrolled');
      else navbar.classList.remove('nav-scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // HYUGA - Toggle menu mobile
    const navToggle = document.getElementById('navToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (navToggle && mobileMenu) {
      navToggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
    }

    // HYUGA - Swiper testimoni
    if (window.Swiper && document.querySelector('.testi-swiper')) {
      new Swiper('.testi-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: { delay: 4000, disableOnInteraction: false },
        pagination: { el: '.testi-pagination', clickable: true },
        breakpoints: { 640: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } },
      });
    }

    // HYUGA - FAQ accordion
    document.querySelectorAll('[data-faq-toggle]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const item = btn.closest('[data-faq]');
        const body = item.querySelector('[data-faq-body]');
        const icon = btn.querySelector('i');
        const isOpen = item.classList.toggle('faq-open');
        if (isOpen) {
          body.style.maxHeight = body.scrollHeight + 'px';
          if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
          body.style.maxHeight = '0px';
          if (icon) icon.style.transform = 'rotate(0)';
        }
      });
    });
  });
})();
