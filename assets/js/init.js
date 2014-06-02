/**
 * SearchBlox JavaScript API
 * @author Rw
 * @year 2014
 * @version 1.0
 * License: Not for public use
 */
var SB = (function($) {

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
                    if (data.success == true && data.data.status == "OK") {
                        self.after('<p class="sb-success">Reboot Succeeded. You should wait a minute for droplet to up and running again.</p>');
                    } else {
                        self.after('<p class="sb-error">Reboot Failed.' + data.data + '</p>');
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