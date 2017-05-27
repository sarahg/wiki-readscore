$(document).ready(function() {
  'use strict';

  $('#search-category').submit(function() {

    var category = $('input#wiki-category').val().trim();
    var $results = $('#results');

    // @todo maybe leave the form in place and show the loader below instead
    $(this).html('<div class="loader">Loading...</div>');

    $.ajax({
      type: 'POST',
      url: 'includes/app.php',
      data: 'category=' + category,
      cache: false,
      success: function(result) {
        $('.loader').hide();
        $('.results').html(result);

        // @todo table sortability by title/score
        // @todo maybe add fancy classes to rows to have diff colors for score ranges (red/yellow/green)

      }
    });

    return false;

  });

});
