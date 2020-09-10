<?php

namespace App\Http\Controllers\Resource;

use App\Fleet;
use App\ServiceType;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Setting;
use App\WalletRequests;
use Storage;
use App\FleetPaymentSettings;

class FleetResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
        $this->middleware('permission:fleet-list', ['only' => ['index']]);
        $this->middleware('permission:fleet-create', ['only' => ['create','store']]);
        $this->middleware('permission:fleet-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:fleet-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $fleets = Fleet::orderBy('created_at' , 'desc')->get();
        return view('admin.fleet.index', compact('fleets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.fleet.create');
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
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'email' => 'required|unique:fleets,email|email|max:255',
            'country_code' => 'required|min:2',
            'mobile' => 'digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $fleet = $request->all();
            $fleet['password'] = bcrypt($request->password);
            if($request->hasFile('logo')) {
                $fleet['logo'] = $request->logo->store('fleet');
            }
	        $roles = 5; // role FLEET
            $fleet = Fleet::create($fleet);
	        $fleet->assignRole($roles);
            $fleet_id = $fleet->id;
			$serviceTypeName = ServiceType::where('fleet_id','0')->pluck('name');

            $obj = new FleetPaymentSettings();
            $obj->fleet_id = $fleet->id;
            $obj->cash_payment_status = 'yes';
            $obj->save();
//			Helper::addServiceType();

            Helper::welcomeEmailToNewUser('fleet', Fleet::find($fleet->id));

            return back()->with('flash_success', trans('admin.fleet_msgs.fleet_saved'));

        }

        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.fleet_msgs.fleet_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Fleet $fleet
     *
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Fleet $fleet
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $fleet = Fleet::findOrFail($id);
            return view('admin.fleet.edit',compact('fleet'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Fleet   $fleet
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|max:255',
            'country_code' => 'required|min:2',
            'company' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $fleet = Fleet::findOrFail($id);

            if($request->hasFile('logo')) {
                Storage::delete($fleet->logo);
                $fleet->logo = $request->logo->store('fleet');
            }

	        $roles = 5; // role FLEET

            $fleet->name = $request->name;
            $fleet->company = $request->company;
            $fleet->country_code = $request->country_code;
            $fleet->mobile = $request->mobile;
            $fleet->commission = $request->commission;
            $fleet->save();
	        $fleet->assignRole($roles);

            return redirect()->route('admin.fleet.index')->with('flash_success', trans('admin.fleet_msgs.fleet_update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.fleet_msgs.fleet_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Fleet  $Fleet
     *
     * @return Response
     */
    public function destroy($id)
    {

        try {
            $fleet_request=WalletRequests::where('request_from','fleet')->where('from_id',$id)->count();

            if($fleet_request>0){
                return back()->with('flash_error', trans('admin.fleet_msgs.fleet_settlement'));
            }

            Fleet::find($id)->delete();
            return back()->with('message', trans('admin.fleet_msgs.fleet_delete'));
        }
        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.fleet_msgs.fleet_not_found'));
        }
    }

}
