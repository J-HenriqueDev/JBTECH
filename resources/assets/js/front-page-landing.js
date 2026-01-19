/**
 * Main - Front Pages
 */
'use strict';

(function () {
  const nav = document.querySelector('.layout-navbar'),
    heroAnimation = document.getElementById('hero-animation'),
    animationImg = document.querySelectorAll('.hero-dashboard-img'),
    animationElements = document.querySelectorAll('.hero-elements-img'),
    swiperLogos = document.getElementById('swiper-clients-logos'),
    swiperReviews = document.getElementById('swiper-reviews'),
    swiperTechTrends = document.getElementById('swiper-tech-trends'),
    landingHero = document.getElementById('landingHero'),
    testimonialSwiper = document.querySelector('.testimonialSwiper'),
    ReviewsPreviousBtn = document.getElementById('reviews-previous-btn'),
    ReviewsNextBtn = document.getElementById('reviews-next-btn'),
    ReviewsSliderPrev = document.querySelector('.swiper-button-prev'),
    ReviewsSliderNext = document.querySelector('.swiper-button-next'),
    priceDurationToggler = document.querySelector('.price-duration-toggler'),
    priceMonthlyList = [].slice.call(document.querySelectorAll('.price-monthly')),
    priceYearlyList = [].slice.call(document.querySelectorAll('.price-yearly'));

  // Hero
  const mediaQueryXL = '1200';
  const width = screen.width;
  if (width >= mediaQueryXL && heroAnimation) {
    heroAnimation.addEventListener('mousemove', function parallax(e) {
      animationElements.forEach(layer => {
        layer.style.transform = 'translateZ(1rem)';
      });
      animationImg.forEach(layer => {
        let x = (window.innerWidth - e.pageX * 2) / 100;
        let y = (window.innerHeight - e.pageY * 2) / 100;
        layer.style.transform = `perspective(1200px) rotateX(${y}deg) rotateY(${x}deg) scale3d(1, 1, 1)`;
      });
    });
    nav.addEventListener('mousemove', function parallax(e) {
      animationElements.forEach(layer => {
        layer.style.transform = 'translateZ(1rem)';
      });
      animationImg.forEach(layer => {
        let x = (window.innerWidth - e.pageX * 2) / 100;
        let y = (window.innerHeight - e.pageY * 2) / 100;
        layer.style.transform = `perspective(1200px) rotateX(${y}deg) rotateY(${x}deg) scale3d(1, 1, 1)`;
      });
    });

    heroAnimation.addEventListener('mouseout', function () {
      animationElements.forEach(layer => {
        layer.style.transform = 'translateZ(0)';
      });
      animationImg.forEach(layer => {
        layer.style.transform = 'perspective(1200px) scale(1) rotateX(0) rotateY(0)';
      });
    });
  }

  // swiper carousel
  // Customers reviews
  // -----------------------------------
  let swiperReviewsInstance;
  if (swiperReviews) {
    swiperReviewsInstance = new Swiper(swiperReviews, {
      slidesPerView: 1,
      spaceBetween: 10,
      grabCursor: true,
      autoplay: {
        delay: 0,
        disableOnInteraction: false
      },
      speed: 3000,
      loop: true,
      breakpoints: {
        1400: {
          slidesPerView: 4,
          spaceBetween: 20
        },
        1200: {
          slidesPerView: 3,
          spaceBetween: 20
        },
        992: {
          slidesPerView: 2,
          spaceBetween: 20
        }
      },
      on: {
        init: function () {
          this.wrapperEl.style.transitionTimingFunction = 'linear';
        }
      }
    });
  }

  // Reviews slider next and previous
  // -----------------------------------
  // Add click event listener to next button
  if (ReviewsNextBtn && swiperReviewsInstance) {
    ReviewsNextBtn.addEventListener('click', function () {
      swiperReviewsInstance.slideNext();
    });
  }
  if (ReviewsPreviousBtn && swiperReviewsInstance) {
    ReviewsPreviousBtn.addEventListener('click', function () {
      swiperReviewsInstance.slidePrev();
    });
  }

  // Review client logo
  // -----------------------------------
  if (swiperLogos) {
    new Swiper(swiperLogos, {
      slidesPerView: 2,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false
      },
      breakpoints: {
        992: {
          slidesPerView: 5
        },
        768: {
          slidesPerView: 3
        }
      }
    });
  }

  // Tech Trends Swiper
  // -----------------------------------
  if (swiperTechTrends) {
    new Swiper(swiperTechTrends, {
      slidesPerView: 1,
      spaceBetween: 20,
      pagination: {
        el: '.swiper-pagination',
        clickable: true
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
          spaceBetween: 20
        },
        768: {
          slidesPerView: 3,
          spaceBetween: 30
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 30
        }
      },
      autoplay: {
        delay: 3000,
        disableOnInteraction: false
      },
      loop: true
    });
  }

  // Landing Hero Swiper
  // -----------------------------------
  if (landingHero) {
    new Swiper(landingHero, {
      slidesPerView: 1,
      centeredSlides: true,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev'
      }
    });
  }

  // Hero Testimonial Swiper
  // -----------------------------------
  if (testimonialSwiper) {
    new Swiper(testimonialSwiper, {
      slidesPerView: 1,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true
      }
    });
  }

  // Pricing Plans
  // -----------------------------------
  document.addEventListener('DOMContentLoaded', function (event) {
    if (priceDurationToggler) {
      function togglePrice() {
        if (priceDurationToggler.checked) {
          // If checked
          priceYearlyList.map(function (yearEl) {
            yearEl.classList.remove('d-none');
          });
          priceMonthlyList.map(function (monthEl) {
            monthEl.classList.add('d-none');
          });
        } else {
          // If not checked
          priceYearlyList.map(function (yearEl) {
            yearEl.classList.add('d-none');
          });
          priceMonthlyList.map(function (monthEl) {
            monthEl.classList.remove('d-none');
          });
        }
      }
      // togglePrice Event Listener
      togglePrice();

      priceDurationToggler.onchange = function () {
        togglePrice();
      };
    }
  });
})();
