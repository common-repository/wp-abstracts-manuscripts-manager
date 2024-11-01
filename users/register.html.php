<script>
	jQuery(document).ready(function() {

		var _formData = JSON.stringify( <?php echo $form_data; ?> );

		var _layoutTemplates = {
			default: function(field, label, help, data) {
				var optional = data.hasOwnProperty("required") ? true : false;
				if (!data.hasOwnProperty("required")) {
					field = jQuery(field).addClass('optional');
				}
				return jQuery('<div/>')
					.addClass('wpabstracts ' + data.layout)
					.attr('id', 'row-' + data.id)
					.append(label, help, field);
			}
		};

		jQuery('#registration_form_container').formRender({
			formData: _formData,
			layoutTemplates: _layoutTemplates
		});

		var pw_rules =
			'<?php echo json_encode($settings->password_rules);?>';

		wpabstracts_init_registration(pw_rules);

		var tempForm = '<?php echo json_encode($_POST);?>';

		if (tempForm) {
			var formData = JSON.parse(tempForm);
			jQuery.each(formData, function(key, value) {
				jQuery('input[name="' + key + '"]').val(value);
			});
		}

		jQuery('#wpabs_useraddon_register').on('click', function(e) {
			var errors = false;

			jQuery(
					"#wpabstracts_register_form input, #wpabstracts_register_form select, #wpabstracts_register_form textarea"
					)
				.each(function() {
					if (jQuery(this).attr('required')) {
						errors = wp_field_validate(jQuery(this).attr('id'), errors);
					}
				});

			if (errors) {
				alertify.error(wpabstracts.required_fields);
			} else {
				jQuery('#wpabstracts_register_form').submit();
			}
		});

		function wpabstracts_init_registration(rules) {

			var pRules = JSON.parse(rules);

			var min_pwd = pRules['min_pwd'];
			var max_pwd = pRules['max_pwd'];
			var one_number = pRules['number'];
			var one_uppercase = pRules['uppercase'];
			var one_lowercase = pRules['lowercase'];
			var is_special = pRules['special'];

			var ruleHtml = "";
			ruleHtml += "<div id='pwd_length'>Must be between " + min_pwd + " and " + max_pwd +
				" characters</div>";
			if (one_number) {
				ruleHtml += "<div id='pwd_number'>Must have at least one number</div>";
			}
			if (one_uppercase) {
				ruleHtml += "<div id='pwd_uppercase'>Must have at least one uppercase</div>";
			}
			if (one_lowercase) {
				ruleHtml += "<div id='pwd_lowercase'>Must have at least one lowercase</div>";
			}
			if (is_special) {
				ruleHtml += "<div id='pwd_special'>Must have at least one special character</div>";
			}
			ruleHtml += "<div id='pwd_match'>Must match repeat password</div>";

			function _validate_pwd(pswd) {

				var errors = false;

				if (pswd.length >= min_pwd && pswd.length <= max_pwd) {
					jQuery('#pwd_length').removeClass('text-danger').addClass('text-success');
				} else {
					jQuery('#pwd_length').removeClass('text-success').addClass('text-danger');
					errors = true;
				}

				if (one_number) {
					if (pswd.match(/\d/)) {
						jQuery('#pwd_number').removeClass('text-danger').addClass('text-success');
					} else {
						jQuery('#pwd_number').removeClass('text-success').addClass('text-danger');
						errors = true;
					}
				}

				if (one_uppercase) {
					if (pswd.match(/[A-Z]/)) {
						jQuery('#pwd_uppercase').removeClass('text-danger').addClass('text-success');
					} else {
						jQuery('#pwd_uppercase').removeClass('text-success').addClass('text-danger');
						errors = true;
					}
				}

				if (one_lowercase) {
					if (pswd.match(/[a-z]/)) {
						jQuery('#pwd_lowercase').removeClass('text-danger').addClass('text-success');
					} else {
						jQuery('#pwd_lowercase').removeClass('text-success').addClass('text-danger');
						errors = true;
					}
				}

				if (is_special) {
					if (pswd.match(/[!@#\$%\^\&*\)\(+=._-]/)) {
						jQuery('#pwd_special').removeClass('text-danger').addClass('text-success');
					} else {
						jQuery('#pwd_special').removeClass('text-success').addClass('text-danger');
						errors = true;
					}
				}

				if (jQuery('#password').val() == jQuery('#password_repeat').val()) {
					jQuery('#pwd_match').removeClass('text-danger').addClass('text-success');
				} else {
					jQuery('#pwd_match').removeClass('text-success').addClass('text-danger');
					errors = true;
				}

				return errors;

			}

			jQuery('#wpabstracts_register_form #password, #wpabstracts_register_form #password_repeat').popover({
				'html': true,
				'placement': function() {
					return 'top';
				},
				'content': function() {
					return ruleHtml;
				},
				'title': function() {
					return "Password Requirement";
				},
				'trigger': 'focus'
			});

			jQuery('#wpabstracts_register_form #password, #wpabstracts_register_form #password_repeat').on('keyup',
				function() {
					_validate_pwd(jQuery('#wpabstracts_register_form #password').val());
				});

			jQuery('#wpabstracts_register_form #password, #wpabstracts_register_form #password_repeat').on('blur',
				function() {
					jQuery('#wpabstracts_register_form #password, #wpabstracts_register_form #password_repeat')
						.popover('hide');
				});

			jQuery('#wpabstracts_register_form #password, #wpabstracts_register_form #password_repeat').on(
				'shown.bs.popover',
				function() {
					_validate_pwd(jQuery('#wpabstracts_register_form #password').val());
				});

		}

	});
</script>

<div class="wpabstracts container-fluid">

	<form method="post" enctype="multipart/form-data" id="wpabstracts_register_form">

		<div class="wpabstracts panel panel-default">
			<div class="wpabstracts panel-heading">
				<h5><?php echo apply_filters('wpabstracts_title_filter', __('Login Information', 'wpabstracts'), 'login_information');?>
				</h5>
			</div>
			<div class="wpabstracts panel panel-body">
				<div class="wpabstracts form-group col-sm-4 col-xs-12 required">
					<label class="wpabstracts control-label" for="email"><?php _e('Email', 'wpabstracts'); ?></label>
					<input type="text" name="email" id="email" class="wpabstracts form-control" placeholder="Email"
						required>
					<span class="wpabstracts help-block"><?php _e('Please enter a valid email address.', 'wpabstracts'); ?></span>
				</div>
				<div class="wpabstracts form-group col-sm-4 col-xs-12 required">
					<label class="wpabstracts control-label" for="password"><?php _e('Password', 'wpabstracts'); ?></label>
					<input type="password" required name="password" id="password" autocomplete="off"
						class="wpabstracts form-control" placeholder="Password" required>
					<span class="wpabstracts help-block"><?php _e('Please enter a password.', 'wpabstracts'); ?></span>
				</div>
				<div class="wpabstracts form-group col-sm-4 col-xs-12 required">
					<label class="wpabstracts control-label" for="password_repeat"><?php _e('Repeat Password', 'wpabstracts'); ?></label>
					<input type="password" required name="password_repeat" id="password_repeat" autocomplete="off"
						class="wpabstracts form-control" placeholder="Repeat Password" required>
					<span class="wpabstracts help-block"><?php _e('Please repeat your password.', 'wpabstracts'); ?></span>
				</div>
			</div>
		</div>

		<div class="wpabstracts panel panel-default">
			<div class="wpabstracts panel-heading">
				<h5><?php echo apply_filters('wpabstracts_title_filter', __('Account Information', 'wpabstracts'), 'account_information');?>
				</h5>
			</div>
			<div class="wpabstracts panel panel-body">
				<div id="registration_form_container"></div>
			</div>
			<?php if (get_option('wpabstracts_captcha')) { ?>
			<div class="wpabstracts panel panel-body">
				<div class="wpabstracts form-group col-xs-12 col-sm-8 col-md-6">
					<?php wpabstracts_show_captcha(); ?>
				</div>
			</div>
			<?php } ?>
			<div class="wpabstracts panel panel-body">
				<div class="wpabstracts form-group col-xs-12 col-sm-4">
					<a id="wpabs_useraddon_register" class="wpabstracts btn btn-primary btn-block"><?php _e('Register', 'wpabstracts'); ?></a>
				</div>
			</div>
		</div>

	</form>

</div>