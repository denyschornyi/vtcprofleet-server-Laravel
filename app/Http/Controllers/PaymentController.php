<?php

namespace App\Http\Controllers;

use App\AdminWallet;
use App\Card;
use App\Fleet;
use App\Admin;
use App\Helpers\PaytmLibrary;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\SendPushNotification;
use App\Provider;
use App\PaymentLog;
use App\ProviderCard;
use App\ProviderWallet;
use App\Services\PaymentGateway;
use App\User;
use App\UserRequestPayment;
use App\UserRequests;
use App\UserWallet;
use App\WalletRequests;
use App\UserWalletRequest;
use Auth;
use Exception;
use Illuminate\Http\Request;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Stripe\Stripe;
use Tzsk\Payu\Facade\Payment as PayuPayment;
use App\FleetPaymentSettings;

class PaymentController extends Controller
{
	/**
	 * payment for user.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function payment(Request $request)
	{
		// $this->validate($request, [
		// 	'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,' . Auth::user()->id
		// ]);
		
		$UserRequest = UserRequests::find($request->request_id);

		$paymentMode = $request->has('payment_mode') ? strtoupper($request->payment_mode) : $UserRequest->payment_mode;
		$tip_amount = 0;
		
		$random = config('constants.booking_prefix') . mt_rand(100000, 999999);
		$RequestPayment = UserRequestPayment::where('request_id', $request->request_id)->first();
		$fleet_provider_id = $RequestPayment->fleet_id;
		$fleet_user_id = UserRequests::where('id', $request->request_id)->value('fleet_id');
		// //admin commission to pool
		// if($fleet_user_id != '0') {
		// 	$str = Fleet::where('id', $fleet_user_id)->value('pool');
		// 	$comm_vals = explode(';', $str);
		// 	$count1 = 0;
		// 	foreach($comm_vals as $index => $comm_val){
		// 		if(strpos($comm_val, 'credit0') !== false){
		// 			$credit_ary = explode('_', $comm_val);
					
		// 			$credit_ary[1] += $RequestPayment->admin_commission;
		// 			$comm_vals[$index] = implode('_', $credit_ary);
		// 			$count1++;
		// 		break;
		// 		}
		// 	}
			
		// 	if($count1 == 0) {
		// 		$str = $str.'credit0_'.$RequestPayment->admin_commission.';';
		// 		Fleet::where('id', $fleet_user_id)->update(['pool' => $str]);
		// 	}
		// 	else{
		// 		$pool_val = implode(';', $comm_vals);
		// 		Fleet::where('id', $fleet_user_id)->update(['pool' => $pool_val]);
		// 	}

		// 	$str1 = Admin::where('id', 1)->value('pool');
		// 	$comm_vals1 = explode(';', $str1); 
		// 	$count2 = 0;
		// 	foreach($comm_vals1 as $key => $comm_val1) {
		// 		if(strpos($comm_val1, 'debit'.$fleet_user_id) !== false){
		// 			$credit_ary = explode('_', $comm_val1);
		// 			$credit_ary[1] += $RequestPayment->admin_commission;
		// 			$comm_vals1[$key] = implode('_', $credit_ary);
		// 			$count2++;
		// 		break;
		// 		}
		// 	}
		// 	if($count2 == 0) {
		// 		$str1 = $str1.'debit'.$fleet_user_id.'_'.$RequestPayment->admin_commission.';';
		// 		Admin::where('id', 1)->update(['pool' => $str1]);
		// 	}
		// 	else {
				
		// 		$pool_val = implode(';', $comm_vals1);
		// 		Admin::where('id', 1)->update(['pool'=>$pool_val]);
		// 	}

		// }

		// //pool balance
		// if($fleet_user_id != $fleet_provider_id)
		// {	
			
		// 	$credit_comm = $RequestPayment->tax + $RequestPayment->pool_commission + $RequestPayment->admin_commission;
		// 	$debit_comm = $RequestPayment->total - ($RequestPayment->tax + $RequestPayment->pool_commission + $RequestPayment->admin_commission);
			
		// 	if($fleet_user_id == '0') {
		// 		if($UserRequest->payment_mode == 'CASH'){
		// 			$str1 = Fleet::where('id', $fleet_provider_id)->value('pool');
		// 			$comm1_vals = explode(';', $str1);
		// 			$count1 = 0;
		// 			foreach($comm1_vals as $index1 => $comm1_val){
		// 				if(strpos($comm1_val, 'credit0') !== false) {
		// 					$credit_ary1 = explode('_', $comm1_val);
		// 					$credit_ary1[1] += $credit_comm;
		// 					$comm1_vals[$index1] = implode('_', $credit_ary1);
		// 					$count1++;
		// 				break;
		// 				}
		// 			}
		// 			if($count1 == 0) {
		// 				$str1 = $str1.'credit0_'.$credit_comm.';';
		// 			}
		// 			else{
		// 				$str1 = implode(';', $comm1_vals);
		// 			}
		// 			Fleet::where('id', $fleet_provider_id)->update(['pool'=>$str1]);

		// 			// $update_credit_comm = Fleet::where('id', $provider_fleetId)->value('credit_0') + $credit_comm; 
		// 			// Fleet::where('id', $provider_fleetId)->update(['credit_'.$user_fleetId => $update_credit_comm]);
		// 			$str2 = Admin::where('id', 1)->value('pool');
		// 			$comm2_vals = explode(';', $str2);
		// 			$count2 = 0;
		// 			foreach($comm2_vals as $index2 => $comm2_val) {
		// 				if(strpos($comm2_val, 'debit'.$fleet_provider_id) !== false){
		// 					$credit_ary2 = explode('_', $comm2_val);
		// 					$credit_ary2[1] += $credit_comm;
		// 					$comm2_vals[$index2] = implode('_', $credit_ary2);
		// 					$count2++;
		// 				break;
		// 				}
		// 			}
		// 			if($count2 == 0) {
		// 				$str2 = $str2.'debit'.$fleet_provider_id.'_'.$credit_comm.';';
		// 			}
		// 			else{
		// 				$str2 = implode(';', $comm2_vals);
		// 			}
		// 			Admin::where('id', 1)->update(['pool' => $str2]);

		// 			// $update_debit_comm = Admin::where('id', 1)->value('credit_'.$provider_fleetId) + $credit_comm;
		// 			// Admin::where('id', 1)->update(['debit_'.$provider_fleetId => $update_debit_comm]);
		// 		}
		// 		else{
		// 			$str1 = Admin::where('id', 1)->value('pool');
		// 			$comm1_vals = explode(';', $str1);
		// 			$count1 = 0;
		// 			foreach($comm1_vals as $index1 => $comm1_val) {
		// 				if(strpos($comm1_val, 'credit'.$fleet_provider_id) !== false){
		// 					$debit_ary1 = explode('_', $comm1_val);
		// 					$debit_ary1[1] += $debit_comm;
		// 					$comm1_vals[$index1] = implode('_', $debit_ary1);
		// 					$count1++;
		// 				break;
		// 				}
		// 			}
		// 			if($count1 == 0) {
		// 				$str1 = $str1.'credit'.$fleet_provider_id.'_'.$debit_comm.';';
		// 			}
		// 			else{
		// 				$str1 = implode(';', $comm1_vals);
		// 			}
		// 			Admin::where('id', 1)->update(['pool'=>$str1]);

		// 			$str2 = Fleet::where('id', $fleet_provider_id)->value('pool');
		// 			$comm2_vals = explode(';', $str2);
		// 			$count2 = 0;
		// 			foreach($comm2_vals as $index2 => $comm2_val) {
		// 				if(strpos($comm2_val, 'debit0') !== false){
		// 					$debit_ary2 = explode('_', $comm2_val);
		// 					$debit_ary2[1] += $debit_comm;
		// 					$comm2_vals[$index2] = implode('_', $debit_ary2);
		// 					$count2++;
		// 				break;
		// 				}
		// 			}
		// 			if($count2 == 0) {
		// 				$str2 = $str2.'debit0_'.$debit_comm.';';
		// 			}
		// 			else {
		// 				$str2 = implode(';', $comm2_vals);
		// 			}
		// 			Fleet::where('id', $fleet_provider_id)->update(['pool' => $str2]);
		// 		}
		// 	}
		// 	else{
		// 		if($fleet_provider_id == '0'){
		// 			if($UserRequest->payment_mode == 'CASH'){
						
		// 				$str1 = Fleet::where('id', $fleet_user_id)->value('pool');
		// 				$comm1_vals = explode(';', $str1);
		// 				$count1 = 0;
		// 				foreach($comm1_vals as $index1 => $comm1_val){
		// 					if(strpos($comm1_val, 'debit0') !== false) {
		// 						$credit_ary1 = explode('_', $comm1_val);
		// 						$credit_ary1[1] += $credit_comm;
		// 						$comm1_vals[$index1] = implode('_', $credit_ary1);
		// 						$count1++;
		// 					break;
		// 					}
		// 				}
		// 				if($count1 == 0) {
		// 					$str1 = $str1.'debit0_'.$credit_comm.';';
		// 				}
		// 				else{
		// 					$str1 = implode(';', $comm1_vals);
		// 				}
		// 				Fleet::where('id', $fleet_user_id)->update(['pool'=>$str1]);

		// 				$str2 = Admin::where('id', 1)->value('pool');
		// 				$comm2_vals = explode(';', $str2);
		// 				$count2 = 0;
		// 				foreach($comm2_vals as $index2 => $comm2_val){
		// 					if(strpos($comm2_val, 'credit'.$fleet_user_id) !== false){
		// 						$credit_ary2 = explode('_', $comm2_val);
		// 						$credit_ary2[1] += $credit_comm;
		// 						$comm2_vals[$index2] = implode('_', $credit_ary2);
		// 						$count2++;
		// 					break;
		// 					}
		// 				}
		// 				if($count2 == 0) {
		// 					$str2 = $str2.'credit'.$fleet_user_id.'_'.$credit_comm.';';
		// 				}
		// 				else{
		// 					$str2 = implode(';', $comm2_vals);
		// 				}
		// 				Admin::where('id', 1)->update(['pool' => $str2]);
		// 			}
		// 			else{
		// 				$str1 = Fleet::where('id', $fleet_user_id)->value('pool');
		// 				$comm1_vals = explode(';', $str1);
		// 				$count1 = 0;
		// 				foreach($comm1_vals as $index1 => $comm1_val){
		// 					if(strpos($comm1_val, 'credit0') !== false){
		// 						$debit_ary1 = explode('_', $comm1_val);
		// 						$debit_ary1[1] += $debit_comm;
		// 						$comm1_vals[$index1] = implode('_', $debit_ary1);
		// 						$count1++;
		// 					break;
		// 					}
		// 				}
		// 				if($count1 == 0) {
		// 					$str1 = $str1.'credit0_'.$debit_comm.';';
		// 				}
		// 				else{
		// 					$str1 = implode(';', $comm1_vals);
		// 				}
		// 				Fleet::where('id', $fleet_user_id)->update(['pool' => $str1]);

		// 				$str2 = Admin::where('id', 1)->value('pool');
		// 				$comm2_vals = explode(';', $str2);
		// 				$count2 = 0;
		// 				foreach($comm2_vals as $index2 => $comm2_val){
		// 					if(strpos($comm2_val, 'debit'.$fleet_user_id) !== false){
		// 						$debit_ary2 = explode('_', $comm2_val);
		// 						$debit_ary2[1] += $debit_comm;
		// 						$comm2_vals[$index2] = implode('_', $debit_ary2);
		// 						$count2++;
		// 					break;
		// 					}
		// 				}
		// 				if($count2 == 0){
		// 					$str2 = $str2.'debit'.$fleet_user_id.'_'.$debit_comm.';';
		// 				}
		// 				else{
		// 					$str2 = implode(';', $comm2_vals);
		// 				}
		// 				Admin::where('id', 1)->update(['pool' => $str2]);

						
		// 			}
		// 		}
		// 		else{
		// 			if($UserRequest->payment_mode == 'CASH') {
		// 				$str1 = Fleet::where('id', $fleet_provider_id)->value('pool');
		// 				$comm1_vals = explode(';', $str1);
		// 				$count1 = 0;
		// 				foreach($comm1_vals as $index1 => $comm1_val){
		// 					if(strpos($comm1_val, 'credit'.$fleet_user_id) !== false){
		// 						$credit_ary1 = explode('_', $comm1_val);
		// 						$credit_ary1[1] += $credit_comm;
		// 						$comm1_vals[$index1] = implode('_', $credit_ary1);
		// 						$count1++;
		// 					break;
		// 					}
		// 				}
		// 				if($count1 == 0) {
		// 					$str1 = $str1.'credit'.$fleet_user_id.'_'.$credit_comm.';';
		// 				}
		// 				else{
		// 					$str1 = implode(';', $comm1_vals);
		// 				}
		// 				Fleet::where('id', $fleet_provider_id)->update(['pool' => $str1]);

		// 				$str2 = Fleet::where('id', $fleet_user_id)->value('pool');
		// 				$comm2_vals = explode(';', $str2);
		// 				$count2 = 0;
		// 				foreach($comm2_vals as $index2 => $comm2_val) {
		// 					if(strpos($comm2_val, 'debit'.$fleet_provider_id) !== false){
		// 						$credit_ary2 = explode('_', $comm2_val);
		// 						$credit_ary2[1] += $credit_comm;
		// 						$comm2_vals[$index2] = implode('_', $credit_ary2);
		// 						$count2++;
		// 					break;
		// 					}
		// 				}
		// 				if($count2 == 0) {
		// 					$str2 = $str2.'debit'.$fleet_provider_id.'_'.$credit_comm.';';
		// 				}
		// 				else{
		// 					$str2 = implode(';', $comm2_vals);
		// 				}
		// 				Fleet::where('id', $fleet_user_id)->update(['pool'=>$str2]);

						
		// 			}
		// 			else{

		// 				$str1 = Fleet::where('id', $fleet_user_id)->value('pool');
		// 				$comm1_vals = explode(';', $str1);
		// 				$count1 = 0;
		// 				foreach($comm1_vals as $index1 => $comm1_val){
		// 					if(strpos($comm1_val, 'credit'.$fleet_provider_id) !== false){
		// 						$debit_ary1 = explode('_', $comm1_val);
		// 						$debit_ary1[1] += $debit_comm;
		// 						$comm1_vals[$index1] = implode('_', $debit_ary1);
		// 						$count1++;
		// 					break;
		// 					}
		// 				}
		// 				if($count1 == 0){
		// 					$str1 = $str1.'credit'.$fleet_provider_id.'_'.$debit_comm.';';
		// 				}
		// 				else{
		// 					$str1 = implode(';', $comm1_vals);
		// 				}
		// 				Fleet::where('id', $fleet_user_id)->update(['pool' => $str1]);

		// 				$str2 = Fleet::where('id', $fleet_provider_id)->value('pool');
		// 				$comm2_vals = explode(';', $str2);
		// 				$count2 = 0;
		// 				foreach($comm2_vals as $index2 => $comm2_val){
		// 					if(strpos($comm2_val, 'debit'.$fleet_user_id) !== false){
		// 						$debit_ary2 = explode('_', $comm2_val);
		// 						$debit_ary2[1] += $debit_comm;
		// 						$comm2_vals[$index2] = implode('_', $debit_ary2);
		// 						$count2++;
		// 					break;
		// 					}
		// 				}
		// 				if($count2 == 0){
		// 					$str2 = $str2.'debit'.$fleet_user_id.'_'.$debit_comm.';';
		// 				}
		// 				else{
		// 					$str2 = implode(';', $comm2_vals);
		// 				}
		// 				Fleet::where('id', $fleet_provider_id)->update(['pool'=>$str2]);
		// 			}
		// 		}
		// 	}
		// }
		if ($paymentMode != 'CASH') {
			
			if (isset($request->tips) && !empty($request->tips)) {
				$tip_amount = round($request->tips, 2);
				$RequestPayment->tips = $tip_amount;
				$RequestPayment->provider_pay = $RequestPayment->provider_pay + $tip_amount;
				$RequestPayment->save();
			}
			if($tip_amount > 0 && $fleet_user_id != $fleet_provider_id){

				// $user_wallet = UserWallet::where('id', $request->request_id)->first();
				// $user_wallet->close_balance = $user_wallet->close_balance - $tip_amount;
				// $user_wallet->save();
				// User::where('id', $RequestPayment->user_id)->update(['wallet_balance' => $user_wallet->close_balance]);
				if($fleet_user_id == '0'){
					$admin_pool_data = Admin::where('id', 1)->value('pool');
					$pool_data1 = explode(';', $admin_pool_data);
					$count1 = 0;
					foreach($pool_data1 as $index1 => $value){
						if(strpos($value, 'credit'.$fleet_provider_id) !== false){
							$val_ary1 = explode('_', $value);
							$val_ary1[1] += $tip_amount;
							$pool_data1[$index1] = implode('_', $val_ary1);
							$count1++;
						}
					}
					if($count1 == 0){
						$admin_pool_data = $admin_pool_data.'credit'.$fleet_provider_id.'_'.$tip_amount.';';
					}
					else{
						$admin_pool_data = implode(';', $pool_data1);
					}
					Admin::where('id', 1)->update(['pool' => $admin_pool_data]);

					
					$fleet_pool_data = Fleet::where('id', $fleet_provider_id)->value('pool');
					$pool_data2 = explode(';', $fleet_pool_data);
					$count2 = 0;
					foreach($pool_data2 as $index2 => $val){
						if(strpos($val, 'debit0') !== false){
							$val_ary2 = explode('_', $val);
							$val_ary2[1] += $tip_amount;
							$pool_data2[$index2] = implode('_', $val_ary2);
							$count2++;
						}
					}
					if($count2 == 0){
						$fleet_pool_data = $fleet_pool_data.'debit0_'.$tip_amount.';';
					}
					else{
						$fleet_pool_data = implode(';', $pool_data2);
					}
					Fleet::where('id', $fleet_provider_id)->update(['pool'=>$fleet_pool_data]);
				}

				else if($fleet_provider_id == 0){
					$admin_pool_data = Admin::where('id', 1)->value('pool');
					$pool_data1 = explode(';', $admin_pool_data);
					$count1 = 0;
					foreach($pool_data1 as $index1 => $val1){
						if(strpos($val1, 'debit'.$fleet_user_id) !== false){
							$val_ary1 = explode('_', $val1);
							$val_ary1[1] += $tip_amount;
							$pool_data1[$index1] = implode('_', $val_ary1);
							$count1++;
						}
					}
					if($count1 == 0){
						$admin_pool_data = $admin_pool_data.'debit'.$fleet_user_id.'_'.$tip_amount.';';
					}
					else{
						$admin_pool_data = implode(';', $pool_data1);
					}
					Admin::where('id', 1)->update(['pool'=>$admin_pool_data]);

					$fleet_pool_data = Fleet::where('id', $fleet_user_id)->value('pool');
					$pool_data2 = explode(';', $fleet_pool_data);
					$count2 = 0;
					foreach($pool_data2 as $index2 => $val2){
						if(strpos($val2, 'credit0') !== false){
							$val_ary2 = explode('_', $val2);
							$val_ary2[1] += $tip_amount;
							$pool_data2[$index2] = implode('_', $val_ary2);
							$count2++;
						}
					}
					if($count2 == 0){
						$fleet_pool_data = $fleet_pool_data.'credit0_'.$tip_amount.';';
					}
					else{
						$fleet_pool_data = implode(';', $pool_data2);
					}
					Fleet::where('id', $fleet_user_id)->update(['pool' => $fleet_pool_data]);
				}

				else{
					$fleet_pool_data1 = Fleet::where('id', $fleet_user_id)->value('pool');
					$pool_data1 = explode(';', $fleet_pool_data1);
					$count1 = 0;
					foreach($pool_data1 as $index1 => $val1){
						if(strpos($val1, 'credit'.$fleet_provider_id) !== false){
							$val_ary1 = explode('_', $val1);
							$val_ary1[1] += $tip_amount;
							
							$pool_data1[$index1] = implode('_', $val_ary1);
							$count1++;
						}
					}
					if($count1 == 0){
						$fleet_pool_data1 = $fleet_pool_data1.'credit'.$fleet_provider_id.'_'.$tip_amount.';';
					}
					else{
						$fleet_pool_data1 = implode(';', $pool_data1);
					}
					Fleet::where('id', $fleet_user_id)->update(['pool' => $fleet_pool_data1]);

					$fleet_pool_data2 = Fleet::where('id', $fleet_provider_id)->value('pool');
					$pool_data2 = explode(';', $fleet_pool_data2);
					$count2 = 0;
					foreach($pool_data2 as $index2 => $val2){
						if(strpos($val2, 'debit'.$fleet_user_id) !== false){
							$val_ary2 = explode('_', $val2);
							$val_ary2[1] += $tip_amount;
							$pool_data2[$index2] = implode('_', $val_ary2);
							$count2++;
						}
					}
					if($count2 == 0){
						$fleet_pool_data2 = $fleet_pool_data2.'debit'.$fleet_user_id.'_'.$tip_amount.';';
					}
					else{
						$fleet_pool_data2 = implode(';', $pool_data2);
					}
					Fleet::where('id', $fleet_provider_id)->update(['pool' => $fleet_pool_data2]);
				}
			}
			$totalAmount = $RequestPayment->total + $tip_amount;
			if ($totalAmount == 0) {
				$UserRequest->payment_mode = $paymentMode;
				$RequestPayment->card = $RequestPayment->payable;
				$RequestPayment->payable = 0;
				$RequestPayment->tips = $tip_amount;
				
				$RequestPayment->save();

				//for create the transaction
				(new TripController)->callTransaction($request->request_id);
				if($paymentMode == 'WALLET'){
					$gateway = new PaymentGateway('wallet');
					return $gateway->process([
						'order' => $random
					]);
				}
				if ($request->ajax()) {
					return response()->json(['message' => trans('api.paid')]);
				} else {
					return redirect('dashboard')->with('flash_success', trans('api.paid'));
				}
			} else {
				
				$log = new PaymentLog();
				$log->user_type = 'user';
				$log->transaction_code = $random;
				$log->amount = $totalAmount;
				$log->transaction_id = $UserRequest->id;
				$log->payment_mode = $paymentMode;
				$log->user_id = \Auth::user()->id;
				// if($paymentMode == 'WALLET') $log->is_wallet = 1;
				$log->save();

				switch ($paymentMode) {
					case 'WALLET': 
						$gateway = new PaymentGateway('wallet');
						return $gateway->process([
							'order' => $random
						]);
						break;
					case 'BRAINTREE':

						$gateway = new PaymentGateway('braintree');

						return $gateway->process([
							'amount' => $totalAmount,
							'nonce' => $UserRequest->braintree_nonce,
							'order' => $random,
						]);

						break;

					case 'CARD':

						$stripe_secret_key = config('constants.stripe_secret_key', '');
						if (Auth::user()->fleet_id > 0) {
							$FleetPaymentSettings = FleetPaymentSettings::where( 'fleet_id', \Auth::user()->fleet_id )->first();
							if ($FleetPaymentSettings != null) {
								$stripe_secret_key = $FleetPaymentSettings->stripe_secret_key;
							}
						}
						
						$Card = Card::where('user_id', Auth::user()->id)->where('is_default', 1)->first();

						if ($Card == null)  $Card = Card::where('user_id', Auth::user()->id)->first();
						// UserRequests::where('id', $request->request_id)->update(['paid'=>'1']);
						$gateway = new PaymentGateway('stripe');
						return $gateway->process([
							'stripe_secret_key' => $stripe_secret_key,
							'order' => $random,
							"amount" => $totalAmount,
							"currency" => config('constants.stripe_currency'),
							"customer" => Auth::user()->stripe_cust_id,
							"card" => $Card->card_id,
							"description" => "Payment Charge for " . Auth::user()->email,
							"receipt_email" => Auth::user()->email,
						]);

						break;

					case 'PAYUMONEY':

						if ($request->ajax()) {

							$paramList = [
								'key' => config('constants.payumoney_key'),
								'txnid' => $random,
								'amount' => $totalAmount,
								'productinfo' => $random,
								'firstname' => Auth::user()->first_name,
								'email' => Auth::user()->email,
								'phone' => Auth::user()->mobile,
							];

							$paramList['surl'] = url('api/user/payu/success');
							$paramList['curl'] = url('api/user/payu/failure');
							$paramList['service_provider'] = 'payumoney';
							$paramList['merchant_id'] = config('constants.payumoney_merchant_id');
							$paramList['payu_salt'] = config('constants.payumoney_salt');

							$hash = '';
							// Hash Sequence
							$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

							$hashVarsSeq = explode('|', $hashSequence);
							$hash_string = '';
							foreach ($hashVarsSeq as $hash_var) {
								$hash_string .= isset($paramList[$hash_var]) ? $paramList[$hash_var] : '';
								$hash_string .= '|';
							}

							$hash_string .= config('constants.payumoney_salt');

							$paramList['hash_string'] = $hash_string;
							$paramList['hash'] = hash('sha512', $hash_string);

							return response()->json($paramList, 200);
						}

						$gateway = new PaymentGateway('payumoney');
						return $gateway->process([
							'order' => $random,
							'txnid' => $random,
							'amount' => $totalAmount,
							'productinfo' => "New Transaction #" . $UserRequest->booking_id,
							'firstname' => Auth::user()->first_name,
							'email' => Auth::user()->email,
							'phone' => Auth::user()->mobile,
						]);

						break;

					case 'PAYPAL':

						$gateway = new PaymentGateway('paypal');
						return $gateway->process([
							'order' => $random,
							'item_name' => $random,
							'item_currency' => config('constants.paypal_currency'),
							'item_quantity' => 1,
							'amount' => $totalAmount,
							'description' => 'Test',
						]);

						break;

					case 'PAYPAL-ADAPTIVE':

						$gateway = new PaymentGateway('paypal-adaptive');

						$provider = Provider::find($UserRequest->provider_id);

						$provider_amount = 10;

						if ($provider->paypal_email != null) {

							$primary_email = config('constants.paypal_email', '');
							$secondary_email[] = ['secondary_email' => $provider->paypal_email, 'amount' => $provider_amount];

							return $gateway->process([
								'order' => $random,
								'primary_email' => $primary_email,
								'secondary_email' => $secondary_email,
								'amount' => $totalAmount,
								'payer' => "EACHRECEIVER",
							]);
						} else {
							return redirect('dashboard')->with('flash_error', 'Please choose another payment method!');
						}

						break;

					case 'PAYTM':

						if ($request->ajax()) {

							$callback_url = (config('constants.paytm_environment') == 'local') ? 'https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=' . $random : 'https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=' . $random;

							$paramList["MID"] = config('constants.paytm_merchant_id');
							$paramList["ORDER_ID"] = $random;
							$paramList["CUST_ID"] = (String)Auth::user()->id;
							$paramList["INDUSTRY_TYPE_ID"] = config('constants.paytm_industry_type');
							$paramList["CHANNEL_ID"] = "WAP";
							$paramList["TXN_AMOUNT"] = (double)$totalAmount;

							$paramList["WEBSITE"] = 'APPSTAGING';
							$paramList["CALLBACK_URL"] = $callback_url;
							$paramList["MOBILE_NO"] = Auth::user()->mobile;
							$paramList["EMAIL"] = Auth::user()->email;
							$paramList["CHECKSUMHASH"] = PaytmLibrary::getChecksumFromArray($paramList, config('constants.paytm_merchant_key'));

							return response()->json($paramList, 200);
						}

						$gateway = new PaymentGateway('paytm');

						return $gateway->process([
							'order' => $random,
							'user' => Auth::user()->first_name,
							'mobile_number' => Auth::user()->mobile,
							'email' => Auth::user()->email,
							'amount' => $totalAmount,
							'callback_url' => url('/paytm/response'),
						]);

						break;
				}
			}
		}
		
	}

	/**
	 * add wallet money for user.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add_money(Request $request)
	{
		$random = config('constants.booking_prefix') . mt_rand(100000, 999999);

		$user_type = $request->user_type;

		$log = new PaymentLog();
		$log->user_type = $user_type;
		$log->is_wallet = '1';
		$log->amount = $request->amount;
		$log->transaction_code = $random;
		$log->payment_mode = strtoupper($request->payment_mode);
		$log->user_id = \Auth::user()->id;
		$log->save();

		switch (strtoupper($request->payment_mode)) {

			case 'BRAINTREE':

				$gateway = new PaymentGateway('braintree');
				return $gateway->process([
					'amount' => $request->amount,
					'nonce' => $request->braintree_nonce,
					'order' => $random,
				]);

				break;

			case 'CARD':
				$fleet_id = 0;
				if ($user_type == 'provider') {

					//$Card = ProviderCard::where('user_id', $request->card_id)->first();

					ProviderCard::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
					ProviderCard::where('card_id', $request->card_id)->update(['is_default' => 1]);
					$fleet_id = Auth::user()->fleet;
				} else {

					//$Card = Card::where('user_id', $request->card_id)->first();

					Card::where('user_id', Auth::user()->id)->update(['is_default' => 0]);
					Card::where('card_id', $request->card_id)->update(['is_default' => 1]);
					
					
					$fleet_id = Auth::user()->fleet_id;
				}

				$stripe_secret_key = config('constants.stripe_secret_key', '');
				if ($fleet_id != 0) {
					$FleetPaymentSettings = FleetPaymentSettings::where('fleet_id', $fleet_id)->first();
           			$stripe_secret_key = $FleetPaymentSettings == null ? '' : $FleetPaymentSettings->stripe_secret_key;
				}

				$gateway = new PaymentGateway('stripe');
				return $gateway->process([
					"stripe_secret_key" => $stripe_secret_key,
					"order" => $random,
					"amount" => $request->amount,
					"currency" => config('constants.stripe_currency'),
					"customer" => Auth::user()->stripe_cust_id,
					// "customer" => ($user_type == 'provider') ? Auth::user()->stripe_acc_id : Auth::user()->stripe_cust_id,
					"card" => $request->card_id,
					"description" => "Adding Money for " . Auth::user()->email,
					"receipt_email" => Auth::user()->email,
					"type" => '',
					// "type" => ($user_type == 'provider') ? 'connected_account' : '',
				]);

				break;

			case 'PAYUMONEY':

				if ($request->ajax()) {

					$paramList = [
						'key' => config('constants.payumoney_key'),
						'txnid' => $random,
						'amount' => $request->amount,
						'productinfo' => "Wallet",
						'firstname' => Auth::user()->first_name,
						'email' => Auth::user()->email,
						'phone' => Auth::user()->mobile,
					];

					$paramList['surl'] = url('api/user/payu/response');
					$paramList['curl'] = url('api/user/payu/failure');
					$paramList['service_provider'] = 'payumoney';
					$paramList['merchant_id'] = config('constants.payumoney_merchant_id');
					$paramList['payu_salt'] = config('constants.payumoney_salt');

					$hash = '';
					// Hash Sequence
					$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

					$hashVarsSeq = explode('|', $hashSequence);
					$hash_string = '';
					foreach ($hashVarsSeq as $hash_var) {
						$hash_string .= isset($paramList[$hash_var]) ? $paramList[$hash_var] : '';
						$hash_string .= '|';
					}

					$hash_string .= config('constants.payumoney_salt');

					$paramList['hash_string'] = $hash_string;
					$paramList['hash'] = hash('sha512', $hash_string);

					return response()->json($paramList, 200);
				}

				$gateway = new PaymentGateway('payumoney');
				return $gateway->process([
					'order' => $random,
					'txnid' => $random,
					'amount' => $request->amount,
					//Alias is used to trck the transaction, if it is failed we can remove that entry
					'productinfo' => 'Wallet',
					'firstname' => Auth::user()->first_name, # Payee Name.
					'email' => Auth::user()->email, # Payee Email Address.
					'phone' => Auth::user()->mobile, # Payee Phone Number.
				]);

				break;

			case 'PAYPAL':

				$gateway = new PaymentGateway('paypal');
				return $gateway->process([
					'order' => $random,
					'item_name' => 'Item',
					'item_currency' => config('constants.paypal_currency'),
					'item_quantity' => 1,
					'amount' => $request->amount,
					'description' => 'Wallet Money added',
				]);

				break;

			case 'PAYTM':

				if ($request->ajax()) {

					$callback_url = (config('constants.paytm_environment') == 'local') ? 'https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=' . $random : 'https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=' . $random;

					$paramList["MID"] = config('constants.paytm_merchant_id');
					$paramList["ORDER_ID"] = $random;
					$paramList["CUST_ID"] = (String)Auth::user()->id;
					$paramList["INDUSTRY_TYPE_ID"] = config('constants.paytm_industry_type');
					$paramList["CHANNEL_ID"] = "WAP";
					$paramList["TXN_AMOUNT"] = (double)$request->amount;

					$paramList["WEBSITE"] = 'APPSTAGING';
					$paramList["CALLBACK_URL"] = $callback_url;
					$paramList["MOBILE_NO"] = Auth::user()->mobile;
					$paramList["EMAIL"] = Auth::user()->email;
					$paramList["CHECKSUMHASH"] = PaytmLibrary::getChecksumFromArray($paramList, config('constants.paytm_merchant_key'));

					return response()->json($paramList, 200);
				}

				$gateway = new PaymentGateway('paytm');

				$provider_url = '';

				if ($request->type == 'provider') {
					$provider_url = '/provider';
				}

				return $gateway->process([
					'order' => $random,
					'user' => Auth::user()->first_name,
					'mobile_number' => Auth::user()->mobile,
					'email' => Auth::user()->email,
					'amount' => $request->amount,
					'callback_url' => url($provider_url . '/paytm/response'),
				]);

				break;

			case 'WIREBANK':
			case 'CHEQUE':
				try {
					$wallet = new UserWalletRequest;
					$wallet->user_id = \Auth::user()->id;
					$wallet->alias_id = 'UREQ' . mt_rand(100000, 999999);
					$wallet->amount = $request->amount;
					$wallet->type = strtoupper($request->payment_mode);
					$wallet->save();
					return back()->with('flash_success', 'Request Succesfully');
				} catch (Exception $e) {
					return back()->with('flash_error', 'Something went wrong. Please check payment method and money');
				}
				break;
			default:
				return back()->with('flash_error', 'Something went wrong. Please check payment method and money');
				break;
		}
	}

	/**
	 * send money to provider or fleet.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function send_money(Request $request, $id)
	{

		try {

			$Requests = WalletRequests::where('id', $id)->first();

			if ($Requests->request_from == 'provider') {
				$provider = Provider::find($Requests->from_id);
				$stripe_acc_id = $provider->stripe_cust_id;
				$email = $provider->email;
			} else {
				if($Requests->from_id != '0'){
					$fleet = Fleet::find($Requests->from_id);
					$stripe_acc_id = $fleet->stripe_acc_id;
					$email = $fleet->email;
				}
				else{
					// Card::where('user_id', 1)->value('card_id');
					$admin = Admin::where('id', 1)->first();
					$stripe_acc_id = $admin->stripe_acc_id;
					$email = $admin->email;
				}
			}

			if (empty($stripe_acc_id)) {
				throw new Exception(trans('admin.payment_msgs.account_not_found'));
			}

			$StripeCharge = $Requests->amount;
			// var_dump($stripe_acc_id);
			// exit;
			Stripe::setApiKey(config('constants.stripe_secret_key'));

			$tranfer = \Stripe\Transfer::create(array(
				"amount" => $StripeCharge,
				"currency" => "EUR",
				"destination" => $stripe_acc_id,
				"description" => "Payment Settlement for " . $email,
			));

			//create the settlement transactions
			(new TripController)->settlements($id);

			$response = array();
			$response['success'] = trans('admin.payment_msgs.amount_send');
		} catch (Exception $e) {
			$response['error'] = $e->getMessage();
		}

		return $response;
	}

	public function response(Request $request)
	{
		$log = PaymentLog::where('transaction_code', $request->order)->first();

		if ($log->is_wallet == 1) {

			if ($log->user_type == "user") {
				$user = \App\User::find($log->user_id);
				$wallet = (new TripController)->userCreditDebit($log->amount, $user->id, 1);
				(new SendPushNotification)->WalletMoney($user->id, currency($log->amount));
			} else if ($log->user_type == "provider") {
				$user = \App\Provider::find($log->user_id);
				$wallet = (new TripController)->providerCreditDebit($log->amount, $user->id, 1);
				(new SendPushNotification)->ProviderWalletMoney($user->id, currency($log->amount));
			}

			$wallet_balance = $user->wallet_balance + $log->amount;

			if ($request->ajax()) {
				return response()->json(['success' => currency($log->amount) . " " . trans('api.added_to_your_wallet'), 'message' => currency($log->amount) . " " . trans('api.added_to_your_wallet'), 'wallet_balance' => $wallet_balance]);
			} else {
				if ($log->user_type == "provider") {
					return redirect('/provider/wallet_transation')->with('flash_success', currency($log->amount) . trans('admin.payment_msgs.amount_added'));
				} else {
					return redirect('wallet')->with('flash_success', currency($log->amount) . trans('admin.payment_msgs.amount_added'));
				}
			}
		}

		$payment_id = $request->has('pay') ? $request->pay : null;

		switch ($log->payment_mode) {

			case 'BRAINTREE':
				# code...
				break;
			case 'CARD':
				# code...
				break;
			case 'PAYUMONEY':
				# code...
				break;


			case 'PAYPAL-ADAPTIVE':

				break;


			case 'PAYPAL':

				$paypal_conf = \Config::get('paypal');
				$api_context = new ApiContext(
					new OAuthTokenCredential(
						$paypal_conf['client_id'],
						$paypal_conf['secret']
					)
				);
				$api_context->setConfig($paypal_conf['settings']);

				$payment = Payment::get($request->paymentId, $api_context);

				$execution = new PaymentExecution();
				$execution->setPayerId($request->PayerID);

				//Execute the payment
				$result = $payment->execute($execution, $api_context);
				$log->response = $result;
				$log->save();

				if ($result->getState() == 'approved') {
					$payment_id = $request->PayerID;
				}

				break;


			case 'PAYTM':
				# code...
				break;
		}

		$UserRequest = UserRequests::find($log->transaction_id);

		$RequestPayment = UserRequestPayment::where('request_id', $UserRequest->id)->first();
		$RequestPayment->payment_id = $payment_id;
		$RequestPayment->payment_mode = $UserRequest->payment_mode;
		$RequestPayment->card = $RequestPayment->payable;
		$RequestPayment->save();
		$User = User::where('id', $RequestPayment->user_id)->first();
		if($RequestPayment->payment_mode == 'WALLET' && $User->wallet_balance < ($RequestPayment->total + $RequestPayment->tips)) {
			$UserRequest->paid = 0;
		}
		else{
			$UserRequest->paid = 1;
		}
		
		$UserRequest->status = 'COMPLETED';
		$UserRequest->save();
		//for create the transaction
		(new TripController)->callTransaction($UserRequest->id);
		if($UserRequest->payment_mode == 'WALLET'){
			(new TripController)->userCreditDebit($log->amount, $UserRequest, 0);
		}
		if ($request->ajax()) {
			return response()->json(['message' => trans('api.paid')]);
		} else {
			return redirect('dashboard')->with('flash_success', trans('api.paid'));
		}
	}

	public function failure(Request $request)
	{
		$log = PaymentLog::where('transaction_code', $request->order)->first();

		if ($log->is_wallet == 1) {

			if ($request->ajax()) {
				return response()->json(['success' => 'false', 'message' => 'Transaction Failed']);
			} else {
				if ($request->type == "provider") {
					return redirect('/provider/wallet_transation')->with('flash_error', 'Transaction Failed');
				} else {
					return redirect('wallet')->with('flash_error', 'Transaction Failed');
				}
			}
		}

		if ($request->ajax()) {
			return response()->json(['message' => 'Transaction Failed']);
		} else {
			if ($request->type == "provider") {
				return redirect('/')->with('flash_success', 'Transaction Failed');
			} else {
				return redirect('dashboard')->with('flash_success', 'Transaction Failed');
			}
		}
	}

	public function paytm_response(Request $request)
	{

		$log = PaymentLog::where('transaction_code', $request->ORDERID)->first();
		$log->response = $request->all();
		$log->save();

		$provider_url = $log->user_type == 'provider' ? '/provider' : '';

		if ($request->STATUS == "TXN_SUCCESS") {
			return redirect($provider_url . '/payment/response?order=' . $request->ORDERID . '&pay=' . $request->TXNID);
		} else {
			return redirect($provider_url . '/payment/failure?order=' . $request->ORDERID);
		}
	}

	public function payu_response(Request $request)
	{
		$log = PaymentLog::where('transaction_code', $request['txnid'])->first();
		$log->response = json_encode($request->all());
		$log->save();

		$provider_url = $log->user_type == 'provider' ? '/provider' : '';

		return redirect($provider_url . '/payment/response?order=' . $request['txnid'] . '&pay=' . $request->payuMoneyId);
	}

	public function payu_error(Request $request)
	{

		$log = PaymentLog::where('transaction_code', $request)->first();
		$log->response = json_encode($request);
		$log->save();

		$provider_url = $log->user_type == 'provider' ? '/provider' : '';

		return redirect($provider_url . '/payment/failure?order=' . $request['txnid']);
	}
}
