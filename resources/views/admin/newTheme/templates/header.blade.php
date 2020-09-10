
<?php include("../includes/lib-initialize.php"); ?>
<?php //if(basename($_SERVER['PHP_SELF']) == '/admin/team-chat.php') { ?>
<div class="container">
  <div class="row">
	<div class="greatings">
    	<a href="index.php?logout=true" class="pull-right" style="margin-top: 15px;">Log Out</a>
    	<p class="pull-left">
        	<img src="<?php echo profile_picture($msg->logged_user_id, $base_url); ?>" width="50" height="50" />
			<?php echo $msg->return_display_name($msg->logged_user_id); ?>
        </p>
    </div>
  </div>
</div>
<?php //} ?>