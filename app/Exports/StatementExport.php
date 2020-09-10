<?php


namespace App\Exports;
use App\User;
use App\Provider;
use App\UserRequestPayment;
use App\UserRequests;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Facades\Session;

class StatementExport implements FromView,WithCustomCsvSettings
{
	
	public $id;
	public $st;

	function __construct($x, $y) {
        $this->id  = $x;
        $this->st = $y;
  	}

	private $headers = [
		'Content-Type' => 'text/csv',
	];

	public function getCsvSettings(): array
	{
		return [
			'delimiter' => ';'
		];
	}

	public function view(): View {
		// TODO: Implement view() method.
		// var_dump($this->id);
		
		$keyId = $this->id;
		$admin_provider_ids = Provider::where('fleet', 0)->pluck('id')->toArray();
		$admin_user_ids = User::where('fleet_id', 0)->pluck('id')->toArray();
		if($this->st == 'admin') {
			$user_ids = User::where('fleet_id', 0)->pluck('id')->toArray();
			$provider_ids = Provider::where('fleet', 0)->pluck('id')->toArray();
			$rides = UserRequests::with( 'payment' )->orderBy( 'id', 'desc' );
		}
		else if($this->st == 'fleet') {
			$user_ids = User::where('fleet_id', $keyId)->pluck('id')->toArray();
			$provider_ids = Provider::where('fleet', $keyId)->pluck('id')->toArray();
			$ride_ids1 = UserRequests::whereIn('user_id', $user_ids)->pluck('id')->toArray();
			$ride_ids2 = UserRequests::whereIn('provider_id', $provider_ids)->pluck('id')->toArray();
			$ride_ids = array_merge($ride_ids1, $ride_ids2);
			
			$rides = UserRequests::whereIn('id', $ride_ids)->with('payment')->orderBy('id', 'desc');
			
		}
		else if($this->st == 'provider') {
			$rides = UserRequests::where('provider_id', $keyId)->with('payment')->orderBy('id', 'desc');
		}
		else if($this->st == 'user') {
			$rides = UserRequests::where( 'user_id', $keyId )->with( 'payment' )->orderBy( 'id', 'desc' );
		}
		else{
			//exception
		}
		
		// $rides = \Illuminate\Support\Facades\DB::table( 'user_requests' )
		// ->leftjoin( 'user_request_payments',
		// 	'user_requests.id',
		// 	'=',
		// 	'user_request_payments.request_id' )
		// ->select( 'user_requests.id',
		// 	'user_requests.booking_id',
		// 	'user_requests.created_at',
		// 	'user_request_payments.commision',
		// 	'user_request_payments.fleet',
		// 	'user_request_payments.discount',
		// 	'user_request_payments.peak_amount',
		// 	'user_request_payments.peak_comm_amount',
		// 	'user_request_payments.waiting_amount',
		// 	'user_request_payments.waiting_comm_amount',
		// 	'user_request_payments.tax',
		// 	'user_request_payments.tips',
		// 	'user_request_payments.round_of',
		// 	'user_request_payments.total',
		// 	'user_request_payments.wallet',
		// 	'user_request_payments.payable',
		// 	'user_requests.payment_mode',
		// 	'user_request_payments.payable',
		// 	'user_request_payments.cash',
		// 	'user_request_payments.card',
		// 	'user_request_payments.provider_pay',
		// 	'user_requests.status'
		// )
		// ->where('user_requests.fleet_id', 0)
		// ->orderBy( 'user_requests.id', 'desc' );

		// $revenue      = \Illuminate\Support\Facades\DB::table( 'user_requests' )
		// 	->leftjoin( 'user_request_payments',
		// 		'user_requests.id',
		// 		'=',
		// 		'user_request_payments.request_id' )
		// 	->select( DB::raw(
		// 	'SUM(commision + peak_comm_amount + waiting_comm_amount) as commision, 
		// 	SUM(fleet) as fleet,
		// 	SUM(discount) as discount,
		// 	SUM(peak_amount) as peak_amount,
		// 	SUM(peak_comm_amount) as peak_comm_amount,
		// 	SUM(waiting_amount) as waiting_amount,
		// 	SUM(waiting_comm_amount) as waiting_comm_amount,
		// 	SUM(tax) as tax,
		// 	SUM(tips) as tips,
		// 	SUM(round_of) as round_of,
		// 	SUM(total + tips) as total,
		// 	SUM(wallet) as wallet,
		// 	SUM(cash) as cash,
		// 	SUM(card) as card,
		// 	SUM(payable) as payable,
		// 	SUM(provider_pay) as provider_pay
		// 	'
		// ) );

		if( Session::has( 'from_date' ) && Session::has( 'to_date' ))
		{
			$from_date = Session::get( 'from_date' );
			$to_date   = Session::get( 'to_date' );
			$rides->whereBetween( 'user_requests.created_at',
				[
					Carbon::createFromFormat( 'Y-m-d', $from_date ),
					Carbon::createFromFormat( 'Y-m-d', $to_date ),
				] );

			// $revenue->whereBetween( 'user_requests.created_at',
			// 	[
			// 		Carbon::createFromFormat( 'Y-m-d', $from_date ),
			// 		Carbon::createFromFormat( 'Y-m-d', $to_date ),
			// 	] );
		}
		if(Session::has( 'searchval' )){
			
			$search_val = Session::get( 'searchval' );
			// var_dump($search_val);
			// exit;
			$rides->where('user_requests.status','like','%'.$search_val.'%');

			// $request_id =  DB::table('user_requests')->where('status','like','%'.$search_val.'%')->pluck('id');
			// $revenue->whereIn('user_request_payments.request_id',$request_id);
		}

		if(Session::has( 'searchval' ) && Session::has( 'from_date' ) && Session::has( 'to_date' ))
		{
			$search_val = Session::get( 'searchval' );
			$rides->where('user_requests.status','like','%'.$search_val.'%');

			// $request_id =  DB::table('user_requests')->where('status','like','%'.$search_val.'%')
			// 	->whereBetween( 'created_at',
			// 		[
			// 			Carbon::createFromFormat( 'Y-m-d', $from_date ),
			// 			Carbon::createFromFormat( 'Y-m-d', $to_date ),
			// 		] )
			// 	->pluck('id');
			// $revenue->whereIn('request_id',$request_id);
		}

		$rides = $rides->get();

		$revenue['commission'] = 0;
		$revenue['pool_commission'] = 0;
		$revenue['admin_commission'] = 0;
		$revenue['discount'] = 0;
		$revenue['peak_amount'] = 0;
		$revenue['peak_comm_amount'] = 0;
		$revenue['waiting_amount'] = 0;
		$revenue['waiting_comm_amount'] = 0;
		$revenue['tax'] = 0;
		$revenue['tips'] = 0;
		$revenue['round_of'] = 0;
		$revenue['total'] = 0;
		$revenue['wallet'] = 0;
		$revenue['cash'] = 0;
		$revenue['card'] = 0;
		$revenue['payable'] = 0;
		$revenue['provider_pay'] = 0;

		if($this->st == 'admin'){
			foreach($rides as $ride) {
				if(in_array($ride->user_id, $user_ids)){
					if(in_array($ride->provider_id, $provider_ids)){
						$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
						$revenue['commission'] += $commission_unit;
					}
					else{
						// $revenue['admin_commission'] += $ride->payment->admin_commission;
						$revenue['pool_commission'] += $ride->payment->pool_commission;
					}
				}
				else{
					if(in_array($ride->provider_id, $provider_ids)){
						$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
						$revenue['commission'] += $commission_unit;
						// $revenue['pool_commission'] += $ride->payment->pool_commission;
						$revenue['admin_commission'] += $ride->payment->admin_commission;
					}
					else{
							$revenue['admin_commission'] += $ride->payment->admin_commission;
					}
				}
			
				$revenue['discount'] += $ride->payment->discount;
				$revenue['peak_amount'] += $ride->payment->peak_amount;
				$revenue['peak_comm_amount'] += $ride->payment->peak_comm_amount;
				$revenue['waiting_amount'] += $ride->payment->waiting_amount;
				$revenue['waiting_comm_amount'] += $ride->payment->waiting_comm_amount;
				$revenue['tax'] += $ride->payment->tax;
				$revenue['tips'] += $ride->payment->tips;
				$revenue['round_of'] += $ride->payment->round_of;
				$revenue['total'] += $ride->payment->tax + $ride->payment->tips;
				$revenue['wallet'] += $ride->payment->wallet;
				$revenue['cash'] += $ride->payment->cash;
				$revenue['card'] += $ride->payment->card;
				$revenue['payable'] += $ride->payment->payable;
				$revenue['provider_pay'] += $ride->payment->provider_pay;
			}
		}

		if($this->st == 'fleet'){
			foreach($rides as $ride){
				if(in_array($ride->user_id, $user_ids) && in_array($ride->provider_id, $admin_provider_ids)){
					$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
					$revenue['commission'] += $commission_unit;	
				}
				$revenue['admin_commission'] += $ride->payment->admin_commission;

				$revenue['discount'] += $ride->payment->discount;
				$revenue['peak_amount'] += $ride->payment->peak_amount;
				$revenue['peak_comm_amount'] += $ride->payment->peak_comm_amount;
				$revenue['waiting_amount'] += $ride->payment->waiting_amount;
				$revenue['waiting_comm_amount'] += $ride->payment->waiting_comm_amount;
				$revenue['tax'] += $ride->payment->tax;
				$revenue['tips'] += $ride->payment->tips;
				$revenue['round_of'] += $ride->payment->round_of;
				$revenue['total'] += $ride->payment->tax + $ride->payment->tips;
				$revenue['wallet'] += $ride->payment->wallet;
				$revenue['cash'] += $ride->payment->cash;
				$revenue['card'] += $ride->payment->card;
				$revenue['payable'] += $ride->payment->payable;
				$revenue['provider_pay'] += $ride->payment->provider_pay;
				
			}
			
		}

		if($this->st == 'provider'){
			foreach($rides as $ride) {
				if(in_array($ride->user_id, $admin_user_ids)){
					$revenue['admin_commission'] += $ride->payment->admin_commission;
				}
				else{
					$revenue['pool_commission'] += $ride->payment->pool_commission;
				}
				$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
				$revenue['commission'] += $commission_unit;

				$revenue['discount'] += $ride->payment->discount;
				$revenue['peak_amount'] += $ride->payment->peak_amount;
				$revenue['peak_comm_amount'] += $ride->payment->peak_comm_amount;
				$revenue['waiting_amount'] += $ride->payment->waiting_amount;
				$revenue['waiting_comm_amount'] += $ride->payment->waiting_comm_amount;
				$revenue['tax'] += $ride->payment->tax;
				$revenue['tips'] += $ride->payment->tips;
				$revenue['round_of'] += $ride->payment->round_of;
				$revenue['total'] += $ride->payment->tax + $ride->payment->tips;
				$revenue['wallet'] += $ride->payment->wallet;
				$revenue['cash'] += $ride->payment->cash;
				$revenue['card'] += $ride->payment->card;
				$revenue['payable'] += $ride->payment->payable;
				$revenue['provider_pay'] += $ride->payment->provider_pay;
			}
		}

		if($this->st == 'user'){
			foreach($rides as $ride){
				$revenue['overall'] += $ride->payment->total;
			}
		}
		// $revenue = $revenue->get();
		if($this->st == 'user') {
			return view('admin.providers.pdfs.export_admin1',[
				'ride'=>$rides, 'revenue' => $revenue
			]);
		}
		else{
			return view('admin.providers.pdfs.export_admin',[
				'ride'=>$rides, 'revenue' => $revenue, 'user_ids' => $user_ids, 'provider_ids' => $provider_ids, 'statement_for' => $this->st, 'admin_user_ids' => $admin_user_ids, 'admin_provider_ids' => $admin_provider_ids
			]);
		}
	}
}
