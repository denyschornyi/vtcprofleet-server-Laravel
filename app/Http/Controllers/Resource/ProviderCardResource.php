<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\ProviderCard;
use App\Provider;
use Exception;
use Auth;
use Illuminate\Http\Response;
use Setting;
use Session;
use Stripe\Customer;
use Stripe\Stripe;
use App\FleetPaymentSettings;

class ProviderCardResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        try {

            $cards = ProviderCard::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->get();
            return $cards;
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'stripe_token' => 'required'
        ]);

        try {


            $customer_id = $this->customer_id();

            $this->set_stripe();
            // external_account code
            // $customer = \Stripe\Account::retrieve($customer_id);
            // $card = $customer->external_accounts->create(
            //     array("external_account" => $request->stripe_token)
            // );
            // $customer = \Stripe\Account::retrieve(Auth::user()->stripe_acc_id);
            // external_account code

            $customer = Customer::retrieve($customer_id);
            $card = $customer->sources->create(["source" => $request->stripe_token]);

            $exist = ProviderCard::where('user_id', Auth::user()->id)
                ->where('last_four', $card['last4'])
                ->where('brand', $card['brand'])
                ->count();

            if ($exist == 0) {

            // external_account code
                // //delete previous card
                // $Providercard = ProviderCard::where('user_id', Auth::user()->id)->first();

                // if (!empty($Providercard)) {
                //     $card_detail = $customer->external_accounts->retrieve($Providercard->card_id);

                //     if (count($card_detail) > 1) {
                //         $card_detail->delete();
                //     }

                //     ProviderCard::where('card_id', $Providercard->card_id)->delete();
                // }
            // external_account code

                //add new card
                $create_card = new ProviderCard;
                $create_card->user_id = Auth::user()->id;
                $create_card->card_id = $card['id'];
                $create_card->last_four = $card['last4'];
                $create_card->brand = $card['brand'];
                $create_card->is_default = '1';
                $create_card->save();

                if (Setting::get('demo_mode', 0) == 1) {
                    Provider::where('id', Auth::user()->id)->where('status', 'card')->update(['status' => 'approved']);
                } else {
                    Provider::where('id', Auth::user()->id)->where('status', 'card')->update(['status' => 'onboarding']);
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['message' => trans('api.card_already')]);
                } else {
                    return back()->with('flash_success', trans('api.card_already'));
                }
            }

            if ($request->ajax()) {
                return response()->json(['message' => trans('api.card_added')]);
            } else {
                return back()->with('flash_success', trans('api.card_added'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 520);
            } else {
                return back()->with('flash_error', $e->getMessage());
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int    $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'card_id' => 'required|exists:provider_cards,card_id,user_id,' . Auth::user()->id,
        ]);
        try {
            ProviderCard::where('user_id', Auth::user()->id)->update(['is_default' => '0']);
            ProviderCard::where('card_id', $request->card_id)->update(['is_default' => '1']);
            return 'success';
        } catch (Exception $e) {
            return 'failure';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'card_id' => 'required|exists:provider_cards,card_id,user_id,' . Auth::user()->id,
        ]);

        try {

            $this->set_stripe();

            // $customer = \Stripe\Account::retrieve(Auth::user()->stripe_cust_id);

            // $card_detail = $customer->external_accounts->retrieve($request->card_id);
            // if (count($card_detail) > 1) {
            //     $card_detail->delete();
            // }

            $customer = Customer::retrieve(Auth::user()->stripe_cust_id);
            $customer->sources->retrieve($request->card_id)->delete();

            ProviderCard::where('card_id', $request->card_id)->delete();

            if ($request->ajax()) {
                return response()->json(['message' => trans('api.card_deleted')]);
            } else {
                return back()->with('flash_success', trans('api.card_deleted'));
            }
        } catch (Stripe_CardError $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            } else {
                return back()->with('flash_error', $e->getMessage());
            }
        }
    }

    public function delete_card(Request $request) {
        $this->validate($request, [
            'card_id' => 'required|exists:provider_cards,card_id,user_id,' . Auth::user()->id,
        ]);

        try {

            $this->set_stripe();

            // $customer = \Stripe\Account::retrieve(Auth::user()->stripe_cust_id);

            // $card_detail = $customer->external_accounts->retrieve($request->card_id);
            // if (count($card_detail) > 1) {
            //     $card_detail->delete();
            // }

            $customer = Customer::retrieve(Auth::user()->stripe_cust_id);
            $customer->sources->retrieve($request->card_id)->delete();

            ProviderCard::where('card_id', $request->card_id)->delete();

            if ($request->ajax()) {
                return response()->json(['message' => trans('api.card_deleted')]);
            } else {
                return back()->with('flash_success', trans('api.card_deleted'));
            }
        } catch (Stripe_CardError $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            } else {
                return back()->with('flash_error', $e->getMessage());
            }
        }
    }

    /**
     * setting stripe.
     *
     * @return Response
     */
    public function set_stripe()
    {
        if (Auth::user()->fleet > 0) {
			$FleetPaymentSettings = FleetPaymentSettings::where('fleet_id', Auth::user()->fleet)->first();
            $stripe_secret_key = $FleetPaymentSettings == null ? '' : $FleetPaymentSettings->stripe_secret_key;
		} else {
			$stripe_secret_key = config('constants.stripe_secret_key', '');
		}
		return \Stripe\Stripe::setApiKey($stripe_secret_key);
    }

    /**
     * Get a stripe customer id.
     *
     * @return Response
     */
    // public function customer_id()
    // {
    //     if (Auth::user()->stripe_cust_id != null) {

    //         return Auth::user()->stripe_cust_id;
    //     } else {

    //         try {

    //             $stripe = $this->set_stripe();

    //             // $customer = \Stripe\Customer::create([
    //             //     'email' => Auth::guard('provider')->user()->email,
    //             // ]);

    //             $customer = \Stripe\Account::create(array(
    //                 "country" => "US",
    //                 "type" => "custom",
    //                 "email" => Auth::user()->email
    //             ));

    //             $customer_update = \Stripe\Account::retrieve($customer['id']);
    //             $customer_update->tos_acceptance->date = time();
    //             $customer_update->tos_acceptance->ip = $_SERVER['REMOTE_ADDR'];
    //             $customer_update->legal_entity->business_name =  Auth::user()->first_name . ' ' . Auth::user()->last_name;
    //             $customer_update->legal_entity->dob->day = '27';
    //             $customer_update->legal_entity->dob->month = '05';
    //             $customer_update->legal_entity->dob->year = '1990';
    //             $customer_update->legal_entity->first_name = Auth::user()->first_name;
    //             $customer_update->legal_entity->last_name = Auth::user()->last_name;
    //             $customer_update->legal_entity->type = 'individual';
    //             $customer_update->save();

    //             Provider::where('id', Auth::user()->id)->update(['stripe_acc_id' => $customer['id']]);

    //             /*if(Setting::get('demo_mode', 0) == 1) {
    //                 Provider::where('id',Auth::user()->id)->where('status','card')->update(['status'=>'approved']);
    //             }
    //             else{
    //                 Provider::where('id',Auth::user()->id)->where('status','card')->update(['status'=>'onboarding']);
    //             }*/

    //             return $customer['id'];
    //         } catch (Exception $e) {

    //             return $e;
    //         }
    //     }
    // }

    /**
     * Get a stripe customer id.
     *
     * @return Response
     */
    public function customer_id()
    {
        if (Auth::user()->stripe_cust_id != null) {

            return Auth::user()->stripe_cust_id;
        } else {

            try {

                $stripe = $this->set_stripe();

                $customer = Customer::create([
                    'email' => Auth::user()->email,
                ]);

                Provider::where('id', Auth::user()->id)->update(['stripe_cust_id' => $customer['id']]);
                return $customer['id'];
            } catch (Exception $e) {
                return $e;
            }
        }
    }

    public function set_default(Request $request)
    {
        try {
            ProviderCard::where('user_id', Auth::user()->id)->update(['is_default' => '0']);
            ProviderCard::where('id', $request->id)->update(['is_default' => '1']);
            return 'success';
        } catch (Exception $e) {
            return 'failure';
        }
    }
}
