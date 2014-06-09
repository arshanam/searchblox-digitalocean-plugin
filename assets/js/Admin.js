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

    $(document).on('click', '#DO_Image .check-status, #DO_droplet .check-status', function (e) {
        var self = $(this),
            status_result = $(this).closest('p').find('.status-result'),
            status =  $(this).prev('.form-input-tip'),
            defaultData = {
                action: 'check_status',
                status_value: status.val(),
                status_id: status.prop('id')
            };
        
        if (status.data('order-id')) {
            defaultData.post_id = status.data('order-id');
        }
        
        $.ajax({
            type: 'POST',
            url: RWConfig.admin_url,
            data: defaultData,
            beforeSend: function () {
                status_result.hide();
                self.prop('disabled', true);
            },
            success: function(data) {
                try {
                    if (data.success == true && data.data.status == "OK") {
                        status_result.text("Status OK").prop('class', 'status-result sb-success');
                    } else {
                        status_result.text("Status Failed").prop('class', 'status-result sb-error');
                    }
                    status_result.show();
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