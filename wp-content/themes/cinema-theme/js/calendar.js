/**
 * File calendar.js.
 *
 * Does stuff relevant to the monthly calendar template.
 */

document.addEventListener("DOMContentLoaded", function() {

  const templateUrl = template_url.templateUrl;
  const filmLinks = document.querySelectorAll('.film-title')
  const modalOuter = document.querySelector('.modal-outer')
  const modalInner = document.querySelector('.modal-inner')
  const modalContent = document.querySelector('.modal-content')
  const modalCloser = document.querySelector('.close-modal')

  filmLinks.forEach(a => a.addEventListener('click', handleFilmLinkClick))

  function handleFilmLinkClick(event) {
    const link = event.currentTarget
    const filmId = link.dataset.filmid
    modalOuter.classList.add('open')
    // console.log(link)
    // console.log(filmId)
    ajaxText(filmId)
    // sendFilmToModal(filmId)
  }

  function ajaxText(filmId) {
    console.log('ajax test')
    console.log('filmId = ' + filmId)
    const data = {
      'action': 'cinema_theme_ajax_call', // the name of our PHP function
      'filmId': filmId,                   // a relevant value we'd like to pass
    };
    jQuery.post(ajaxurl, data, function(response) {
      jQuery(modalContent).html(response);
    });
  }

  function closeModal() {
    modalOuter.classList.remove('open')
  }

  modalCloser.addEventListener('click', function() {
    closeModal();
  })

  modalOuter.addEventListener('click', function(e) {
    const isOutside = !e.target.closest('.modal-inner')
    isOutside && closeModal();
  })

  window.addEventListener('keydown', (event) => {
    event.key === 'Escape' && closeModal();
  })

});
