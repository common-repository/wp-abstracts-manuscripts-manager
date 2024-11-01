<p class="wpabstracts text-right"><strong><?php echo apply_filters('wpabstracts_title_filter', __('Welcome back','wpabstracts'), 'welcome_back');?> <?php echo $user->display_name; ?></strong></p>
<div class="wpabstracts">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#wpabstracts-dash-menu">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" id="wpabstracts-dashboard-btn" href="?dashboard"><span class="wpabstracts glyphicon glyphicon-home" aria-hidden="true"></span> <?php echo apply_filters('wpabstracts_title_filter', __('Dashboard','wpabstracts'), 'dashboard');?></a>
            </div>
            <div class="collapse navbar-collapse" id="wpabstracts-dash-menu">
                <?php if($show_nav_btns){ ?>
                    <ul class="nav navbar-nav">
                    <?php if (in_array('subscriber', $user->roles) || in_array('administrator', $user->roles)) { ?>
                        <li>
                            <a href="?task=add_abstract" id="new-abstract"><span class="wpabstracts glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo apply_filters('wpabstracts_title_filter', __('New Abstract','wpabstracts'), 'new_abstract');?></a>
                        </li>
                    <?php } else if (in_array('editor', $user->roles) && get_option('wpabstracts_reviewer_submit')){ ?>
                        <li>
                            <a href="?task=add_abstract"><span class="wpabstracts glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo apply_filters('wpabstracts_title_filter', __('New Abstract','wpabstracts'), 'new_abstract');?></a>
                        </li>
                    <?php } ?>
                    </ul>
                <?php } ?>
                <ul class="nav navbar-nav navbar-right">
                    <?php ob_start();?>
                        <li>
                            <a href="?task=profile">
                                <span class="wpabstracts glyphicon glyphicon-user" aria-hidden="true"></span> <?php echo apply_filters('wpabstracts_title_filter', __('My Profile','wpabstracts'), 'my_profile');?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo wp_logout_url(home_url()); ?>">
                                <span class="wpabstracts glyphicon glyphicon-off" aria-hidden="true"></span> <?php echo apply_filters('wpabstracts_title_filter', __('Logout','wpabstracts'), 'logout_btn');?>
                            </a>
                        </li>
                    <?php $right_links = ob_get_clean();?>
                    <?php echo apply_filters('wpabstracts_dashboard_rightmenu', $right_links); ?>
                </ul>
            </div>
        </div>
    </nav>
</div>