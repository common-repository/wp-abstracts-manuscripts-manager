<?php
// don't show login on dashboard or register page if logged in.
global $post;
$is_dashboard = has_shortcode($post->post_content, 'wpabstracts');
$is_register = has_shortcode($post->post_content, 'wpabstracts_register');

if(($is_dashboard || $is_register) && is_user_logged_in()){
	return;
}
if(is_user_logged_in()){
	$dashboard_link = "<a href=" . wpabstracts_get_dashboard() . ">" . apply_filters('wpabstracts_title_filter', __('Dashboard','wpabstracts'), 'dashboard') . "</a>";
	$logout_link = "<a href=" . wp_logout_url(get_permalink()) . ">" . apply_filters('wpabstracts_title_filter', __('Logout','wpabstracts'), 'logout_btn') . "</a>";
	wpabstracts_show_message(__("You're already logged in. You can go to your $dashboard_link or $logout_link.", "wpabstracts"), 'alert-info');
	return;
}
?>
<div class="wpabstracts container">
	<div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
		<div style="padding: 10px 0;">
			<?php echo apply_filters('wpabstracts_title_filter', __('Please sign in for your conference participation', 'wpabstracts'), 'sign_in_help'); ?>
		</div>
		<div class="wpabstracts panel panel-default" >
			<div class="wpabstracts panel-heading">
				<div class="wpabstracts panel-title">
					<?php echo apply_filters('wpabstracts_title_filter', __('Sign In','wpabstracts'), 'sign_in'); ?>
				</div>
				<div style="float:right; font-size: 80%; position: relative; top:-18px"><?php echo wpabstracts_lostpassword_url(); ?></div>
			</div>

			<div style="padding-top:30px" class="wpabstracts panel-body" >

				<div style="display:none" id="wpabstracts-alert" class="wpabstracts alert alert-danger col-sm-12"></div>

				<form id="loginform" class="wpabstracts form-horizontal" role="form">

					<div style="margin-bottom: 25px" class="wpabstracts input-group">
						<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
						<input id="wpabstracts-username" type="text" class="wpabstracts form-control" name="username" value="" placeholder="username or email">
					</div>

					<div style="margin-bottom: 25px" class="wpabstracts input-group">
						<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
						<input id="wpabstracts-password" type="password" class="wpabstracts form-control" name="password" placeholder="password">
					</div>

					<?php if(get_option('wpabstracts_captcha')){ ?>
						<div class="wpabstracts input-group">
							<?php if(get_option('wpabstracts_captcha')){ ?>
								<?php wpabstracts_show_captcha(); ?>
							<?php } ?>
						</div>
					<?php } ?>

					<div style="margin-top:10px" class="wpabstracts form-group">
						<div class="col-sm-12 controls">
							<button id="wpabstracts-login" class="btn btn-info btn-block"><?php echo apply_filters('wpabstracts_title_filter', __('Login','wpabstracts'), 'login_btn');?> <i class="fa fa-spinner fa-spin isloading"></i></button>
						</div>
					</div>

					<div class="wpabstracts form-group">
						<div class="wpabstracts col-xs-12">
							<input type="checkbox" id="wpabstracts-remember" value="forever"> <?php echo apply_filters('wpabstracts_title_filter', __('Remember Me','wpabstracts'), 'remember_me');?>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-12 control">
							<div style="border-top: 1px solid #ccc; padding-top:15px; font-size:14px">
								<?php echo wpabstracts_register_url(); ?>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">

jQuery( document ).ready(function() {
    var rememberme = localStorage.getItem('wpa_remember');
	if(rememberme){
		var rem = JSON.parse(rememberme);
		jQuery('#wpabstracts-remember').attr('checked', rem.remember);
		if(rem.remember){
			jQuery('#wpabstracts-username').val(rem.username);
		}
	}

	var manageLoader = function(show){
		if(show){
			jQuery('#wpabstracts-login i').removeClass('isloading');
		}else {
			jQuery('#wpabstracts-login i').addClass('isloading');
		}
	};

	jQuery('#wpabstracts-login').on('click', function(e){
		e.preventDefault();
		var username = jQuery('#wpabstracts-username').val();
		var password = jQuery('#wpabstracts-password').val();
		var captcha = jQuery('#captcha_input').val();
		var hash = jQuery('#captcha_hash').val();
		var remember = jQuery('#wpabstracts-remember').is(':checked');

		if(!username || !password){
			jQuery('#wpabstracts-alert').text(wpabstracts.sign_in_msg).show();
			return;
		}

		if(hash && !captcha) {
			jQuery('#wpabstracts-alert').text(wpabstracts.captcha_required).show();
			return;
		}

		var rememberMe = {'remember':remember, 'username':username};
		localStorage.setItem('wpa_remember', JSON.stringify(rememberMe));

		manageLoader(true);

		var ajax_data = {
			action: 'wpa_login',
			log: username,
			pwd: password,
			captcha_input: captcha,
			captcha_hash: hash,
			rememberme: remember
		};

		jQuery.ajax({
			url: wpabstracts.ajaxurl,
			type: 'POST',
			data: ajax_data,
			success: function(data){
				var response = JSON.parse(data);
				if(response.success){
					jQuery('#wpabstracts-alert')
					.removeClass('alert-danger')
					.addClass('alert-info')
					.text(response.message)
					.show();
					window.location.href = response.redirect;
				}else{
					jQuery('#wpabstracts-alert').text(response.message).show();
					manageLoader(false);
				}
			},
			error: function (xhr, status) {
				manageLoader(false);
				console.log('Login Ajax Error: ', xhr);
			}
		});
	});
});

</script>
