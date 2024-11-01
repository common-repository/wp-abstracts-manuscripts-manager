/**
 * Kevon Adonis
 * Copyright 2016
 * http://www.wpabstracts.com/addons/users
 */

function wpabstracts_register(){
	 var errors = false;

	 jQuery("#wpabstracts_register_form input[type=text], #wpabstracts_register_form select").each(function(){
		  errors = wp_field_validate(jQuery(this).attr('id'), errors);
	 });

	 jQuery("#wpabstracts_register_form input[type=radio], #wpabstracts_register_form input[type=checkbox]").each(function(){
		  errors = wp_option_validate(jQuery(this).attr('name'), errors);
	 });

	 if(errors) {
		  alertify.error(front_ajax.fillin);
	 } else {
		  jQuery('#wpabstracts_register_form').submit();
	 }
}

function wpabstracts_populate_form(user_data, user_login){

	 $("#wpabstracts_register_form :input").each(function(){
		  $(this).val(user_data[$(this).attr('name')]);
	 });

	 $("#email").val(user_login.username);

	 $("#wpabstracts_register_form").append('<input type="hidden" value="' + user_login.ID + '">');

};

function wpabstracts_init_registration(rules){

	 pRules = JSON.parse(rules);

	 var min_pwd = pRules['min_pwd'];
	 var max_pwd = pRules['max_pwd'];
	 var one_number = pRules['number'];
	 var one_uppercase = pRules['uppercase'];
	 var one_lowercase = pRules['lowercase'];
	 var is_special = pRules['special'];

	 var ruleHtml = "";
	 ruleHtml += "<div id='pwd_length'>Must be between "+min_pwd+" and "+max_pwd+" characters</div>";
	 if(one_number){
		  ruleHtml += "<div id='pwd_number'>Must have at least one number</div>";
	 }
	 if(one_uppercase){
		  ruleHtml += "<div id='pwd_uppercase'>Must have at least one uppercase</div>";
	 }
	 if(one_lowercase){
		  ruleHtml += "<div id='pwd_lowercase'>Must have at least one lowercase</div>";
	 }
	 if(is_special){
		  ruleHtml += "<div id='pwd_special'>Must have at least one special character</div>";
	 }

	 var _validate_pwd = function(pswd){

		  // validate for length restrictions
		  if(pswd.length >= min_pwd && pswd.length <=max_pwd) {
				jQuery('#pwd_length').removeClass('text-danger').addClass('text-success');
		  } else {
				jQuery('#pwd_length').removeClass('text-success').addClass('text-danger');
		  }

		  // validate for atleast a number
		  if (one_number){
				if(pswd.match(/\d/) ) {
					 jQuery('#pwd_number').removeClass('text-danger').addClass('text-success');
				} else {
					 jQuery('#pwd_number').removeClass('text-success').addClass('text-danger');
				}
		  }

		  // validate uppercase letter
		  if (one_uppercase){
				if ( pswd.match(/[A-Z]/) ) {
					 jQuery('#pwd_uppercase').removeClass('text-danger').addClass('text-success');
				} else {
					 jQuery('#pwd_uppercase').removeClass('text-success').addClass('text-danger');
				}
		  }

		  // validate lowercase
		  if (one_lowercase){
				if ( pswd.match(/[a-z]/) ) {
					 jQuery('#pwd_lowercase').removeClass('text-danger').addClass('text-success');
				} else {
					 jQuery('#pwd_lowercase').removeClass('text-success').addClass('text-danger');
				}
		  }

		  // validate special character
		  if (is_special){
				if ( pswd.match(/[!@#\$%\^\&*\)\(+=._-]/) ) {
					 jQuery('#pwd_special').removeClass('text-danger').addClass('text-success');
				} else {
					 jQuery('#pwd_special').removeClass('text-success').addClass('text-danger');
				}
		  }

	 };


	 jQuery('#wpabstracts_register #password').on('keyup', function(){
		  _validate_pwd(jQuery(this).val());
	 });

	 jQuery('[data-toggle="popover"]').popover({
		  html : true,
		  content: function() {
			 return ruleHtml;
		  },
		  title: function() {
			 return "Password Requirement";
		  }
	 });

}
