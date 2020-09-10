<?php 
/*
////////////// Teameyo Project Management System  //////////////////////
//////////////////// Admin Footer for payment pages //////////////////////////
*/
$dash_settings = settings::findById(1);
$url = $dash_settings->url;
?>
    <!--[if lt IE 9]>
    <script src="assets/plugins/respond.min.js"></script>  
    <![endif]-->  
    <script src="../assets/js/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
$.base_url = "<?php echo $url; ?>";

</script>
    <script src="../assets/js/bootstrap.js" type="text/javascript"></script>
    <script src="../assets/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="../assets/js/jquery.nicescroll.min.js" type="text/javascript"></script>
	<script src="../assets/js/general.js" type="text/javascript"></script>
    <script src="../assets/js/client.js" type="text/javascript"></script>
    <!-- END CORE PLUGINS -->
</body>
<!-- END BODY -->
</html>