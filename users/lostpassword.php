<div class="wpabstracts container">
    <div id="reset_password_box" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="wpabstracts panel panel-default" >
            <div class="wpabstracts panel-heading">
                <div class="wpabstracts panel-title">
                    <?php echo apply_filters('wpabstracts_title_filter', __('Forgot Password','wpabstracts'), 'forgot_password'); ?>
                </div>
                <span class="wpabstracts small"><?php _e('Please enter your email address and we will send you instructions to reset your password.', 'wpabstracts');?></span>
            </div>
            <div style="padding-top:30px" class="wpabstracts panel-body" >
                <div style="display:none" id="wpabstracts-alert" class="wpabstracts alert alert-danger col-sm-12"></div>
                <form id="loginform" class="wpabstracts form-horizontal" role="form">
                    <?php // this prevent automated script for unwanted spam
                        if (function_exists('wp_nonce_field')){
                            wp_nonce_field( 'wpabstracts_lost_password_action', 'wpabstracts_lost_password_nonce' );
                        }
                    ?>
                    <div style="margin-bottom: 25px" class="wpabstracts input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="wpabstracts-email" type="text" class="wpabstracts form-control" name="email" value="" placeholder="Username or Email Address">
                    </div>
                    <div style="margin-top:10px" class="wpabstracts form-group">
                        <div class="col-sm-12 controls">
                            <a id="wpabstracts-resetpw" href="javascript:void(0)" class="btn btn-info"><?php echo apply_filters('wpabstracts_title_filter', __('Get New Password','wpabstracts'), 'get_password'); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    (function($){

        $('#wpabstracts-resetpw').on('click', function(e){
            e.preventDefault();
            var username = $('#wpabstracts-email').val();
            var nonce = $('#wpabstracts_lost_password_nonce').val();

            if(!username){
                $('#wpabstracts-alert').text(__('Please enter your username or email address to reset your password.', 'wpabstracts')).show();
                return;
            }

            var data = {
                action: 'wpa_lostpassword',
                nonce: nonce,
                user_login: username
            };

            $.post(wpabstracts.ajaxurl, data).done(function(resp){
                var response = JSON.parse(resp);
                if(response.success){
                    $('#wpabstracts-alert').removeClass('alert-danger').addClass('alert-info').text(response.message).show();
                }else{
                    $('#wpabstracts-alert').text(response.message).show();
                }
            });

        });
    })(jQuery);
</script>
