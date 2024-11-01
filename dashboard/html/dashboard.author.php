<div class="wpabstracts container-fluid">
    <div class="wpabstracts panel panel-default">
        <div class="wpabstracts panel-heading">
            <h6 class="wpabstracts panel-title"><?php echo apply_filters('wpabstracts_title_filter', __('My Abstracts','wpabstracts'), 'my_abstracts');?></h6>
        </div>

        <div class="wpabstracts panel-body">
            <div class="wpabstracts table-responsive">
                <table class="wpabstracts table table-hover table-striped" id="wpa_my_abstracts">
                    <thead>
                        <tr>
                            <th><?php _e('ID','wpabstracts');?></th>
                            <th><?php _e('Title','wpabstracts');?></th>
                            <?php if(get_option('wpabstracts_show_reviews')){ ?>
                            <th><?php _e('Review','wpabstracts');?></th>
                            <?php } ?>
                            <th><?php _e('Status','wpabstracts');?></th>
                            <th><?php _e('Preference','wpabstracts');?></th>
                            <th><?php _e('Submit Date','wpabstracts');?></th>
                            <th><i class="wpabstracts glyphicon glyphicon-paperclip" title="<?php _e("Attachments", 'wpabstracts'); ?>"></th>
                            <th><?php _e('Action','wpabstracts');?></th>
                        </tr>
                    </thead>
                    <?php
                        if (!count($abstracts)) { ?>
                            <tbody>
                                <tr>
                                    <td colspan="7"><?php _e("You have NOT submitted any abstracts.", 'wpabstracts'); ?><td>
                                </tr>
                            </tbody>
                            <?php } else { ?>
                            <tbody>
                            <?php
                            foreach($abstracts as $abstract){ ?>
                                <?php $attachments = wpabstracts_get_attachments($abstract->abstract_id); ?>
                                <tr>
                                    <td><?php echo $abstract->abstract_id; ?></td>
                                    <td style="width:20%;"><?php echo stripslashes($abstract->title); ?></td>
                                    <?php if(get_option('wpabstracts_show_reviews')){ ?>
                                    <td style="width:35%;">
                                        <ol><?php _e("No reviews as yet.", 'wpabstracts');?></ol>
                                    </td>
                                    <?php } ?>
                                    <td><?php echo wpabstracts_map_status_name($statuses, $abstract->status); ?></td>
                                    <td><?php echo stripslashes($abstract->presenter_preference); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($abstract->submit_date)); ?></td>
                                    <td style="text-align: center;"><?php echo count($attachments); ?></td>
                                    <?php if(wpabstracts_user_can_edit($abstract->status)) { ?>
                                    <td>
                                        <a href="?task=edit_abstract&id=<?php echo $abstract->abstract_id; ?>"><?php _e('Edit','wpabstracts');?></a>
                                        <a href='javascript:wpabstracts_delete_abstracts(<?php echo $abstract->abstract_id; ?>, 1, 0)'><?php _e('Delete','wpabstracts');?></a>
                                    </td>
                                    <?php } else { ?>
                                        <td>
                                        <a href="?task=view_abstract&id=<?php echo $abstract->abstract_id; ?>"><?php _e('View','wpabstracts');?></a>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php
                            } ?>
                            </tbody>
                    <?php } ?>
                </table>
                <script>
                    jQuery(document).ready( function () {
                        var abs_count = '<?php echo count($abstracts);?>';
                        if(abs_count > 0){
                            jQuery('#wpa_my_abstracts').DataTable({
                                responsive: false,
                                dom: 'Bfrltip',
                                buttons: []
                            });
                        }                                
                    });
                </script>
            </div>
        </div>
    </div>
</div>