<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");

if (isset($_GET['task']) && $_GET['task'] == 'restore') {
    $restore_form = '[{"type":"paragraph","label":"Please take a moment and tell us about yourself."},{"type":"select","required":true,"label":"Title","name":"title","className":"form-control","layout":"form-group col-sm-4","values":[{"label":"Mr","value":"Mr"},{"label":"Mrs","value":"Mrs"},{"label":"Dr","value":"Dr"},{"label":"Prof","value":"Prof"}]},{"type":"text","required":true,"label":"First Name","description":"Please enter first name","name":"firstname","className":"form-control","layout":"form-group col-sm-4","wpSync":"first_name"},{"type":"text","required":true,"label":"Last Name","description":"Enter last name","name":"lastname","className":"form-control","layout":"form-group col-sm-4","wpSync":"last_name"},{"type":"select","required":true,"label":"Gender","description":"Select a gender","name":"gender","className":"form-control","layout":"form-group col-sm-4","values":[{"label":"Male","value":"Male"},{"label":"Female","value":"Female"}]},{"type":"text","label":"Phone","description":"How can you reach you?","name":"phone","className":"form-control","layout":"form-group col-sm-4"},{"type":"text","label":"Designation","description":"Tell us your role","name":"designation","className":"form-control","layout":"form-group col-sm-4"},{"type":"text","label":"Personal URL","description":"Enter your personal website  Twitter or LinkedIn URL","name":"personalurl","className":"form-control","layout":"form-group col-sm-4","wpSync":"user_url"},{"type":"text","label":"Organization","description":"What organization are you affiliated with.","name":"organization","className":"form-control","layout":"form-group col-sm-4"},{"type":"select","label":"Contact Preference","description":"What is your preferred method of contact?","name":"contact-preference","className":"form-control","layout":"form-group col-sm-4","values":[{"label":"Email","value":"Email","selected":true},{"label":"Phone","value":"Phone"}]},{"type":"text","label":"Address","description":"Enter full physical address","name":"address","className":"form-control","layout":"form-group col-sm-12","wpSync":"address"},{"type":"textarea","label":"Bio","description":"Tell us about yourself","name":"bio","rows":"3","className":"form-control","layout":"form-group col-sm-12","wpSync":"description"}]';
    update_option('wpabstracts_registration_form', $restore_form);
    wpabstracts_show_message(__("Your registration from was successfully restored.", "wpabstracts"), 'alert-success');
}

$default_form = get_option('wpabstracts_registration_form');

?>

<script>
jQuery(function() {

    var gridLayout = {
        label: 'Layout',
        options: {
            'form-group col-sm-4': 'One Third',
            'form-group col-sm-8': 'Two Third',
            'form-group col-sm-6': 'Half',
            'form-group col-sm-12': 'Full Width'
        }
    }

    var bsClass = {
        label: 'Class',
        options: {
            'form-control': 'Default'
        }
    }

    var wpFields = {
        label: 'Sync WP Fields',
        options: {
            '': 'None',
            'first_name': 'First Name',
            'last_name': 'Last Name',
            'nickname': 'Nickname',
            'address': 'Address',
            'user_url': 'Website',
            'description': 'Biographical Info'
        }
    }

    var langPath = '<?php echo plugin_dir_url(__DIR__) . 'languages/formbuilder' ?>';

    var fbOptions = {
        editOnAdd: true,
        fieldRemoveWarn: true,
        stickyControls: {
            enable: false
        },
        sortableControls: false,
        disableInjectedStyle: false,
        disableFields: ['autocomplete', 'button', 'file', 'number', 'radio-group', 'checkbox-group',
            'hidden'
        ],
        disabledAttrs: ['access', 'maxlength', 'subtype'],
        disabledActionButtons: ['data', 'clear'],
        controlPosition: 'left',
        controlOrder: ['header', 'paragraph', 'text', 'textarea', 'select', 'date'],
        typeUserAttrs: {
            'text': {
                className: bsClass,
                layout: gridLayout,
                wpSync: wpFields
            },
            'textarea': {
                className: bsClass,
                layout: gridLayout,
                wpSync: wpFields
            },
            'select': {
                className: bsClass,
                layout: gridLayout,
                wpSync: wpFields
            },
            'date': {
                className: bsClass,
                layout: gridLayout,
                wpSync: wpFields
            },
        },
        actionButtons: [{
            id: 'preview',
            className: 'btn btn-success',
            label: 'Preview',
            type: 'button',
            events: {
                click: function() {
                    showPreview(formBuilder.formData);
                }
            }
        }],
        i18n: {
            location: langPath
        },
        onSave: function(e, formData) {
            if (formData) {
                wpabstracts_save_regform(JSON.stringify(JSON.parse(formData)));
            }
        }
    };

    var defaultFormData = JSON.stringify(<?php echo $default_form; ?>);

    if (defaultFormData) {
        fbOptions.formData = defaultFormData;
    }

    var formBuilder = jQuery('#formbuilder').formBuilder(fbOptions);

    function showPreview(formData) {

        let _layoutTemplates = {
            default: function(field, label, help, data) {
                return jQuery('<div/>')
                    .addClass('wpabstracts ' + data.layout)
                    .attr('id', 'row-' + data.id)
                    .append(label, help, field);
            }
        }

        let formRenderOpts = {
            dataType: 'json',
            formData: formData,
            layoutTemplates: _layoutTemplates
        };

        let previewContainer = jQuery('#preview_container');
        let previewForm = jQuery('#preview_form');
        previewForm.formRender(formRenderOpts);
        jQuery('select[multiple]').multiselect();
        previewContainer.dialog({
            'width': 1024,
            'height': jQuery(window).height() - 80,
            'modal': true,
            'closeOnEscape': true,
            "closeText": "",
            'title': "Registration Form Preview",
            'buttons': [{
                text: "Close",
                click: function() {
                    jQuery("#preview_container").dialog('close');
                },
                "class": "wpa_button success"
            }]
        }).dialog('open');

    }
});
</script>
<style>
#formbuilder .name-wrap {
    display: none !important;
}
</style>
<div class="wpabstracts container-fluid">
    <form method="post" id="wpabstracts_formbuilder" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <h3><?php _e('Drag input elements from the left to create your user registration form', 'wpabstracts');?> <input
                type="button" onclick="wpabstracts_restore_user_formbuilder()" class="wpabstracts btn btn-primary"
                value="<?php _e('Restore Form', 'wpabstracts');?>" /></h3>
    </form>
    <div id='formbuilder'></div>
