var last_time = 0; // <--- New
var new_time = 0; // <--- New
// var audio = new Audio($.base_url + '/newAssets/beep/beeps.mp3');

function unread_chat() {
	// var URL = $.base_url + '/message-popup.php';
	// var old_time = $('.msg-menu ul li:first-child').find('.mbox-info-sec').html();

	// $.ajax({
	// 	type: "POST",
	// 	url: URL,
	// 	data: {
	// 		message_page: 'whole_page'
	// 	},
	// 	cache: false,
	// 	error: function () {
	// 		$('.msg-menu ul').html('<li>' + $.ajaxError + '</li>');
	// 	},
	// 	success: function (html) {
	// 		$('.msg-menu ul').html(html);

	// 	}
	// });


}

function counter_up() {
	// var URLB = $.base_url + '/ajax/counter.php';
	// var oldval = $('.msg-icon-img>span').html();
	// $.ajax({
	// 	type: "POST",
	// 	url: URLB,
	// 	data: {
	// 		counter_up: 'update_c'
	// 	},
	// 	cache: false,
	// 	error: function () {
	// 		$('.msg-icon-img>span').html($.ajaxError);
	// 	},
	// 	success: function (html) {
	// 		$('.msg-icon-img>span').html('');
	// 		$('.msg-icon-img>span').html(html);
	// 		if (parseInt(oldval) < parseInt(html)) {
	// 			audio.play();
	// 		}
	// 	}
	// });
}
// setInterval("unread_chat()", 4000);
// setInterval("counter_up()", 4000);
$(document).ready(function () {
	$(".adminlogin").click(function () {
		$(".logemail").val('admin@teameyo.com');
		$(".pass").val('admin12345');
	});
	$(".clientlogin").click(function () {
		$(".logemail").val('client@teameyo.com');
		$(".pass").val('admin12345');
	});
	$(".stafflogin").click(function () {
		$(".logemail").val('staff@teameyo.com');
		$(".pass").val('admin12345');
	});
	$(".alert-savestn").click(function () {
		alert("System setting cannot be change in demo version");
		return false;
	});
	// $(".editprofilebtn").click( function(){
	// 	alert("Profile cannot be edit in demo version");
	// 	return false;
	// });
	$("#inputtoggale").click(function () {
		var x = $(".passwordfield").attr("type");
		if (x === "password") {
			$(".passwordfield").attr("type", "text");
		} else {
			$(".passwordfield").attr("type", "password");
		}
	});

	var winWidth = $(window).width();
	if (winWidth <= 767) {
		$(".mobile-menu i").click(function () {
			$(".sidebar-admin.col-md-3").animate({
				left: 0
			});
		});
		$(".cross-mobile").click(function () {
			$(".sidebar-admin.col-md-3").animate({
				left: '-107%'
			});
		});
		$("#messages-stack-list").on("click", '.prepare-message', function (event) {
			$(".messages-box .messageWrapper").animate({
				right: 0
			});
			$(".messages-box .messageWrapper").removeClass("animatedBox");
		});
		$(".mobilearr").click(function () {
			$(this).parent().animate({
				right: '-100%'
			});
			$(this).parent().addClass("animatedBox");
		});
		$("#text-messages-request").on("click", ".mobilesmenu", function () {

			$(".buttons-cont").fadeToggle("fast");

		});

	} else {
		$(".mobile-menu i").click(function () {
			$(".sidebar-admin.col-md-3").animate({
				left: 0
			});
		});
		$(".cross-mobile").click(function () {
			$(".sidebar-admin.col-md-3").animate({
				left: '-30%'
			});
		});
	}
	$(".btn-action").click(function (e) {
		e.preventDefault();
	});
	var client_val = $("select[name=client]").val();
	$('.new_val').val(client_val);
	$("select[name=client]").change(function () {
		$('.new_val').val($(this).val());
	});

	// Scroll
	$("ul#messages-stack-list, #text-messages-request, #text-messages, .unread-scroll, .admin-nav-area>ul.list-unstyled, .db-box-wrapadmin").niceScroll({
		cursorcolor: "#2f2e2e",
		cursoropacitymax: 0.6,
		boxzoom: false,
		touchbehavior: true,
		autohidemode: false,
		touchbehavior: false
	});
	$("#protbl-input").on("keyup", function () {
		var value = $(this).val().toLowerCase();
		$("#projects-tbl tr:not(.hiddenRow)").filter(function () {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
		});
	});
	$("#staff-searchnew").on("keyup", function () {
		var value = $(this).val().toLowerCase();
		$(".col-sm-3.staff").filter(function () {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
			$(".norecords").hide();
			if ($('.staff:visible').length == 0) {
				$(".norecords").show();
			}
		});
	});
	$(':checkbox[name=btSelectAll]').click(function () {
		var alltrs = $(this).parent().parent().parent().parent().find('tr');
		$(':checkbox[name=btSelectItem]').prop('checked', this.checked);
		var checkbox_val = [];
		$.each($("input[name='btSelectItem']:checked"), function () {
			checkbox_val.push($(this).val());
		});
		var check_fcal = checkbox_val.join(", ");
		console.log("My favourite sports are: " + check_fcal);
		$(".bulk_ids").val(check_fcal);
		if (check_fcal) {
			$(".pm-trash").addClass('active');
			$('button[name="bulk_del_proj"]').prop('disabled', '');
		} else {
			$(".pm-trash").removeClass('active');
			$('button[name="bulk_del_proj"]').prop('disabled', 'disabled');
		}

	});
	$(':checkbox[name=btSelectItem]').click(function () {
		var checkbox_val = [];
		$.each($("input[name='btSelectItem']:checked"), function () {
			checkbox_val.push($(this).val());
		});
		var check_fcal = checkbox_val.join(", ");

		$(".bulk_ids").val(check_fcal);
		if (check_fcal) {
			$(".pm-trash").addClass('active');
			$('button[name="bulk_del_proj"]').prop('disabled', '');
		} else {
			$(".pm-trash").removeClass('active');
			$('button[name="bulk_del_proj"]').prop('disabled', 'disabled');
		}
	});
	$(':checkbox[name=client-checkbox]').click(function () {
		var checkbox_val = [];
		$.each($("input[name='client-checkbox']:checked"), function () {
			checkbox_val.push($(this).val());
		});
		var check_ucal = checkbox_val.join(", ");

		$(".bulk_uids").val(check_ucal);
		if (check_ucal) {
			$(".um-trash").addClass('active');
			$('button[name="bulk_del_user"]').prop('disabled', '');
		} else {
			$(".um-trash").removeClass('active');
			$('button[name="bulk_del_user"]').prop('disabled', 'disabled');
		}
	});


	$(".action-toggle").click(function () {
		$(this).next().toggle();
	});
	$(function () {
		$('[data-toggle="tooltip"]').tooltip();
	});
	var sampage_count = parseInt($("#projects-tbl tr").length);
	var start_val = parseInt($(".resilts-txt span.start_val").html());
	var total_count = sampage_count + start_val;
	$(".resilts-txt span.end_val").html(total_count);
	$(".upload-up").click(function () {
		$(this).parent().parent().find("input").click();
		return false;
	});
	$(function () {
		// Multiple images preview in browser
		var imagesPreview = function (input, placeToInsertImagePreview) {

			if (input.files) {
				var filesAmount = input.files.length;

				for (i = 0; i < filesAmount; i++) {
					var reader = new FileReader();

					reader.onload = function (event) {
						$($.parseHTML('<img>')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
					};

					reader.readAsDataURL(input.files[i]);
				}
			}

		};

		$('input[type="file"]').on('change', function () {
			$(this).parent().find('div.uploaded-img').css('display', 'block');
			$(this).parent().find('div.uploaded-img').html('');
			imagesPreview(this, $(this).parent().find('div.uploaded-img'));
			$(this).parent().find(".remove-up").css('display', 'block');
		});
	});
	$(".remove-up").click(function () {
		$(this).hide();
		$(this).parent().parent().find('div.uploaded-img').hide();
		$(this).parent().parent().find(".uploaded-img").html('');
		$(this).parent().parent().find('input[type="file"]').val('');
		return false;
	});
	$(".setup-btn").click(function () {
		$(this).parent().parent().parent().find('.payment-fields').toggle();
		return false;
	});
	/* Image Upload Profile */
	$('#imgInpb').change(function (event) {
		console.log(1);
		var tmppath = URL.createObjectURL(event.target.files[0]);
		$(".upload-pro-pic .img-uploadwrap img").attr('src', URL.createObjectURL(event.target.files[0]));
	});
	$('#imgInp').change(function (event) {
		console.log(2);
		var tmppath = URL.createObjectURL(event.target.files[0]);
		$(".upload-pro-pic .img-uploadwrap img").attr('src', URL.createObjectURL(event.target.files[0]));
		$(".upload-pro-pic .img-uploadwrap img").css('opacity', '0.4');
		var formData = new FormData();
		formData.append('pro-pic', $(this)[0].files[0]);
		formData.append('editClient', $(this).data('clientid'));
		// uploadFile(formData);

	});

	/*function uploadFile(formData) {
		$.ajax({
			type: "POST",
			url: $.base_url + "/ajax/ajax_profilepic.php",
			data: formData,
			processData: false, // tell jQuery not to process the data
			contentType: false, // tell jQuery not to set contentType
			success: function (data) {
				console.log(data);
				if (data == 'ok') {
					$(".imageupates").show();
					$(".imageupatesfail").hide();
				} else {
					$(".imageupates").hide();
					$(".imageupatesfail").show();

				}
				$(".upload-pro-pic .img-uploadwrap img").css('opacity', '1');
			}
		});
	}*/
	/* Image Upload Profile End*/
	$(".collapse").on('shown.bs.collapse', function () {
		$(this).prev().addClass('show');
	});
	$(".collapse").on('hidden.bs.collapse', function () {
		$(this).prev().removeClass('show');
	});

	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth() + 1; //January is 0!

	var yyyy = today.getFullYear();
	if (dd < 10) {
		dd = '0' + dd;
	}
	if (mm < 10) {
		mm = '0' + mm;
	}
	var today = yyyy + '-' + mm + '-' + dd;
	$(".status1").change(function () {
		var thisval = this.value;
		if (thisval == '1') {
			$(".releaseDate").val(today);
		} else {
			$(".releaseDate").val('1970-01-01');
		}
	});
});
$(document).on("click", function (event) {
	if (!$(event.target).is('.action-toggle.show')) {
		$('.toggle-action.in').hide();
		$('.toggle-action.in').collapse('hide');
	}

});
