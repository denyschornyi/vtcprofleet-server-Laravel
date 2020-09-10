<?php

namespace App\Http\Controllers\Resource;
use anlutro\LaravelSettings\ArrayUtil;
use App\FleetPeakHour;
use App\FleetServicePeakHour;
use App\FleetServiceType;
use App\ServiceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Setting;
use Exception;
use App\Helpers\Helper;
use App\ProviderService;


class FleetServiceResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
    	//add service type
    	Helper::addServiceType();
	    $services = ServiceType::with(array('fleet_service_type'=>function($query){
		    $query->where('fleet_service_types.fleet_id',Auth::guard('fleet')->id());
	    }))->get();
	    // dd($services);
        if($request->ajax()) {
            return $services;
        } else {
            return view('fleet.service.index', compact('services'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

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
    }

    /**
     * Display the specified resource.
     *
     * @param ServiceType $serviceType
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            return FleetServiceType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ServiceType $serviceType
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
	        $service = FleetServiceType::where('fleet_id',Auth::guard('fleet')->id())
		        ->where('id', $id)->first();
            $service_type_id = $service->service_type_id;
            $Peakhour=FleetPeakHour::with(['servicetimes' => function ($query) use ($service_type_id) {
                        $query->where('service_type_id', $service_type_id);
                        $query->where('fleet_id', Auth::guard('fleet')->id());
                        }])
	            ->where('fleet_id',Auth::guard('fleet')->id())->get();
        //    echo json_encode($Peakhour); exit;
//			dd($service->fleet_service_type->id);
            return view('fleet.service.edit',compact('service','Peakhour'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request     $request
     * @param ServiceType $serviceType
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'fixed' => 'required|numeric',
            'price' => 'sometimes|nullable|numeric',
            'minute' => 'sometimes|nullable|numeric',
            'hour' => 'sometimes|nullable|numeric',
            'distance' => 'sometimes|nullable|numeric',
            'min_price' => 'required|numeric',
        ]);

        try
        {
	        $fleet_id = Auth::guard('fleet')->id();
            $service['fixed'] = $request->fixed;
            $service['min_price'] = $request->min_price;

            if(!empty($request->price))
                $service['price'] = $request->price;
            else
                $service['price']=0;

            if(!empty($request->minute))
                $service['minute'] = $request->minute;
            else
                $service['minute'] = 0;

            if(!empty($request->hour))
                $service['hour'] = $request->hour;
            else
                $service['hour'] = 0;

            if(!empty($request->distance))
                $service['distance'] = $request->distance;
            else
                $service['distance'] = 0;

            $service['calculator'] = $request->calculator;

	        // $service['capacity'] = $request->capacity;

            if(!empty($request->waiting_free_mins))
                $service['waiting_free_mins'] = $request->waiting_free_mins;
            else
                $service['waiting_min_charge'] = 0;

            if(!empty($request->waiting_min_charge))
                $service['waiting_min_charge'] = $request->waiting_min_charge;
            else
                $service['waiting_min_charge'] = 0;

	        // $service['capacity'] = $request->capacity;
            // if(!empty($request->luggage_capacity))
            //     $service['luggage_capacity'] = $request->luggage_capacity;

            FleetServiceType::where('id', $id)->update($service);

	        $service_type_id = FleetServiceType::where('id', $id)->value('service_type_id');
//	        dd($id);
            //update peakho
            if($request->peak_price){

                foreach ($request->peak_price as $key => $value) {

                    if($value['status']==1){
                        //update price
                         if($value['id']){
                            $service_map = FleetServicePeakHour::where('fleet_id',$fleet_id)->where('service_type_id',$service_type_id)->where('peak_hours_id',$key)->update(['min_price'=>$value['id'] ]);
                         }
                         else{
                            //delete peakhours
                            FleetServicePeakHour::where('fleet_id',$fleet_id)->where('service_type_id',$service_type_id)->where('peak_hours_id',$key)->delete();
                         }
                    }
                    else{
                        if($value['id']){
                            //insert price
                            $service_map = FleetServicePeakHour::create(['service_type_id'=>$service_type_id,'peak_hours_id'=>$key,'min_price'=>$value['id'],'fleet_id'=>$fleet_id]);
                        }
                    }
                }

            }

            return redirect()->route('fleet.service.index')->with('flash_success', trans('admin.service_type_msgs.service_type_update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ServiceType  $serviceType
     *
     * @return Response
     */
    public function destroy($id)
    {

        try {
//            $provider_service=ProviderService::where('service_type_id',$id)->count();
//            if($provider_service>0){
//                return back()->with('flash_error', trans('admin.service_type_msgs.service_type_using'));
//            }

	        ServiceType::find($id)->delete();
//            FleetServicePeakHour::where('service_type_id',$id)->delete();

            return back()->with('flash_success', trans('admin.service_type_msgs.service_type_delete'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }
}