</div>
<div id="preview_container" class="hidden">
    <div class="wpabstracts container-fluid">
        <div class="wpabstracts panel panel-default">
            <div class="wpabstracts panel-heading">
                <h3 class="wpabstracts panel-title"><?php _e('Login Information', 'wpabstracts');?> <small
                        class="wpabstracts text-danger">Static Area</small></h3>
            </div>
            <div class="wpabstracts panel panel-body">
                <div class="wpabstracts form-group col-md-4 col-xs-12">
                    <label class="wpabstracts control-label" for="email"><?php _e('Email', 'wpabstracts');?></label>
                    <input type="text" name="email" id="email" required class="wpabstracts form-control"
                        placeholder="<?php _e('Email', 'wpabstracts');?>">
                    <span
                        class="wpabstracts help-block"><?php _e('Please enter a valid email address.', 'wpabstracts');?></span>
                </div>
                <div class="wpabstracts form-group col-md-4 col-xs-12">
                    <label class="wpabstracts control-label"
                        for="password"><?php _e('Password', 'wpabstracts');?></label>
                    <input type="password" name="password" id="password" required autocomplete="off"
                        class="wpabstracts form-control" placeholder="<?php _e('Password', 'wpabstracts');?>">
                    <span class="wpabstracts help-block"><?php _e('Please enter a password.', 'wpabstracts');?></span>
                </div>
                <div class="wpabstracts form-group col-md-4 col-xs-12">
                    <label class="wpabstracts control-label"
                        for="password_repeat"><?php _e('Repeat Password', 'wpabstracts');?></label>
                    <input type="password" name="password_repeat" required id="password_repeat"
                        class="wpabstracts form-control" placeholder="<?php _e('Repeat Password', 'wpabstracts');?>">
                    <span
                        class="wpabstracts help-block"><?php _e('Please repeat your password.', 'wpabstracts');?></span>
                </div>
            </div>
        </div>

        <div class="wpabstracts panel panel-default">
            <div class="wpabstracts panel-heading">
                <h3 class="wpabstracts panel-title"><?php _e('Account Information', 'wpabstracts');?> <small
                        class="wpabstracts text-danger"><?php _e('Form Preview', 'wpabstracts');?></small></h3>
            </div>
            <div class="wpabstracts panel panel-body">
                <div id="preview_form"></div>
            </div>

            <div class="wpabstracts panel panel-body">
                <div class="wpabstracts form-group">
                    <a href="#" id="wpabs_useraddon_register"
                        class="wpabstracts btn btn-primary"><?php _e('Register', 'wpabstracts');?></a>
                </div>
            </div>
        </div>
    </div>
</div>