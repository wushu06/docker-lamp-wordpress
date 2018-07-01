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


    $(".upload-file").on('click', function(e){
        e.preventDefault();
        var file_csv1 = $('#locationUpload1').prop('files')[0];
        var file_csv2 = $('#locationUpload2').prop('files')[0];
        var file_csv3 = $('#locationUpload3').prop('files')[0];
        var file_csv4 = $('#locationUpload4').prop('files')[0];
        var file_csv5 = $('#locationUpload5').prop('files')[0];
       // var file_csv5 = $('#locationUpload6').prop('files')[0];
        var btn = $(this);
        var file_csv;


        if (file_csv1  !==  undefined ) {

            file_csv = file_csv1;

        }else if  (file_csv2  !== undefined ) {

            file_csv = file_csv2;
        }else if  (file_csv3  !== undefined ) {

            file_csv = file_csv3;
        }else if  (file_csv4 !== undefined ) {

            file_csv = file_csv4;
        }else if  (file_csv5 !== undefined ) {

            file_csv = file_csv5;
        }







        var form_data = new FormData();

        form_data.append('file', file_csv);
        form_data.append('action', 'hook_me_up_upload_csv_file');
        form_data.append('security', WP_JOB_LISTING.security);






        $.ajax({
            type: 'POST',
            url: ajaxurl,

            contentType: false,
            processData: false,
            data: form_data,

            success:function(response){
                if( true === response.success ) {

                    $('.output-table').append(response.data);
                    btn.next().removeClass('hidden');

                    // console.log(response.data);
                } else {

                    console.log('failed!');
                }

            }
        });
    });
});

