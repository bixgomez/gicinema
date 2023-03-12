/**
 * File calendar.js.
 *
 * Does stuff relevant to the monthly calendar template.
 */

const filmLinks = document.querySelectorAll('a.film-title')
const modalOuter = document.querySelector('.modal-outer')
const modalInner = document.querySelector('.modal-inner')
const modalCloser = document.querySelector('.close-modal')

filmLinks.forEach(a => a.addEventListener('click', handleFilmLinkClick))

function handleFilmLinkClick(event) {
  const link = event.currentTarget
  const filmId = link.dataset.filmid
  const filmLink = link.dataset.filmlink
  modalOuter.classList.add('open')
  // console.log(link)
  // console.log(filmId)
  // console.log(filmLink)
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
