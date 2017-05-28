$(document).ready(function() {
  'use strict';

  $('#search-category').submit(function() {

    var category = $('input#wiki-category').val().trim();

    // Show the loading animation and disable the form while we await results.
    $('.loader-wrapper').html('<div class="loader">Loading...</div>');
    $('input').prop('disabled', true);

    //var start_time = new Date().getTime();

    $.ajax({
      type: 'POST',
      url: 'includes/app.php',
      data: 'category=' + category,
      cache: false,
      success: function(result) {

        $('.loader-wrapper').hide();
        $('.results').html(result);
        $('input').prop('disabled', false);

        // If the user runs another search, replace the result table with the loader.
        $('#search-category').submit(function() {
          $('.results').html('');
          $('.loader-wrapper').show();
        });

        //var request_time = new Date().getTime() - start_time;
        //console.log('Request time: ' + request_time + 'ms');
        
      }
    });

    return false;

  });

});
