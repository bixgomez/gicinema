// TODO: Make this work.

jQuery(document).ready(function($) {
  // Target the repeater field by its unique field key or CSS class
  var repeaterField = $('.acf-field-617b2f8e4b8c4');

  // Hide or disable the delete button for each row
  // repeaterField.find('.acf-row-handle.remove').hide();
  // repeaterField.find('.acf-icon.-plus').hide();
  repeaterField.find('.acf-icon.-minus').hide();
});