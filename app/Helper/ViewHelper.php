<?php

use App\PromocodeUsage;
use App\ServiceType;

function currency($value = '')
{
	return currency_number($value) . config('constants.currency');
}

function currency_number($value = '')
{
	if ($value == "") {
		return number_format(0, 2, '.', '');
	} else {
		return number_format($value, 2, '.', '');
	}
}

function currency_ohter($value = '')
{
	return currency_number_other($value) . config('constants.currency');
}

function currency_number_other($value = '')
{
	if ($value == "") {
		return number_format(0, 2, ',', '');
	} else {
		return number_format($value, 2, ',', '');
	}
}

function appDate($value)
{
	return date('d-m-Y', strtotime($value));
}

function appDateTime($value)
{
	return date('d-m-Y H:i:s', strtotime($value));
}

function distance($value = '')
{
	if ($value == "") {
		return "0 " . config('constants.distance', 'Kms');
	} else {
		return $value . " " . config('constants.distance', 'Kms');
	}
}

function getCompanyName($id)
{
	if($id == 0){
		return \App\Admin::where('id',1)->value('name');
	}
	else{
		return \App\Fleet::where('id',$id)->value('company');
	}
}

function img($img)
{
	if ($img == "") {
		return asset('main/avatar.jpg');
	} else if (strpos($img, 'http') !== false) {
		return $img;
	} else {
		return asset('storage/' . $img);
	}
}

function image($img)
{
	if ($img == "") {
		return asset('main/avatar.jpg');
	} else {
		return asset($img);
	}
}

function promo_used_count($promo_id)
{
	return PromocodeUsage::where('status', 'USED')->where('promocode_id', $promo_id)->count();
}

function curl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$return = curl_exec($ch);
	curl_close($ch);
	return $return;
}

function get_all_service_types()
{
	return ServiceType::all()->where('fleet_id',0);
}

function demo_mode()
{
	if ( Setting::get('demo_mode', 0) == 1) {
		return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appdupe.com');
	}
}

function get_all_language()
{
	return array('fr' => 'French', 'en' => 'English', 'ar' => 'Arabic');
}

function timezone_list()
{
	$zones_array = array();
	// $timestamp = time();
	$zones_array[0]['zone'] = 'Europe/Paris';
	$zones_array[0]['diff_from_GMT'] = 'GMT+1:00 ';
	return $zones_array;
}

function getUserName($id)
{
	return \App\User::where('id',$id)->value('first_name').' '.\App\User::where('id',$id)->value('last_name') ;
}

function getBookingID($id)
{
	return \App\UserRequests::where('id',$id)->value('booking_id');
}
