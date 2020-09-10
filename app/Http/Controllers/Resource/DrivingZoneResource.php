<?php

namespace App\Http\Controllers\Resource;

use App\Admin;
use App\Dispute;
use App\DrivingZone;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProviderResources\TripController;
use App\Notifications\WebPush;
use App\Reason;
use App\UserRequestDispute;
use App\UserRequests;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DrivingZoneResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store' ,'update', 'destroy']]);
        $this->middleware('permission:dispute-list', ['only' => ['index']]);
        $this->middleware('permission:dispute-create', ['only' => ['create','store']]);
        $this->middleware('permission:dispute-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:dispute-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
    	$data = DrivingZone::all();
        return view('admin.drivingzone.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    	$country_list = DB::table('country')->get();
        return view('admin.drivingzone.create',compact('country_list'));
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
    	if($request->has('start_country'))
    	{
		    $ref = new DrivingZone();
		    $drivingZone_countryID = DrivingZone::where('status','country')->value(id);
		    if(count($drivingZone_countryID) != '0')
		    {
				$update_ref = DrivingZone::findorFail($drivingZone_countryID);
				$update_ref -> country_list =  implode(",",$request->input('start_country'));
			    if ($request->input('start_switch') == 'on')
			    {
				    $update_ref->active = '1';
			    }else{
				    $update_ref->active = '0';
			    }
				$update_ref -> update();
		    }
		    else
	        {
			    $ref->country_list = implode(",",$request->input('start_country'));
		        if ($request->input('start_switch') == 'on')
		        {
			        $ref->active = '1';
		        }else{
			        $ref->active = '0';
		        }
			    $ref->status = 'country';
		        $ref->save();
		    }
	    }
    	if($request->input('restict_start') != "")
    	{
		    $ref1 = new DrivingZone();
		    $ref1->location_original   = $request->input( 'restict_start' );
		    $ref1->country   = $request->input( 'start_countryname' );
		    $ref1->location  = $request->input( 'start_region' );
		    $ref1->latitude  = $request->input( 'start_lat' );
		    $ref1->longitude = $request->input( 'start_lng' );
		    $ref1->radius    = $request->input( 'start_radius' );
		    $ref1->status    = 'location';

		    if ($request->input('start_switch') == 'on')
		    {
			    $ref1->active = '1';
		    }else{
			    $ref1->active = '0';
		    }
		    $ref1->save();
	    }
	    return redirect()->route('admin.drivingzone.index')->with('flash_success', trans('admin.drivingzone_msgs.saved'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Reason  $id
     * @return Response
     */
    public function show($id)
    {
        try {
            return Dispute::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Reason  $id
     * @return Response
     */
    public function edit($id)
    {
        try {
	        $drivingzone = DrivingZone::findOrFail($id);
	        $country_list = DB::table('country')->get();
            return view('admin.drivingzone.edit',compact('drivingzone','country_list'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  Reason $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {

            $ref = DrivingZone::findOrFail($id);
	        if ($request->input('start_switch') == 'on')
	        {
		        $ref->active = '1';
	        }else{
		        $ref->active = '0';
	        }
			if($ref->status == "country")
			{
				$update_ref -> country_list =  implode(",",$request->input('start_country'));
				$ref -> update();
			}else{
				$ref->location_original = $request->input( 'restict_start' );
				$ref->country           = $request->input( 'start_countryname' );
				$ref->location          = $request->input( 'start_region' );
				$ref->latitude          = $request->input( 'start_lat' );
				$ref->longitude         = $request->input( 'start_lng' );
				$ref->radius            = $request->input( 'start_radius' );
				$ref->update();
			}
            return redirect()->route('admin.drivingzone.index')->with('flash_success', trans('admin.drivingzone_msgs.update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.drivingzone_msgs.not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Reason  $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            DrivingZone::find($id)->delete();
            return back()->with('flash_success', trans('admin.drivingzone_msgs.delete'));
        }
        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.drivingzone_msgs.not_found'));
        }
    }

    public function dispute_list(Request $request)
    {
        $this->validate($request, [
            'dispute_type' => 'required'
        ]);

        $dispute = Dispute::select('dispute_name')->where('dispute_type' , $request->dispute_type)->where('status' , 'active')->get();

        return $dispute;
    }

    public function userdisputes()
    {

        $disputes = UserRequestDispute::with('request')->with('user')->with('provider')->orderBy('created_at' , 'desc')->get();

        return view('admin.userdispute.index', compact('disputes'));
    }

    public function userdisputecreate()
    {
        return view('admin.userdispute.create');
    }

    public function userdisputeedit($id)
    {

        try {
            $dispute = UserRequestDispute::with('request')->with('user')->with('provider')->findOrFail($id);
            return view('admin.userdispute.edit',compact('dispute'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    public function create_dispute(Request $request)
    {

        $this->validate($request, [
            'request_id' => 'required',
            'dispute_type' => 'required',
            'dispute_name' => 'required',
        ]);

        try{
            $Dispute = new UserRequestDispute();
            $Dispute->request_id = $request->request_id;
            $Dispute->dispute_type = $request->dispute_type;
            $Dispute->user_id = $request->user_id;
            $Dispute->provider_id = $request->provider_id;
            $Dispute->dispute_name = $request->dispute_name;
            if(!empty($request->dispute_other))
                $Dispute->dispute_name = $request->dispute_other;
            $Dispute->comments = $request->comments;
            $Dispute->save();

            UserRequests::where('id', $request->request_id)->update(['is_dispute' => 1]);

            $admin = Admin::find( Auth::user()->id);

            if($admin == null) {
                $admin = Admin::whereNotNull('name')->first();
            }

            if($admin != null) {
                $admin->notify(new WebPush("Notifications", trans('admin.dispute.new_dispute'), url('/')));
            }


            if($request->ajax()){
                return response()->json(['message' => trans('admin.dispute_msgs.saved')]);
            }else{
                return back()->with('flash_success', trans('admin.dispute_msgs.saved'));
            }
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.dispute_msgs.not_found'));
        }
    }

    public function update_dispute(Request $request, $id)
    {

        $this->validate($request, [
            'comments' => 'required',
            'status' => 'required',
        ]);

        try{

            $Dispute = UserRequestDispute::findOrFail($id);
            $Dispute->comments = $request->comments;
            $Dispute->refund_amount = $request->refund_amount;

            if(!empty($request->refund_amount)){
                //create the dispute transactions
                if($Dispute->dispute_type=='user'){
                    $type=1;
                    $request_by_id=$Dispute->user_id;
                }
                else{
                    $type=0;
                    $request_by_id=$Dispute->provider_id;
                }

                (new TripController)->disputeCreditDebit($request->refund_amount,$request_by_id,$type);
            }

            $Dispute->status = $request->status;
            $Dispute->save();

            if($request->ajax()){
                return response()->json(['message' => trans('admin.dispute_msgs.saved')]);
            }else{
                return back()->with('flash_success', trans('admin.dispute_msgs.saved'));
            }
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.dispute_msgs.not_found'));
        }
    }

    public function active(Request $request,$id)
    {
//    	$id = $request->input('id');
    	$update_ref = DrivingZone::findorFail($id);
    	$update_ref->active = 1;
    	$update_ref->update();
    	return redirect()->back()->with('flash_success', trans('admin.drivingzone_msgs.actived'));
    }

}
