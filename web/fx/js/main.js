
$(document).ready(function () {
    $('textarea[maxlength]').live('keyup blur', function() {
        // Store the maxlength and value of the field.
        var maxlength = $(this).attr('maxlength');
        var val = $(this).val();

        // Trim the field if it has content over the maxlength.
        if (val.length > maxlength) {
            $(this).val(val.slice(0, maxlength));
        }
    });
    // Facebook Me... :-)
    $('#add-picture button.submit').click(function(e){
        e.preventDefault();
        $form = $('#add-picture');
        $this = $(this);
        if($('#form_accept_conditions').is(':checked')){
            FB.login(function(response) {
                if (response.authResponse) {
                    //console.log('Welcome!  Fetching your information.... ');
                    FB.api('/me', function(response) {
                        //console.log('Good to see you, ' + response.name + '.');
                        $('#form_uuid').val(response.id);
                        $this.attr("disabled", "disabled");
                        $('#add-picture').submit();
                    });
                } else {
                    //console.log('User cancelled login or did not fully authorize.');
                }
            }, {scope: 'email'});
        }else{ // Not all fields are filled
            $('.alerts:last').append($('' +
                        '  <div class="alert alert-error">' +
                        '    <div class="container">' +
                        '      <p><strong>Woops!</strong> Du skal udfylde alle felter. Har du husket at godkende vores betingelser?</p>' +
                        '    </div>' +
                        '  </div>').hide().fadeIn(500));
        }
    });

    $('#add-picture').submit(function(e){
        if($(this).find('#form_uuid').val().length === 0){
            e.preventDefault();
        }
    });

    $('#add-picture #form_title').keyup(function(e){
        $('#preview h3').text($(this).val());
    });
    $('#add-picture #form_description').keyup(function(e){
        $('#preview p').text($(this).val());
    });

    $('#form_picture').uploadify({
        'swf'       : '/fx/js/vendor/uploadify/uploadify.swf',
        'uploader'  : base_url+'index.php/upload',
        'multi'     : false,
        'debug'     : true,
        'onFallback' : function() {
            alert('Flash was not detected.');
        },
        'onUploadSuccess' : function(file, data, response) {
            if(response){
                $('#preview img')
                    .attr('src', data).show();
            }
        }
    });
    // $('#form_picture').on('change',function(e){
    //     //if (this.files && this.files[0]) {
    //     if (e.target.files[0]) {
    //         var reader = new FileReader();

    //         reader.onload = function (e) {
    //             $('#preview img')
    //                 .attr('src', e.target.result).show();
    //         };

    //         reader.readAsDataURL(e.target.files[0]);
    //     }
    // });

    $('.facebook-share').click(function(e){
        e.preventDefault();
        $this = $(this);
        
        // calling the API ...
        var obj = {
            method: 'feed',
            link: $this.data('link'),
            picture: $this.data('picture'),
            name: $this.data('name'),
            caption: $this.data('caption'),
            description: $this.data('description')
        };

        FB.ui(obj, function(response){

        });
    });
});