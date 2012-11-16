
  $('.picture.toggle-active').click(function(e){
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