jQuery(document).ready(function($) {
    $(document).on("click", "#upload_image", function() {

        jQuery.data(document.body, 'prevElement', $(this).prev());

        window.send_to_editor = function(html) {
            var imgurl = jQuery(html).attr('src');
            var inputText = jQuery.data(document.body, 'prevElement');
			console.log('imgurl:' + imgurl);
			console.log('inputText:' + inputText);
            if(inputText != undefined && inputText != '')
            {
                inputText.val(imgurl);
            }

            tb_remove();
        };

        tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        return false;
    });
});