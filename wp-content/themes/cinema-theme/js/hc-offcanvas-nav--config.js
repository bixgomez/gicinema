jQuery(document).ready(function($) {

  $('#primary-menu').hcOffcanvasNav({
    disableAt: 783,
    customToggle: $('.toggle'),
    navTitle: 'Back',
    position: 'right',
    levelTitles: true,
    levelTitleAsBack: true
  });

});