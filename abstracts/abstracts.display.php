<?php
defined('ABSPATH') or die("ERROR: You do not have permission to access this page");
$abs_id = isset($_GET["abs_id"]) ? intval($_GET["abs_id"]) : 0;

if($abs_id > 0) {
    $abstract = wpabstracts_get_abstract($abs_id);
    wpabstracts_accepted_detailview($abstract);
} else {
    $abstracts = wpabstracts_get_accepted_abstracts($event_id);
    wpabstracts_accepted_listview($abstracts);
}

function wpabstracts_accepted_listview($abstracts) { ?>
    <?php $settings = get_option('wpabstracts_accepted_shortcode');?>
    <div class="wpabstracts container-fluid">
        <div style="margin-top:30px;" class="mainbox">
            <?php if(is_array($abstracts) && count($abstracts) > 0) { ?>
                <?php foreach ($abstracts as $key => $abstract) { ?>
                    <h3><a href="?abs_id=<?php echo $abstract->abstract_id;?>"><?php echo $abstract->title;?></a>
                        <?php if($settings['author']['enabled'] == true){ ?>
                            <small>
                                <?php _e('by', 'wpabstracts') . $abstract->author;?> 
                                <?php echo $abstract->author;?>
                            </small>
                        <?php } ?>
                    </h3>
                <?php } ?>
            <?php } ?>
            <?php if(is_array($abstracts) && count($abstracts) == 0) { ?>
                <h3><?php _e('Nothing to display', 'wpabstracts');?></h3>
            <?php } ?>
        </div>
    </div>
    <?php
}

function wpabstracts_accepted_detailview($abstract) { ?>
    <div class="wpabstracts container-fluid">
        <div style="margin-top:30px;" class="mainbox">
            <?php echo wpabstracts_generate_html($abstract, 'accepted_shortcode'); ?>
        </div>
    </div>
    <?php
}
?>