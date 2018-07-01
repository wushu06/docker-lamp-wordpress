jQuery(document).ready(function ($) {

    $('ul.tabs li').click(function(){
        var tab_id = $(this).attr('data-tab');

        $('ul.tabs li').removeClass('current');
        $('.tab-content').removeClass('current');

        $(this).addClass('current');
        $("#"+tab_id).addClass('current');
    });



});
jQuery(document).ready( function($){

    var mediaUploader;

    $('#upload-button').on('click',function(e) {
        e.preventDefault();
        if( mediaUploader ){
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose a Profile Picture',
            button: {
                text: 'Choose Picture'
            },
            multiple: false
        });

        mediaUploader.on('select', function(){
            attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#profile-picture').val(attachment.url);
            $('#profile-picture-preview').css('background-image','url(' + attachment.url + ')');
        });

        mediaUploader.open();

    });

    $('#remove-picture').on('click',function(e){

    });



});
jQuery(document).ready( function($){
    function showDataTable(){
        var data = new FormData();
        var form = $('#importTable');
       var file_csv = $('input[type=file]',form )[0].files;
       /* $.each($('input[type=file]',form )[0].files, function (i, file) {
            console.log(file.name, file);
        });*/
        var form_data = new FormData(this);
        console.log(form_data);

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {

                string: file_csv,
                action: 'save_sort'

            },
            error: function( error ) {
                console.log('ERROr');
            },
            success:function(data){

               console.log('suc'+data);
            }
        });

    }

    $('#importTable').on('submit', function(e){
        e.preventDefault();
        showDataTable()
    });
});


