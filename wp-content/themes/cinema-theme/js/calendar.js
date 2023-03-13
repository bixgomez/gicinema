/**
 * File calendar.js.
 *
 * Does stuff relevant to the monthly calendar template.
 */

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
  console.log(filmId)
  sendFilmToModal(filmId)
}

function sendFilmToModal(filmId) {
  console.log(filmId)
  modalContent.innerHTML = `
    Hey, guess what?  This movie is ${filmId}.
  `
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
