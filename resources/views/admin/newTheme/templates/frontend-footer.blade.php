    <!-- END COPYRIGHT -->

<!-- Load javascripts at bottom, this will reduce page load time -->
<!-- BEGIN CORE PLUGINS(REQUIRED FOR ALL PAGES) -->
<!--[if lt IE 9]>
    <script src="assets/plugins/respond.min.js"></script>
    <![endif]-->

<script src="{{asset('newAssets/js/jquery.js')}}" type="text/javascript"></script>
<script src="{{asset('newAssets/js/bootstrap.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<script>
    $(document).ready(function() {
        $("ul.user_type li").click(function() {
            $("ul.user_type li").each(function(){ $(this).removeClass('active');});
            $(this).addClass('active');
            var action = "{{ url('/admin/login') }}";
            var value = 'admin';
            switch ($(this).attr('tab-name')) {
                case 'admin':
                    break;
                case 'dispatcher':
                    value = 'dispatcher';
                    break;
                case 'fleet':
                    action = "{{ url('/fleet/login') }}";
                    value = 'fleet';
                    break;
                case 'account':
                    value = 'account';
                    break;
                case 'dispute':
                    value = 'dispute';
                    break;
            }
            $('#login_form').attr('action', action);
            $('#login_type').attr('value', value);
        });
    });
</script>



</body>
<!-- END BODY -->

</html>
