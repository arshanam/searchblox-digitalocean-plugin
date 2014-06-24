/**
 * SearchBlox JavaScript API
 * @author Rw
 * @year 2014
 * @version 1.0
 * License: Not for public use
 */
var SB = (function($) {
    $("#order_comments").prop('placeholder', '');
    /**
     * Droplet rebooter
     */
    $(document).on('click', 'input[name=reboot]', function (e) {
        
        var self = $(this);

        $.ajax({
            type: 'POST',
            url: RWConfig.admin_url,
            data: {
                action: 'droplet_reboot',
                droplet_id: self.data('droplet-id'),
                droplet_token: self.data('droplet-token'),
                _: Date.now()
            },
            beforeSend: function () {
                self.prop('disabled', true);
                self.next('.sb-success, .sb-error').remove();
            },
            success: function(data) {
                try {
                    if (data.success == true && !data.data.id) {
                        self.after('<p class="sb-success">Reboot Successful. Please wait for 5 minutes before accessing the admin console.</p>');
                    } else {
                        self.after('<p class="sb-error">Reboot Failed.' + (data.data.message.replace('Droplet', 'Server')) + '</p>');
                    }
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