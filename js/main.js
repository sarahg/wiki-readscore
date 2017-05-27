$(document).ready(function() {
  'use strict';

  $('#search-category').submit(function() {

    var category = $('input#wiki-category').val().trim();
    var $results = $('#results');

    $(this).html('<div class="loader">Loading...</div>');

    console.log(category);

    $.ajax({
      type: 'POST',
      url: 'includes/app.php',
      data: 'category=' + category,
      cache: false,
      success: function(result) {
        $('.loader').hide();
        $('.results').html(result);
      }
    });

    return false;

  });

});
