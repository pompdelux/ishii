
$(document).ready(function () {
    // Facebook Me... :-)
    $('.facebook-connect').click(function(e){
        e.preventDefault();
        $this = $(this);
        FB.login(function(response) {
            if (response.authResponse) {
                console.log('Welcome!  Fetching your information.... ');
                FB.api('/me', function(response) {
                    console.log('Good to see you, ' + response.name + '.');
                    $('#form_uuid').val(response.id);
                    $('input[type="submit"]').removeAttr('disabled');
                    $this.hide();
                });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'email'});
    });
    $('#add-picture').submit(function(e){
        if($(this).find('#form_uuid').val().length === 0){
            e.preventDefault();
        }
    });
});