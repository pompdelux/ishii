
  $('.toggle-picture.toggle-active').click(function(e){
    e.preventDefault();
    var $a = $(this);
    $.ajax({
      url : $a.attr('href'),
      dataType: 'json',
      async : false,
      success : function(response, textStatus, jqXHR) {
        if (response.status) {
          alert(response.message);
        }
      }
    });
  });

  $('.delete').click(function(e){
    e.preventDefault();
    var $a = $(this);
    if (confirm('Er du sikker på du vil slette denne?')) {
      $.ajax({
        url : $a.attr('href'),
        dataType: 'json',
        async : false,
        success : function(response, textStatus, jqXHR) {
          alert(response.message);
          if (response.status) {
            // Find det DOM element der skal fjernes
            $a.parent().parent().fadeOut(function() {
              $(this).remove();
            });
          }
        }
      });
    }
  });
