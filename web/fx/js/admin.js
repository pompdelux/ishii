
  $('.toggle-picture.toggle-active').click(function(e){
    e.preventDefault();
    var $a = $(this);
    $.ajax({
      url : $a.attr('href'),
      dataType: 'json',
      async : false,
      success : function(response, textStatus, jqXHR) {
        if (response.status) {
          alert('Yepeee!');
        }
      }
    });
  });
  $('.delete').click(function(e){
    e.preventDefault();
    var $a = $(this);
    if(confirm('Er du sikker på du vil slette denne?')){
      $.ajax({
        url : $a.attr('href'),
        dataType: 'json',
        async : false,
        success : function(response, textStatus, jqXHR) {
          alert(response.message);
          if (response.status) {
            $a.parent().parent().fadeOut(function() { // Find den DOM element der skal fjernes
              $(this).remove();
            });
          }
        }
      });
    }
  });