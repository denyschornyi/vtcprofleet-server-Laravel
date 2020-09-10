function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $('#blah').attr('src', e.target.result);
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    $(document).on("change", "#imgInp", function(){
		
        readURL(this);
    });
 $( function() {
    $( ".datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$('.ui.dropdown').dropdown({fullTextSearch:true});
  } );