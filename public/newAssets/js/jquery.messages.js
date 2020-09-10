	$.ajaxError = 'Failed to proccess request';
	$.sendButton = '';
	$.emptyBox = 'Please write something to send';
	$.noMessages = 'No Messages';
	$.noContacts = 'No Users';
	$.unreadMessages = 'Unread';
	$.userTypes = 'Typing ...';	
	$.uploadLimit = '5MB';
	
	$.enterIsSend = true;
	$.shift_pressed = false;
			

	
	// Tab Switching
	$('#tabs-control').on("click", "#tab-contacts", function(e) {
		contacts_tab();
		e.preventDefault();
	})
	
	// Tab Switching
	$('#tabs-control').on("click", "#tab-contacts1", function(e) {
		contacts_tab1();
		e.preventDefault();
	})
	
	$('#tabs-control').on("click", "#tab-chats", function(e) {
		chat_tab();
		e.preventDefault();
	})
	
	// Contacts load more
	$('#messages-stack-list').off('click').on("click", ".load-more-contacts", function() {
		var ID = $(this).attr("id");
		var proid = $(this).data("proval");
		var UID = $(this).attr("rel");
		var URL = $.base_url + 'ajax/contacts_more_ajax.php';
		var R_URL = $.base_url + 'ajax/refresh_unreadMessages_ajax.php';
		var tab = 'chats';
		var dataString = "lastid=" + encodeURIComponent(ID) + "&uid="+ encodeURIComponent(UID) + "&tab="+ encodeURIComponent(tab)+"&proid="+ encodeURIComponent(proid);
		if (ID) {
			$.ajax({
				type: "POST",
				url: URL,
				data: {lastid: ID, uid: UID, tab: $.tab},
				cache: false,
				beforeSend: function() {
					$("#more-contacts" + ID).html('<img src="assets/img/ajaxloader.gif" />');
				},
				error: function() { $('#loadingDiv').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
				success: function(html) {
					$('#loadingDiv').hide();
					$("div#more-contacts"+ID+".more-contacts-parent").remove();
					$("#messages-stack-list").append(html);
					update_unMsg_counter(R_URL, dataString, UID, '');
					title_unread_counter();
				}
			});
		} else {
			$("#more-contacts").html($.noContacts); // no results
		}
		return false;
	});

	// Messages System
	$('#messages-stack-list').on("click", "li.prepare-message", function() {  
		
		var ID = $(this).attr("id");
		
		var pro_ID = $(this).data("project");
		var pro_IDB = $(this).parent().data("proval");
		
		var URL = $.base_url + 'ajax/request_chat_ajax.php';
		
		var R_URL = $.base_url + 'ajax/refresh_unreadMessages_ajax.php';
		
		var dataString = 'id='+encodeURIComponent(ID)+'&proid='+encodeURIComponent(pro_ID);
		
		$.ajax({
            type: "POST",
            url: URL,
            data: {id: ID, user_pro_id: pro_IDB},
            cache: false,
			beforeSend: function() { $('#loadingDiv').show(); },
			error: function() { $('#loadingDiv').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
            success: function(html) {
				
				$('#loadingDiv').hide();
				$('#contacts-search-input').val("");
				$(".active-message").removeClass("active-message");
				$("li#"+ID+".prepare-message").focus().addClass("active-message");
				$("#text-messages-request").html(html);
				var d = $("#text-messages-request");
				 d.scrollTop(d.prop("scrollHeight")); 
				$("#text-messages").attr("rel", ID);
				$("#text-messages").attr("data-pid", pro_ID);
				
				
			// $(".messageWrapper").scrollTo("#text-messages .media:first-child");
				
				$(".type-a-message-box").focus();
				
				if($.tab == 'chats')
				{
					cloned_item = $("li#"+ID+".prepare-message").html();
				
					$.when(chat_tab()).done(function() {
						next_tab = 'chats';
						tab_id = ID;
					});
				}
		
				update_unMsg_counter(R_URL, dataString, ID, '');
				title_unread_counter();
				
            }
			
        });
		return false;
		
	});
	
	// Messages System - type message, send (modified since 1.1)
	$("#text-messages-request").on("focus", "textarea.type-a-message-box", function() { 
		var ID = $(this).attr("id");
		var URL = $.base_url + 'ajax/add_chat_ajax.php'; 
		//$('#type-a-message').remove();	
		user_is_typing(this, ID);
		
		$(this).on('blur', function() {
			stop_type_status();	
		});
		
		
		
		return false;		
	});
	
function getCaret(el) { 
    if (el.selectionStart) { 
        return el.selectionStart; 
    } else if (document.selection) { 
        el.focus();
        var r = document.selection.createRange(); 
        if (r == null) { 
            return 0;
        }
        var re = el.createTextRange(), rc = re.duplicate();
        re.moveToBookmark(r.getBookmark());
        rc.setEndPoint('EndToStart', re);
        return rc.text.length;
    }  
    return 0; 
}

	$("#text-messages-request").on("keyup" ,"textarea.type-a-message-box", function(e){
		var ID = $(this).attr("id");
		var pro_ID = $(this).data("project");
		//alert(pro_ID);
		var URL = $.base_url + 'ajax/add_chat_ajax.php'; 
		
		$('div.message-btn-target').html('<a href="#" id="'+ID+'" class="btn btn-default btn-sm send-message" id="type-a-message" ><i class="fa fa-paper-plane" aria-hidden="true"></i> '+$.sendButton+'</a>');
		var running= false;
	if($.enterIsSend == true)
		{ 
 var windowWidth = $(window).width();
 if (e.keyCode == 13 && windowWidth >= 768) {
        var content = this.value;  
        var caret = getCaret(this);          
        if(e.shiftKey){
            this.value = content.substring(0, caret - 1) + "\n" + content.substring(caret, content.length);
            e.stopPropagation();
        } else {
            this.value = content.substring(0, caret - 1) + content.substring(caret, content.length);
            if(running === false) {
					running = true;
						send_message(ID, pro_ID, URL);
					}
        }
    } 

			$("a.send-message").on("click", function(event) {
				if(running === false) {
					running = true;
					send_message(ID, pro_ID, URL);	
				// 	$(".type-a-message-box").focus();
				}

			});
			
			
		} else {
			$("a.send-message").on("click", function(event) {
				send_message(ID, pro_ID, URL);	
			// 	$(".type-a-message-box").focus();

			});
		}
		return false;	
	});

	function send_message(ID, pro_ID, URL)
	{
		var textarea = $('textarea.type-a-message-box').val();
		if ($.trim(textarea).length == 0) 
		{
			alert($.emptyBox);
		} else {
			var myRequest;

//in your change handler...
if (myRequest)
{
    myRequest.abort();
}
myRequest =	$.ajax({
				type: "POST",
				url: URL,
				data: {id: ID, user_pro_id: pro_ID, message: textarea},
				cache: false,
				beforeSend: function() { $('#loadingDiv').show(); $('.type-a-message-box').attr('disabled', 'disabled');},
				error: function() { $('#loadingDiv').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); $('.type-a-message-box').attr('disabled', ''); },
				success: function(html) {
					// alert(pro_ID);
					$('#loadingDiv').hide();
					$('.type-a-message-box').attr('disabled', '');
					$("p.no-messages").remove();
					$("#text-messages-request").html(html);
					$("#text-messages").attr("rel", ID);
					$("#text-messages").attr("data-pid", pro_ID);
					stop_type_status();
					var d = $("#text-messages-request");
					d.scrollTop(d.prop("scrollHeight")); 
					$( "#tab-chats" ).trigger( "click" );

					 var windowWidth = $(window).width();
					 if(windowWidth >= 768){
					$(".type-a-message-box").focus();
					 }
					 
					
				}
			});
		}
	}
		
	// Messages System - load more messages
	$('body').off('click').on("click", ".load-more-messages", function() {
		var ID = $(this).attr("id");
		var UID = $(this).attr("rel");
		var URL = $.base_url + 'ajax/chat_more_ajax.php';
		var R_URL = $.base_url + 'ajax/refresh_unreadMessages_ajax.php';
		var dataString = "lastid=" + encodeURIComponent(ID) + "&uid="+ encodeURIComponent(UID);
		var oldVal = $(this).find("#count-old-messages").html();
		if (ID) {
			$.ajax({
				type: "POST",
				url: URL,
				data: {lastid: ID, uid: UID, countVal: oldVal},
				cache: false,
				beforeSend: function() {
					$("#more" + ID).html('<img src="assets/img/ajaxloader.gif" />');
				},
				error: function() { $('#loadingDiv').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
				success: function(html) {
					// alert(html);
					$('#loadingDiv').hide();
					$("div#more"+ID+".more-messages-parent").remove();
					$("#text-messages").prepend(html);
					update_unMsg_counter(R_URL, dataString, UID, '');
					title_unread_counter();
				}
			});
		} else {
			$("#more").html($.noMessages); // no results
		}
		return false;
	});
	
	// Messages System - filter
	$('#contacts-search').on("click", "#contacts-search-input", function() {
		var oldhtml = $("ul#messages-stack-list").html();
 		$('#tab-chats').removeClass('active-tab');
		$('#tab-contacts').removeClass('active-tab');
		$('#tab-contacts1').removeClass('active-tab');
		
		$(this).keyup(function() {
			var filterbox = $(this).val();
			var URL = $.base_url + 'ajax/chat_filter_ajax.php';
			if ($.trim(filterbox).length > 0) {
				$.ajax({
					type: "POST",
					url: URL,
					data: {filterword: filterbox},
					cache: false,
					beforeSend: function() { $('#loadingDiv').show(); },
					error: function() { 
					$('#loadingDiv').hide(); 
					$('#errorDiv').html('<p>'+$.ajaxError+'</p>'); 
					},
					success: function(html) {
						$('#loadingDiv').hide();
						$("ul#messages-stack-list").html(html).show();
					}
				});
			}
			return false;
		});
		$("ul#messages-stack-list").mouseup(function() {
			return false
		});
		$(document).mouseup(function() {
			$('#contacts-search-input').val("");
			$.tab = 'chats';
			$('#tab-chats').addClass('active-tab');
			$('#tab-contacts').removeClass('active-tab');
			$('#tab-contacts1').removeClass('active-tab');
		});
	});
	
	// Messages System - remove
	$('#text-messages-request').off("click").on("click", ".remove-message", function() {
		var ID = $(this).attr("id");
		var UID = $(this).data("user");
		var URL = $.base_url + 'ajax/chat_remove_ajax.php';
		var PID = $(this).data("pro");
		$.ajax({
				type: "POST",
				url: URL,
				data: {id: ID, uid: UID, proid: PID},
				cache: false,
				beforeSend: function() { $('#loadingDiv').show(); },
				error: function() { $('#loadingDiv').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
				success: function(html) {
					$('#loadingDiv').hide();
					$("#u_msg"+ID).remove();
					var value = parseInt($('span#count-old-messages').text());
					$('span#count-old-messages').html(value-1);
					if(value == 1)
					{
						$(".more-messages-parent").fadeOut(300).remove();
					}
				}
			});
		title_unread_counter();
		return false;
	})
	
	// Messages Sytem - live unread message to the title (since 1.5)
	function title_unread_counter() {
		var URL = $.base_url + 'ajax/ajax_total_unread.php';
		var title = $.pageTitle;
		$.ajax({
				type: "POST",
				url: URL,
				data: {total_unread: 'true'},
				cache: false,
				success: function(res) {
					var sus = res+title;
					$('title').html(sus);
				}
			});
		return false;
	}
	
	function clean_modal()
	{
		$('#generalModal .modal-header h4.modal-title').html('');
		$('#generalModal .modal-body').html('');
	}	
	
	// Emoticons
	$('body').on('click', '#emoticons', function() {
		$('#generalModal .modal-header h4.modal-title').html('Select an Emotion');
		$('#generalModal .modal-body').html(
'<img class="emoticons" src="assets/img/emoticons/Angry.png" id="angry" data-value="[angry]">' +
'<img class="emoticons" src="assets/img/emoticons/Angry-Devil.png" id="angry-devil" data-value="[angry-devil]">' + 
'<img class="emoticons" src="assets/img/emoticons/Anguished.png" id="anguished" data-value="[anguished]">' + 
'<img class="emoticons" src="assets/img/emoticons/Astonished.png" id="astonished" data-value="[astonished]">' +
'<img class="emoticons" src="assets/img/emoticons/Blushed.png" id="blushed" data-value="[blushed]">' + 
'<img class="emoticons" src="assets/img/emoticons/Cold-Sweat.png" id="cold-sweat" data-value="[cold-sweat]">' + 
'<img class="emoticons" src="assets/img/emoticons/Confounded.png" id="confounded" data-value="[confounded]">' + 
'<img class="emoticons" src="assets/img/emoticons/Confused.png" id="confused" data-value="[confused]">' + 
'<img class="emoticons" src="assets/img/emoticons/Crying.png" id="crying" data-value="[crying]">' + 
'<img class="emoticons" src="assets/img/emoticons/Disappointed.png" id="disappointed" data-value="[disappointed]">' + 
'<img class="emoticons" src="assets/img/emoticons/Disappointed-Relieved.png" id="disappointed-relieved" data-value="[disappointed-relieved]">' + 
'<img class="emoticons" src="assets/img/emoticons/Dizzy.png" id="dizzy" data-value="[dizzy]">' + 
'<img class="emoticons" src="assets/img/emoticons/Emoji.png" id="emoji" data-value="[emoji]">' + 
'<img class="emoticons" src="assets/img/emoticons/Expressionless.png" id="expressionless" data-value="[expressionless]">' + 
'<img class="emoticons" src="assets/img/emoticons/Eyes.png" id="eyes" data-value="[eyes]">' + 
'<img class="emoticons" src="assets/img/emoticons/Face-with-Cold.png" id="face-with-cold" data-value="[face-with-cold]">' + 
'<img class="emoticons" src="assets/img/emoticons/Fearful.png" id="fearful" data-value="[fearful]">' + 
'<img class="emoticons" src="assets/img/emoticons/Fire.png" id="fire" data-value="[fire]">' + 
'<img class="emoticons" src="assets/img/emoticons/Flushed.png" id="flushed" data-value="[flushed]">' + 
'<img class="emoticons" src="assets/img/emoticons/Frowning.png" id="frowning" data-value="[frowning]">' + 
'<img class="emoticons" src="assets/img/emoticons/Ghost.png" id="ghost" data-value="[ghost]">' + 
'<img class="emoticons" src="assets/img/emoticons/Grinmacing.png" id="grinmacing" data-value="[grinmacing]">' + 
'<img class="emoticons" src="assets/img/emoticons/Grinning.png" id="grinning" data-value="[grinning]">' + 
'<img class="emoticons" src="assets/img/emoticons/Halo.png" id="halo" data-value="[halo]">' + 
'<img class="emoticons" src="assets/img/emoticons/Head-Bandage.png" id="head-bandage" data-value="[head-bandage]">' + 
'<img class="emoticons" src="assets/img/emoticons/Heart-Eyes.png" id="heart-eyes" data-value="[heart-eyes]">' + 
'<img class="emoticons" src="assets/img/emoticons/Hugging.png" id="hugging" data-value="[hugging]">' + 
'<img class="emoticons" src="assets/img/emoticons/Hungry.png" id="hungry" data-value="[hungry]">' + 
'<img class="emoticons" src="assets/img/emoticons/Hushed.png" id="hushed" data-value="[hushed]">' + 
'<img class="emoticons" src="assets/img/emoticons/Kiss-Emoji.png" id="kiss-emoji" data-value="[kiss-emoji]">' + 
'<img class="emoticons" src="assets/img/emoticons/Kissing.png" id="kissing" data-value="[kissing]">' + 
'<img class="emoticons" src="assets/img/emoticons/Kissing-Face.png" id="kissing-face" data-value="[kissing-face]">' + 
'<img class="emoticons" src="assets/img/emoticons/Loudly-Crying.png" id="loudly-crying" data-value="[loudly-crying]">' + 
'<img class="emoticons" src="assets/img/emoticons/Money-Face.png" id="money-face" data-value="[money-face]">' + 
'<img class="emoticons" src="assets/img/emoticons/Nerd.png" id="nerd" data-value="[nerd]">' + 
'<img class="emoticons" src="assets/img/emoticons/Neutral.png" id="neutral" data-value="[neutral]">' + 
'<img class="emoticons" src="assets/img/emoticons/Relieved.png" id="relieved" data-value="[relieved]">' + 
'<img class="emoticons" src="assets/img/emoticons/Rolling-Eyes.png" id="rolling-eyes" data-value="[rolling-eyes]">' + 
'<img class="emoticons" src="assets/img/emoticons/Shyly.png" id="shyly" data-value="[shyly]">' + 
'<img class="emoticons" src="assets/img/emoticons/Sick.png" id="sick" data-value="[sick]">' + 
'<img class="emoticons" src="assets/img/emoticons/Sign.png" id="sign" data-value="[sign]">' + 
'<img class="emoticons" src="assets/img/emoticons/Sleeping.png" id="sleeping" data-value="[sleeping]">' + 
'<img class="emoticons" src="assets/img/emoticons/Sleeping-Snoring.png" id="sleeping-snoring" data-value="[sleeping-snoring]">' + 
'<img class="emoticons" src="assets/img/emoticons/Slightly.png" id="slightly" data-value="[slightly]">' + 
'<img class="emoticons" src="assets/img/emoticons/Smiling-Devil.png" id="smiling-devil" data-value="[smiling-devil]">' + 
'<img class="emoticons" src="assets/img/emoticons/Smiling-Eyes.png" id="smiling-eyes" data-value="[smiling-eyes]">' + 
'<img class="emoticons" src="assets/img/emoticons/Smiling-Face.png" id="smiling-face" data-value="[smiling-face]">' + 
'<img class="emoticons" src="assets/img/emoticons/Smiling-Smiling.png" id="smiling-smiling" data-value="[smiling-smiling]">' + 
'<img class="emoticons" src="assets/img/emoticons/Smirk.png" id="smirk" data-value="[smirk]">' + 
'<img class="emoticons" src="assets/img/emoticons/Sunglasses.png" id="sunglasses" data-value="[sunglasses]">' + 
'<img class="emoticons" src="assets/img/emoticons/Surprised.png" id="surprised" data-value="[surprised]">' + 
'<img class="emoticons" src="assets/img/emoticons/Sweat.png" id="sweat" data-value="[sweat]">' + 
'<img class="emoticons" src="assets/img/emoticons/Tears.png" id="tears" data-value="[tears]">' + 
'<img class="emoticons" src="assets/img/emoticons/Thermometer.png" id="thermometer" data-value="[thermometer]">' + 
'<img class="emoticons" src="assets/img/emoticons/Thinking.png" id="thinking" data-value="[thinking]">' + 
'<img class="emoticons" src="assets/img/emoticons/Thumbs-Up.png" id="thumbs-up" data-value="[thumbs-up]">' + 
'<img class="emoticons" src="assets/img/emoticons/Tightly.png" id="tightly" data-value="[tightly]">' + 
'<img class="emoticons" src="assets/img/emoticons/Tired.png" id="tired" data-value="[tired]">' +  
'<img class="emoticons" src="assets/img/emoticons/Tongue-Out-Tightly.png" id="tongue-out-tightly" data-value="[tongue-out-tightly]">' + 
'<img class="emoticons" src="assets/img/emoticons/Tongue-Out.png" id="tongue-out" data-value="[tongue-out]">' + 
'<img class="emoticons" src="assets/img/emoticons/Tongue-Winking.png" id="tongue-winking" data-value="[tongue-winking]">' + 
'<img class="emoticons" src="assets/img/emoticons/Unamused.png" id="unamused" data-value="[unamused]">' + 
'<img class="emoticons" src="assets/img/emoticons/Up-Pointing.png" id="up-pointing" data-value="[up-pointing]">' + 
'<img class="emoticons" src="assets/img/emoticons/Upside.png" id="upside" data-value="[upside]">' + 
'<img class="emoticons" src="assets/img/emoticons/Very-Angry.png" id="very-angry" data-value="[very-angry]">' + 
'<img class="emoticons" src="assets/img/emoticons/Very-Mad.png" id="very-mad" data-value="[very-mad]">' + 
'<img class="emoticons" src="assets/img/emoticons/Very-sad.png" id="very-sad" data-value="[very-sad]">' + 
'<img class="emoticons" src="assets/img/emoticons/Victory.png" id="victory" data-value="[victory]">' + 
'<img class="emoticons" src="assets/img/emoticons/Weary.png" id="weary" data-value="[weary]">' + 
'<img class="emoticons" src="assets/img/emoticons/Wink.png" id="wink" data-value="[wink]">' + 
'<img class="emoticons" src="assets/img/emoticons/Worried.png" id="worried" data-value="[worried]">' + 
'<img class="emoticons" src="assets/img/emoticons/Zipper.png" id="zipper" data-value="[zipper]">' 
		);
		$('#generalModal').modal('show');
		$(".emoticons").on('click', function()
		{
			var id = $(this).attr('id');
			var image_url = $(this).data('value');
			var current_value = $("textarea.type-a-message-box").val();
			
			$("textarea.type-a-message-box").val(current_value+image_url);
			$('#generalModal').modal('hide');
$("textarea.type-a-message-box").keyup();
			// clean stuff to avoid duplication
			clean_modal();
		})	
	})
	
	$('body').on('click', ".upload-imgw>a", function(){
	$("#file_upload").click();
	return false;
});

  $('body').on('change', '#file_upload', function ()
        {
            var file = $( '#file_upload' ).get(0).files[0],
                formData = new FormData();
				filename = $('#file_upload')[0].files[0];
				$(".preloadimgname").html(filename);
		var current_value = $("textarea.type-a-message-box").val();
		var tm = $('input#upload-tm').val();
            formData.append( 'file', file );
            formData.append( 'tm', tm );
            console.log( file );
            $.ajax( {
                url        : $.base_url +'includes/save-file.php',
                type       : 'POST',
                contentType: false,
                cache      : false,
                processData: false,
                data       : formData,
                xhr        : function ()
                {
                    var jqXHR = null;
                    if ( window.ActiveXObject )
                    {
                        jqXHR = new window.ActiveXObject( "Microsoft.XMLHTTP" );
                    }
                    else
                    {
                        jqXHR = new window.XMLHttpRequest();
                    }
                    //Upload progress
                    jqXHR.upload.addEventListener( "progress", function ( evt )
                    {
                        if ( evt.lengthComputable )
                        {
                            var percentComplete = Math.round( (evt.loaded * 100) / evt.total );
                            //Do something with upload progress
                            // console.log( 'Uploaded percent', percentComplete );
							$('.preloadpro').html(percentComplete);
							$('.probarinn').css('width', percentComplete+'%');
                        }
                    }, false );
                    //Download progress
                    jqXHR.addEventListener( "progress", function ( evt )
                    {
                        if ( evt.lengthComputable )
                        {
                            var percentComplete = Math.round( (evt.loaded * 100) / evt.total );
                            //Do something with download progress
                            // console.log( 'Downloaded percent', percentComplete );
							$('.preloadpro').html(percentComplete);
							$('.probarinn').css('width', percentComplete+'%');
                        }
                    }, false );
                    return jqXHR;
                },
                success    : function ( data )
                {
                    //Do something success-ish
					$("textarea.type-a-message-box").val(current_value+'[photoAttachment-'+data+']');
					send_message($('textarea.type-a-message-box').attr('id'), $('textarea.type-a-message-box').data('project'), $.base_url + 'ajax/add_chat_ajax.php');
					$('#generalModal').modal('hide');
                    // console.log( data + 'Completed.' );
					
                }
			
            });
        } );

 $('body').on('change', '#send-file', function ()
        {
            var file = $( '#file_upload' ).get(0).files[0],
                formData = new FormData();
				filename = $('#file_upload')[0].files[0];
				$(".preloadimgname").html(filename);
		var current_value = $("textarea.type-a-message-box").val();
		var tm = $('input#upload-tm').val();
            formData.append( 'file', file );
            formData.append( 'tm', tm );
            console.log( file );
            $.ajax( {
                url        : $.base_url +'includes/save-file.php',
                type       : 'POST',
                contentType: false,
                cache      : false,
                processData: false,
                data       : formData,
                xhr        : function ()
                {
                    var jqXHR = null;
                    if ( window.ActiveXObject )
                    {
                        jqXHR = new window.ActiveXObject( "Microsoft.XMLHTTP" );
                    }
                    else
                    {
                        jqXHR = new window.XMLHttpRequest();
                    }
                    //Upload progress
                    jqXHR.upload.addEventListener( "progress", function ( evt )
                    {
                        if ( evt.lengthComputable )
                        {
                            var percentComplete = Math.round( (evt.loaded * 100) / evt.total );
                            //Do something with upload progress
                            // console.log( 'Uploaded percent', percentComplete );
							$('.preloadpro').html(percentComplete);
							$('.probarinn').css('width', percentComplete+'%');
                        }
                    }, false );
                    //Download progress
                    jqXHR.addEventListener( "progress", function ( evt )
                    {
                        if ( evt.lengthComputable )
                        {
                            var percentComplete = Math.round( (evt.loaded * 100) / evt.total );
                            //Do something with download progress
                            // console.log( 'Downloaded percent', percentComplete );
							$('.preloadpro').html(percentComplete);
							$('.probarinn').css('width', percentComplete+'%');
                        }
                    }, false );
                    return jqXHR;
                },
                success    : function ( data )
                {
                    //Do something success-ish
					$("textarea.type-a-message-box").val(current_value+'[fileAttachment-'+data+']');
					send_message($('textarea.type-a-message-box').attr('id'), $('textarea.type-a-message-box').data('project'), $.base_url + 'ajax/add_chat_ajax.php');
					$('#generalModal').modal('hide');
                    // console.log( data + 'Completed.' );
					
                }
            });
        } );
	// Send Photo
	$('body').on('click', '#send-photo', function() {
		$('#generalModal .modal-header h4.modal-title').html('Upload Your Image');
		$('#generalModal .modal-body').html('<div class="upload-imgw"><i class="fa fa-cloud-upload" aria-hidden="true"></i><a href="#">Select Image</a><input type="file" name="file_upload" id="file_upload" style="display:none;" accept="image/x-png,image/gif,image/jpeg" /><br /><p>Note: Allowed files are .gif, .png, .PNG, .jpg, .JPG Limited to '+$.uploadLimit+'<p/><div class="wrap-pro"><span class="preloadimgname"></span><span class="preloadpro"></span></div><div class="probar"><div class="probarinn"></div></div></div>');
		$('#generalModal').modal('show');
		$('#generalModal .modal-footer').html('');
	});
	
	// Send File
	$('body').on('click', '#send-file', function() {
		$('#generalModal .modal-header h4.modal-title').html('Attach Files'); 
		$('#generalModal .modal-body').html('<div class="upload-imgw"><i class="fa fa-cloud-upload" aria-hidden="true"></i><a href="#">Select File</a><input type="file" name="file_upload" id="file_upload" style="display:none;" accept=".zip,.pdf,.doc,.ptt,.txt,.xls,.docx,.xlsx,.pptx,.eps,.psd" /><br /><p>Note: Allowed files are .zip, .pdf, .doc, .ptt, .txt, .xls, .docx, .xlsx, .pptx, .eps, .psd Limited to '+$.uploadLimit+'<p/><div class="wrap-pro"><span class="preloadimgname"></span><span class="preloadpro"></span></div><div class="probar"><div class="probarinn"></div></div></div>');
		// uploadify_init('*.zip; *.pdf; *.doc; *.ppt; *.xls; *.txt; *.docx; *.xlsx; *.pptx', 'fileAttachment', 'files');
		$('#generalModal').modal('show');
		$('#generalModal .modal-footer').html('');
	});

		
// Last Seen
function last_seen()
{
	$(window).bind("beforeunload", function(){
		$.post($.base_url + 'ajax/ajax_last_seen.php', 'offline=true', function(response){}); 	
	}); 
	
	$(window).focus(function() {
		$.post($.base_url + 'ajax/ajax_last_seen.php', 'offline=false', function(response){}); 
	});
}
	
// Update message counter
function update_unMsg_counter(R_URL, dataString, ID, type)
{
	
	$.post(R_URL, dataString, function(response) 
	{
		// alert(response);
		// console.log('user id: ' + ID + 'Response: ' + response); 
		$("#unreader-count"+ID).html(response);
		$("#unreader-counter-"+ID).show();
		var counter = $("#unreader-count-"+ID).text();
		if(counter.length == 0) 
		{
			if(type == 'realtime')
			{
				// alert(response);
				if(counter !== response)
				{
					
					$("#unreader-counter-"+ID).html('<span class="label label-warning" id="unreader-count'+ID+'">'+response+'</span>');	
				}else{
					$("#unreader-counter-"+ID).text('');
				}
			} else {
				$("#unreader-counter-"+ID).fadeOut(300).text('');			
			}
		}
	});		
}

function stop_type_status()
{
	var URL = $.base_url + 'ajax/chat_type_ajax.php';
	$.post(URL, 'status=stopped', function(res) {});	
}

// User is typing (since 1.1)
function user_is_typing(type, id)
{
	var URL = $.base_url + 'ajax/chat_type_ajax.php';
	var timer, timeout = 750;
        
    $(type).keyup(function()
    {
        if (typeof timer != undefined)
            clearTimeout(timer);

         $.post(URL, 'status=typing_'+id, function(res) {});
		
         timer = setTimeout(function()
         {
			$.post(URL, 'status=stopped', function(res) {});
         }, timeout);
    });	
}

// User typing status (looks who is typing) (since 1.1)
function type_status(id)
{
	var URL = $.base_url + 'ajax/chat_type_ajax.php';
	
	$.post(URL, 'id='+id, function(res) 
	{ 
		if(res == id)
		{ 
			$('#type-status-'+id).html($.userTypes).css("color", "#009900");
		}
		
		if(res == 'stopped')
		{
			$('#type-status-'+id).html("");
		}
	});
}

// Chat tab function (since 1.1)
function chat_tab()
{
	$.tab = 'chats';
	
	$('#tab-chats').addClass('active-tab');
	 $('#tab-contacts').removeClass('active-tab');
/* 	$('#tab-contacts1').removeClass('active-tab'); */
	var project_ida = $("#messages-stack-list").data("proval");
	var URL = $.base_url + 'ajax/chat_contacts_ajax.php';
	
	$.ajax({
		type: "POST",
		url: URL,
		data: {post_tabs: 'chats', project_idb: project_ida},
		cache: false,
		beforeSend: function() { $('#loadingDiv-chats').show(); },
		error: function() { $('#loadingDiv-chats').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
		success: function(html) {
			$('#loadingDiv-chats').hide();
			$("#messages-stack-list").html(html);
			
			if(typeof next_tab !== "undefined")
			{
				$("li#"+tab_id+".prepare-message").addClass("active-message");
				if($("li#"+tab_id+".prepare-message").hasClass("active-message") == false)
				{
					$("#no_chat_users_found").remove();
					$.newChat = '<li class="prepare-message border-bottom" id="'+tab_id+'">'+cloned_item+'</li>';
					$("ul#messages-stack-list").append($.newChat);
					$("li#"+tab_id+".prepare-message").addClass("active-message");
				}  
			}
		}
	});	
}

// Contacts Tab (since 1.1)
function contacts_tab()
{
	$.tab = 'contacts';
	
 	$('#tab-contacts').addClass('active-tab');
	$('#tab-contacts1').addClass('active-tab');
	$('#tab-chats').removeClass('active-tab'); 
	
	var URL = $.base_url + 'ajax/chat_contacts_ajax.php';

	$.ajax({
		type: "POST",
		url: URL,
		data: {post_tabs: 'contacts'},
		cache: false,
		beforeSend: function() { $('#loadingDiv-contacts').show(); },
		error: function() { $('#loadingDiv-contacts').hide(); $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
		success: function(html) {
			$('#loadingDiv-contacts').hide();
			
			$("#messages-stack-list").html(html);		
// console.log(html);
		}
	});	
}


// Refresh Chats (since 1.1)
function refresh_chats()
{
	

	if($('#tab-'+$.tab).hasClass('active-tab'))
	{
		$('#tab-'+$.tab).addClass('active-tab');	
	} else {
		$('#tab-'+$.tab).removeClass('active-tab')		
	}
	
	var URL = $.base_url + 'ajax/chat_contacts_ajax.php';
var project_ida = $("#messages-stack-list").data("proval");
var oldCont = $("#messages-stack-list").html();

	$.ajax({
		type: "POST",
		url: URL,
		data: {post_tabs: 'chats', project_idb: project_ida},
		cache: false,
		error: function() { $('#errorDiv').html('<p>'+$.ajaxError+'</p>'); },
		success: function(html) {
			
			if($('#tab-chats').hasClass('active-tab'))
			{ 
				$("#messages-stack-list").html(html);
				if($.newChat !== undefined)
				{
					$("#no_chat_users_found").remove();
					$("#messages-stack-list").append($.newChat);
					$.newChat = '';
				}
			}					
		}
	});	
}



var d = $("#text-messages-request");
// Realtime chat (since 1.1)
function realtime_chat()
{
// $("#text-messages-request").getNiceScroll(0).doScrollTop($('#text-messages').height());
	last_msg_id = $(".msg-div").last().attr("id");
	var ID = $("#text-messages").attr("rel");
	var PID = $("#text-messages").data("pid");
	
	var R_URL = $.base_url + 'ajax/refresh_unreadMessages_ajax.php';
	var URL = $.base_url + 'ajax/chat_realtime_ajax.php';
		
	var dataString = 'id='+encodeURIComponent(ID)+'&user_pro_id='+encodeURIComponent(PID);
		
						
	$.post(URL, dataString, function(html) {
		
		var html_response = $(html);
		var new_msg_id = html_response.attr("id");
		 var d = $("#text-messages-request");
		 
		if(new_msg_id !== last_msg_id)
		{
			$("#text-messages").append(html);
			if(new_msg_id){
				 d.scrollTop(d.prop("scrollHeight")); 
				 $( "#tab-chats" ).trigger( "click" );
			}
             
		}	
		
	});
	
	
	// deal with update counter	and typing status
	$('ul#messages-stack-list li').each(function() {
		cID = $(this).attr('id');
		proID = $(this).data('project');
		cString = 'id='+encodeURIComponent(cID)+'&proid='+encodeURIComponent(proID);
		type_status(cID);
		update_unMsg_counter(R_URL, cString, cID, 'realtime');
	});
	title_unread_counter();
				
	return false;
} 

function google_maps(element, lat, long)
{	
	var position = [lat, long]
	
	function showGoogleMaps() 
	{ 	
		var latLng = new google.maps.LatLng(position[0], position[1]);
	
		var mapOptions = {
			zoom: 16, 
			streetViewControl: false,
			scaleControl: true, 
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			center: latLng
		};
	 
		map = new google.maps.Map(element, mapOptions);
	 
		marker = new google.maps.Marker({
			position: latLng,
			map: map,
			draggable: false,
			animation: google.maps.Animation.DROP
		});
	}
	
	showGoogleMaps();
}

jQuery.fn.scrollTo = function(elem) { 
if($(elem).length){
    $(this).scrollTop($(this).scrollTop() - $(this).offset().top + $(elem).offset().top); 
    return this; 
	}
};

setInterval("realtime_chat()", 2000); 
// setInterval("refresh_chats()", 2000);
last_seen();