<div class="wpabstracts container">
    <div id="reset_password_box" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="wpabstracts panel panel-default" >
            <div class="wpabstracts panel-heading">
                <div class="wpabstracts panel-title">
                    <?php echo apply_filters('wpabstracts_title_filter', __('Reset Password','wpabstracts'), 'reset_password'); ?>
                </div>
                <span class="wpabstracts small"><?php _e('Please enter your new password below.', 'wpabstracts');?></span>
            </div>
            <div style="padding-top:30px" class="wpabstracts panel-body" >
                <div style="display:none" id="wpabstracts-alert" class="wpabstracts alert alert-danger col-sm-12"></div>
                <form id="loginform" class="wpabstracts form-horizontal" role="form">
                    <?php // this prevent automated script for unwanted spam
                        if (function_exists('wp_nonce_field')){
                            wp_nonce_field( 'wpabstracts_reset_password_action', 'wpabstracts_reset_password_nonce' );
                        }
                    ?>
                    <div style="margin-bottom: 25px" class="wpabstracts input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="wpabstracts-password" type="password" class="wpabstracts form-control" name="password" placeholder="New Password">
                    </div>

                     <div style="margin-bottom: 25px" class="wpabstracts input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="wpabstracts-password-repeat" type="password" class="wpabstracts form-control" name="password-repeat" placeholder="Repeat Password">
                    </div>

                    <div style="margin-top:10px" class="wpabstracts form-group">
                        <div class="col-sm-12 controls center-block">
                            <a id="wpabstracts-resetpw" href="javascript:void(0)" class="btn btn-info"><?php echo apply_filters('wpabstracts_title_filter', __('Reset Password','wpabstracts'), 'reset_password'); ?></a>
                        </div>
                    </div>
                    <input id="wpabstracts-login" type="hidden" value="<?php echo $_GET['login'];?>">
                    <input id="wpabstracts-key" type="hidden" value="<?php echo $_GET['key'];?>">
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    (function($){

        $('#wpabstracts-resetpw').on('click', function(e){
            e.preventDefault();
            var pwd = $('#wpabstracts-password').val();
            var pwd_repeat = $('#wpabstracts-password-repeat').val();
            var login = $('#wpabstracts-login').val();
            var key = $('#wpabstracts-key').val();
            var nonce = $('#wpabstracts_reset_password_nonce').val();

            if(!pwd){
                $('#wpabstracts-alert').text('Please enter your new password.').show();
                return;
            }

            if(!pwd_repeat){
                $('#wpabstracts-alert').text('Please repeat your new password.').show();
                return;
            }

            var data = {
                action: 'wpa_resetpassword',
                nonce: nonce,
                password: pwd,
                password_repeat: pwd_repeat,
                user_login:login,
                user_key:key
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
