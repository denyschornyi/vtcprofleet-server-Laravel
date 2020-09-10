<!--[if lt IE 9]>
    <script src="assets/plugins/respond.min.js"></script>  
    <![endif]-->
<script src="{{asset('newAssets/js/jquery.js')}}" type="text/javascript"></script>
<script type="text/javascript">
    $.base_url = "<?php echo url(''); ?>";
</script>
<script src="{{asset('newAssets/js/bootstrap.js')}}" type="text/javascript"></script>
<script src="{{asset('newAssets/js/jquery-ui.min.js')}}" type="text/javascript"></script>

<script src="{{asset('newAssets/js/semantic.min.js')}}" type="text/javascript"></script>
<script src="{{asset('newAssets/js/jquery.nicescroll.min.js')}}" type="text/javascript"></script>
<script src="{{asset('newAssets/js/client.js')}}" type="text/javascript"></script>
<script src="{{asset('newAssets/js/general.js')}}" type="text/javascript"></script>
<!-- <script src="../assets/js/canvasjs.min.js" type="text/javascript"></script> -->
<!-- include summernote css/js -->
<link href="{{asset('newAssets/css/summernote-bs4.css')}}" rel="stylesheet">
<script src="{{asset('newAssets/js/summernote-bs4.js')}}"></script>
<!-- END CORE PLUGINS -->
<script>
    $(document).ready(function() {
        $('#editor, .editor').summernote({
            disableDragAndDrop: true,
            dialogsFade: true,
            height: 250,
            emptyPara: '',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['height', ['height']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
</body>
<!-- END BODY -->

</html> 