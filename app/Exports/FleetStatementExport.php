<?php


namespace App\Exports;
use App\User;
use App\UserRequestPayment;
use App\UserRequests;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class FleetStatementExport implements FromView,WithCustomCsvSettings
{
	public $id;
	public $st;
	public $search;

	function __construct($x, $y, $z) {
        $this->id  = $x;
		$this->st = $y;
		$this->search = $z;
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
		// var_dump($this->id, $this->st);
		// exit;
		$keyId = $this->id;
		$st = $this->st;
		$fleetId = Auth::guard( 'fleet' )->id();
		$fleet_user_ids = User::where('fleet_id', $fleetId)->pluck('id')->toArray();
		$fleet_provider_ids = Provider::where('fleet', $fleetId)->pluck('id')->toArray();

		if($st == 'fleet'){
			$user_request_ids1 = UserRequests::whereIn('user_id', $fleet_user_ids)->pluck('id')->toArray();
			$user_request_ids2 = UserRequests::whereIn('provider_id', $fleet_provider_ids)->pluck('id')->toArray();
			$user_request_ids = array_merge($user_request_ids1, $user_request_ids2);
			
			$rides        = UserRequests::whereIn( 'id', $user_request_ids )
				->with( 'payment' )->orderBy( 'id', 'desc' );
		}
		if($st == 'provider'){
			$rides = UserRequests::where('provider_id', $keyId)->with('payment')->orderBy('id', 'desc');
		}
		if($st == 'user'){
			$rides = UserRequests::where('user_id', $keyId)->with('payment')->orderBy('id', 'desc');
		}
		
		// $userIds      =
		// 	User::where( 'fleet_id', Auth::guard( 'fleet' )->id() )
		// 		->pluck( 'id' );

		// $rides = \Illuminate\Support\Facades\DB::table( 'user_requests' )
		// 	->leftjoin( 'user_request_payments',
		// 		'user_requests.id',
		// 		'=',
		// 		'user_request_payments.request_id' )
		// 	->select( 'user_requests.id',
		// 		'user_requests.booking_id',
		// 		'user_requests.created_at',
		// 		'user_request_payments.commision',
		// 		'user_request_payments.pool_commission',
		// 		'user_request_payments.admin_commission',
		// 		'user_request_payments.discount',
		// 		'user_request_payments.peak_amount',
		// 		'user_request_payments.peak_comm_amount',
		// 		'user_request_payments.waiting_amount',
		// 		'user_request_payments.waiting_comm_amount',
		// 		'user_request_payments.tax',
		// 		'user_request_payments.tips',
		// 		'user_request_payments.round_of',
		// 		'user_request_payments.total',
		// 		'user_request_payments.wallet',
		// 		'user_request_payments.payable',
		// 		'user_requests.payment_mode',
		// 		'user_request_payments.payable',
		// 		'user_request_payments.cash',
		// 		'user_request_payments.card',
		// 		'user_request_payments.provider_pay',
		// 		'user_requests.status'
		// 	)
		// 	->whereIn( 'user_requests.user_id', $userIds )
		// 	->orderBy( 'user_requests.id', 'desc' );

		// $revenue      = \Illuminate\Support\Facades\DB::table( 'user_requests' )
		// 	->leftjoin( 'user_request_payments',
		// 		'user_requests.id',
		// 		'=',
		// 		'user_request_payments.request_id' )
		// 	->select( DB::raw(
		// 		'SUM(commision) as commision, 
		// 	SUM(pool_commission) as pool_commission,
		// 	SUM(admin_commission) as admin_commission,
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
		// 	) )->whereIn( 'user_requests.user_id', $userIds );

		if( Session::has( 'from_date' ) && Session::has( 'to_date' )) {
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
			$rides->where('status','like','%'.$search_val.'%');

			// $request_id =  DB::table('user_requests')->where('status','like','%'.$search_val.'%')->pluck('id');
			// $revenue->whereIn('request_id',$request_id);

		}

		if(Session::has( 'searchval' ) && Session::has( 'from_date' ) && Session::has( 'to_date' )){
			$search_val = Session::get( 'searchval' );
			$rides->where('status','like','%'.$search_val.'%');

			// $request_id =  DB::table('user_requests')->where('status','like','%'.$search_val.'%')
			// 	->whereBetween( 'created_at',
			// 		[
			// 			Carbon::createFromFormat( 'Y-m-d',
			// 				$from_date ),
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
		
		if($st == 'fleet'){
			foreach($rides as $ride){
				if(in_array($ride->user_id, $fleet_user_ids)){
					if(in_array($ride->provider_id, $fleet_provider_ids)){
						$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
						$revenue['commission'] += $commission_unit;
						$revenue['admin_commission'] += $ride->payment->admin_commission;
					}
					else{
						$revenue['pool_commission'] += $ride->payment->pool_commission;
						$revenue['admin_commission'] += $ride->payment->admin_commission;
					}
				}
				else{
					$commission_unit = $ride->payment->commision + $ride->payment->peak_comm_amount + $ride->payment->waiting_comm_amount;
					$revenue['commission'] += $commission_unit;
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

		if($st == 'provider'){
			foreach($rides as $ride){
				if(in_array($ride->user_id, $fleet_user_ids)){
					$revenue['admin_commission'] += $ride->payment->admin_commission;
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

		if($st == 'user'){
			foreach($rides as $ride){
				$revenue['overall'] += $ride->payment->total;
			}
		}
		
		// $revenue = $revenue->get();
		return view('admin.providers.pdfs.export1',[
			'ride'=>$rides, 'revenue' => $revenue, 'userIds'=>$fleet_user_ids, 'providerIds'=>$fleet_provider_ids, 'statement_for' => $st
		]);
	}
}
