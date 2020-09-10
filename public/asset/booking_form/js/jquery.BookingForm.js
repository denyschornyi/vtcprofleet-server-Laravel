var booking = {
    preloader : function(action)
    {
        $('#chbs-preloader').css('display',(action ? 'block' : 'none'));
    },

    // get service type id: booking or as soon as possible
    getServiceTypeId: function () {
        return parseInt($('.ui-tabs-active').attr('data-id'), 10)
    },
    //set service type id : booking : 2 possible : 1
    setServiceTypeID: function (serviceTypeId) {
        $('input[name = "chbs_service_type_id"]').val(serviceTypeId)
    },

    setAction : function(name)
    {
        $('input[name="action"]').val('chbs_'+name);
    },
    //get currentStep ID
    getGlobalNotice : function()
    {
         let step = parseInt($('input[name="chbs_step"]').val(),10);
         return step;
    },

    setScreenSetting : function () {
        for(var i= 1; i<=4; i++)
        {
            if(i === booking.getGlobalNotice())
            {
                //initialize the screen
                $('.chbs-main-content-step-'+i).removeClass('display-block');
                $('.chbs-main-content-step-'+i).removeClass('display-hidden');

                $('.chbs-main-content-step-'+i).addClass('display-block');
            }
            else
            {
                //initialize the screen
                $('.chbs-main-content-step-'+i).removeClass('display-block');
                $('.chbs-main-content-step-'+i).removeClass('display-hidden');

                $('.chbs-main-content-step-'+i).addClass('display-hidden')
            }
        }
    },
    //set navigation
    // setMainNavigation : function()
    // {
    //     var step=parseInt($('input[name="chbs_step"]').val(),10);
    //     var element=$('.chbs-main-navigation-default').find('li');
    //     element.removeClass('chbs-state-selected').removeClass('chbs-state-completed');
    //     element.filter('[data-step="'+step+'"]').addClass('chbs-state-selected');
    //     var i=0;
    //     element.each(function()
    //     {
    //         if((++i)>=step) return;
    //
    //         $(this).addClass('chbs-state-completed');
    //     });
    // },

    goToStep : function (stepDelta,callback)
    {
        let step = $('input[name="chbs_step"]');
        let stepRequest = $('input[name="chbs_step_request"]');
        stepRequest.val(parseInt(step.val(),10)+stepDelta);
        //when state is last step, navigation suspend
        if(stepRequest.val() >= '5') return false;

        booking.setAction('go_to_step');

        step.val(parseInt(stepRequest.val()));
        booking.setServiceTypeID(this.getServiceTypeId());

        let serviceTypeId = this.getServiceTypeId();
        if(parseInt(stepRequest.val(),10)===1){
            booking.setScreenSetting();
            booking.setMainNavigation();
        }
        else if(parseInt(stepRequest.val(),10)===2)
        {
            booking.preloader(true);
            booking.setScreenSetting();
            booking.setMainNavigation();
        }else if(parseInt(stepRequest.val(),10)===3)
        {
            booking.preloader(true);
            booking.setScreenSetting();
            booking.setMainNavigation();
        }else if(parseInt(stepRequest.val(),10)===4) {
            booking.preloader(true);
            booking.setScreenSetting();
            booking.setMainNavigation();
        }

        booking.preloader(false);
    },
    checkStep1Validation : function ()
    {
        console.log($('input[name="chbs_waypoint_location_service_type_1[]"]').length);
        if ($('.chbs-datepicker').val() ===''){

            toastr.error("The Date field is required.", 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 3000 });
            return false;
        }else if ($('.chbs-timepicker').val() ===''){
            toastr.error("The Time field is required.", 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 3000 });
            return false;
        }
        else if($('input[name="chbs_pickup_location_service_type_1"]').val() === '')
        {
            toastr.error("The Pickup location field is required.", 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 3000 });
            return false;

        }else if($('input[name="chbs_dropoff_location_service_type_1"]').val() === ''){
            toastr.error("The Drop location field is required.", 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 3000 });
            return false;
        }

        var switch_val = $('.js-switch').prop('checked');
        if(switch_val === true)
        {
            if($('input[name="chbs_pickup_date_service_type_2"]').val() === '')
            {
                toastr.error("The Return date field is required.", 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 3000 });
                return false;
            }
            if($('#return_time_picker').val() === '')
            {
                toastr.error("The Return time field is required.", 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 3000 });
                return false;
            }
        }
        return true;
    },
};

// --------------------step 1 ------------------------

// set chbs_service_type_id as 2 : this is advance booking
$('#ui-id-li-3').click(function () {
    booking.setServiceTypeID(booking.getServiceTypeId());
    $('#panle_1_pickuparea').removeClass('display-hidden');
});
// set chbs_service_type_id as 1 : this is as soon as possible booking
$('#ui-id-li-2').click(function () {
    booking.setServiceTypeID(booking.getServiceTypeId());
    $('#panle_1_pickuparea').addClass('display-hidden');
});

//end ----------step 1 -----------------


$('.chbs-button-step-next').on('click',function () {
    var flag = false;
    var current_step = $('input[name="chbs_step"]').val();
    if(current_step === '1'){
        flag = booking.checkStep1Validation();
    }else if(current_step === '2'){

    }else if(current_step === '3'){

    }else if(current_step === '4'){

    }
    if (flag) booking.goToStep(1);
});


// $('.chbs-button-step-prev').on('click',function () {
//     booking.goToStep(-1);
// });

//
// $('.chbs-main-content-step-4').on('click','.chbs-summary .chbs-summary-header a',function (e) {
//     e.preventDefault();
//
//     booking.goToStep(parseInt($(this).attr('data-step'),10)-4);
// });


// $('.chbs-main-navigation-default').on('click','.chbs-list-reset li',function () {
//     var navigation=parseInt($(this).attr('data-step'),10);
//     var step=parseInt($('input[name="chbs_step"]').val(),10);
//
//     if(navigation-step===0) return;
    // var flag = false;
    // var current_step = $('input[name="chbs_step"]').val();
    // if(current_step === '1'){
    //     flag = booking.checkStep1Validation();
    // }else if(current_step === '2'){
    //
    // }else if(current_step === '3'){
    //
    // }else if(current_step === '4'){
    //
    // }
    // if(flag)
//     booking.goToStep(navigation-step);
// });










