<!-- modal -->
<div id="generalModal" class="modal fade bs-example-modal-md" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
    	<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"></h4>
          </div>
          <div class="modal-body"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
    </div>
  </div>
</div>

<?php $timestamp = time(); ?>
<input type="hidden" id="upload-tm" value="<?php echo $timestamp; ?>" />
<input type="hidden" id="upload-token" value="<?php echo md5('S4lt' . $timestamp); ?>" />
<script type="text/javascript">
$.base_url = "<?php echo $url; ?>";
$.pageTitle = "Discussions | <?php echo $syatem_title; ?>";
</script>
<script src="<?php echo $url; ?>assets/js/bootstrap.js" type="text/javascript"></script>
<script src="<?php echo $url; ?>assets/js/jquery.nicescroll.min.js" type="text/javascript"></script>
<script src="<?php echo $url; ?>assets/js/lightbox.min.js" type="text/javascript"></script>
<script src="<?php echo $url; ?>assets/js/jquery.messages.js?var=<?php echo rand(); ?>" type="text/javascript"></script>
 <script src="<?php echo $url; ?>assets/js/general.js?var=<?php echo rand(); ?>" type="text/javascript"></script>
<script>
$(document).ready( function(){
	setTimeout( function(){
var cont_h = $('.listWrapper').height();
var tb_h = $('.listWrapper .tabs-control').height();
var ib_h = $('.listWrapper .innerBox').height();
var sf_height = tb_h + ib_h;
var f_height = cont_h - 190;
$('ul#messages-stack-list').height(f_height);
	}, 600);
});
</script>
</body>
</html>