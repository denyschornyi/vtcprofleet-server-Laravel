<?php


namespace App\Exports;
use App\User;
use App\UserRequests;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;


class TripExport implements FromView
{
	public function view(): View {
		// TODO: Implement view() method.

		if(  Auth::user()->user_type === "COMPANY")
		{
			$passenger_id = User::where( 'company_id', Auth::user()->id )->select( 'id' )->get();
			$trips = UserRequests::CompanyTrips($passenger_id)->with('user');
		}
		else if(Auth::user()->user_type === "FLEET_COMPANY"){
			$passenger_id = User::where( 'fleet_company_id', Auth::user()->id )->select( 'id' )->get();
			$passenger_id[] = Auth::user()->id;
			$trips = UserRequests::CompanyTrips($passenger_id)->with('user');
		}
		else{
			$trips = UserRequests::UserTrips(Auth::user()->id)->with('user');
		}
		$trips = $trips->get();
		return view('user.ride.export',[
			'trip'=>$trips
		]);
	}
}
