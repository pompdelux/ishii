
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
    $('.top-participate a').click(function(e){
        e.preventDefault();
        $this = $(this);

        FB.login(function(response) {
            if (response.authResponse) {
                //console.log('Welcome!  Fetching your information.... ');
                FB.api('/me', function(response) {
                    //console.log('Good to see you, ' + response.name + '.');
                    location.href = $this.attr('href');
                });
            } else {
                //console.log('User cancelled login or did not fully authorize.');
                $('.alerts:last').append($('' +
                            '  <div class="alert alert-error">' +
                            '    <div class="container">' +
                            '      <p><strong>Woops!</strong> Du skal godkende denne applikation på Facebook for at deltage.</p>' +
                            '    </div>' +
                            '  </div>').hide().fadeIn(500));
            }
        }, {scope: 'email'});
    });

    $('#add-picture #form_title').keyup(function(e){
        $('#preview h3').text($(this).val());
    });
    $('#add-picture #form_description').keyup(function(e){
        $('#preview p').text($(this).val());
    });

    if (jQuery.isFunction(jQuery.fn.uploadify)) {
        $('#form_picture').uploadify({
            'swf'       : '/fx/js/vendor/uploadify/uploadify.swf',
            'uploader'  : base_url+'index.php/upload',
            'multi'     : false,
            'buttonText': 'Find billede',
            'fileSizeLimit' : '2MB',
            'onFallback' : function() {
                alert('Flash was not detected.');
            },
            'onUploadSuccess' : function(file, data, response) {
                if (response) {
                    $('#preview img').attr('src', '/uploads/tmp/'+data).show();
                    $('#form_tmp_file').val(data);
                }
            }
        });
    }

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

        FB.ui(obj, function(response){ });
    });
});
