/**
 * File calendar.js.
 *
 * Does stuff relevant to the monthly calendar template.
 */

document.addEventListener("DOMContentLoaded", function() {

  const templateUrl = template_url.templateUrl;
  const filmLinks = document.querySelectorAll('.film')
  const modalOuter = document.querySelector('.modal-outer')
  const modalInner = document.querySelector('.modal-inner')
  const modalContent = document.querySelector('.modal-content')
  const modalCloser = document.querySelector('.close-modal')

  filmLinks.forEach(a => a.addEventListener('click', handleFilmLinkClick))

  function handleFilmLinkClick(event) {
    const link = event.currentTarget
    const filmId = link.dataset.filmid
    // console.log(link)
    // console.log(filmId)
    ajaxText(filmId)
    // sendFilmToModal(filmId)
    openModal();
  }

  function ajaxText(filmId) {
    // console.log('ajax test')
    // console.log('filmId = ' + filmId)
    const data = {
      'action': 'cinema_theme_ajax_call', // the name of our PHP function
      'filmId': filmId,                   // pass the film id to it
    };
    jQuery.post(ajaxurl, data, function(response) {
      modalContent.innerHTML = response;
    });
  }

  function openModal() {
    modalContent.innerHTML = '';
    modalOuter.classList.add('open')
    document.body.style.overflow = 'hidden'; // Disable scrolling
  }

  function closeModal() {
    modalContent.innerHTML = '';
    modalOuter.classList.remove('open')
    document.body.style.overflow = ''; // Re-enable scrolling
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
