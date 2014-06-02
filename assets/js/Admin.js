/**
 * SearchBlox JavaScript API
 * @author Rw
 * @year 2014
 * @version 1.0
 * License: Not for public use
 */
var SB = (function($) {
     
    /**
     * SearchBlox Image Status
     */
    var DOImage = $('#DO_Image'),
        image_id = $('#do_image_id'),
        image_status = $('#image_status');
    
    $(document).on('click', '#DO_Image .button:eq(0)', function (e) {
        
        var self = $(this);

        $.ajax({
            type: 'POST',
            url: RWConfig.admin_url,
            data: {
                action: 'image_status',
                image_id: image_id.prop('value'),
            },
            beforeSend: function () {
                image_status.hide();
                self.prop('disabled', true);
            },
            success: function(data) {
                try {
                    if (data.success == true && data.data.status == "OK") {
                        image_status.text("Status OK").prop('class', 'sb-success');
                    } else {
                        image_status.text("Status Failed").prop('class', 'sb-error');
                    }
                    image_status.show();
                } catch(e) {
                    console.log(data);
                }
            },
            complete: function () {
                self.prop('disabled', false);
            }
        });
    });
    
}(jQuery));