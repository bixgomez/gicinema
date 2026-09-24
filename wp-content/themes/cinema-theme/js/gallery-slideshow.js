(function () {
  'use strict';

  if (!window.PhotoSwipeLightbox || !window.PhotoSwipe) return;

  // Fill an 80vw by 80vh area, allowing small originals to enlarge.
  function galleryZoom(level) {
    return Math.min(
      level.panAreaSize.x * 0.8 / level.elementSize.x,
      level.panAreaSize.y * 0.8 / level.elementSize.y
    );
  }

  var lightbox = new PhotoSwipeLightbox({
    gallery: '.gallery',
    children: '.gallery-icon a[data-pswp-width][data-pswp-height]',
    pswpModule: PhotoSwipe,
    mainClass: 'cinema-photoswipe',
    initialZoomLevel: galleryZoom,
    secondaryZoomLevel: function (level) { return galleryZoom(level) * 2; },
    maxZoomLevel: function (level) { return galleryZoom(level) * 4; }
  });

  // Preserve captions entered in the WordPress gallery, when present.
  lightbox.on('uiRegister', function () {
    lightbox.pswp.ui.registerElement({
      name: 'gallery-caption',
      isButton: false,
      appendTo: 'root',
      onInit: function (element, pswp) {
        pswp.on('change', function () {
          var link = pswp.currSlide.data.element;
          var item = link ? link.closest('.gallery-item') : null;
          var caption = item ? item.querySelector('.gallery-caption') : null;
          element.textContent = caption ? caption.textContent.trim() : '';
          element.hidden = !element.textContent;
        });
      }
    });
  });

  lightbox.init();
}());
