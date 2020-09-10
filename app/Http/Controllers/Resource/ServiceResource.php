<?php

namespace App\Http\Controllers\Resource;

use App\Fleet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Setting;
use Exception;
use App\Helpers\Helper;

use App\ServiceType;
use App\ProviderService;
use App\PeakHour;
use App\ServicePeakHour;

class ServiceResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
//        $this->middleware('permission:service-types-list', ['only' => ['index']]);
//        $this->middleware('permission:service-types-create', ['only' => ['create','store']]);
//        $this->middleware('permission:service-types-edit', ['only' => ['edit','update']]);
//        $this->middleware('permission:service-types-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $services = ServiceType::where('fleet_id','0')->get();
        if($request->ajax()) {
            return $services;
        } else {
            return view('admin.service.index', compact('services'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $Peakhour =  PeakHour::get();
        return view('admin.service.create', compact('Peakhour'));
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
            'name' => 'required|unique:service_types|max:255',
            'capacity' => 'required|numeric',
            'fixed' => 'required|numeric',
            'price' => 'sometimes|nullable|numeric',
            'minute' => 'sometimes|nullable|numeric',
            'hour' => 'sometimes|nullable|numeric',
            'distance' => 'sometimes|nullable|numeric',
            'calculator' => 'required|in:MIN,HOUR,DISTANCE,DISTANCEMIN,DISTANCEHOUR',
            'image' => 'mimes:ico,png',
            'marker' => 'mimes:ico,png',
            'min_price' => 'required|numeric',
        ]);

        try {
            $service = new ServiceType;

            $service->name = $request->name;
            $service->fixed = $request->fixed;
            $service->description = $request->description;
            $service['min_price'] = $request->min_price;


            if($request->hasFile('image')) {
                $service->image = Helper::upload_picture($request->image);
            }
            if($request->hasFile('marker')) {
                $service->marker = Helper::upload_picture($request->marker);
            }

            if(!empty($request->price))
                $service->price = $request->price;
            else
                $service->price=0;

            if(!empty($request->minute))
                $service->minute = $request->minute;
            else
                $service->minute = 0;

            if(!empty($request->hour))
                $service->hour = $request->hour;
            else
                $service->hour = 0;

            if(!empty($request->distance))
                $service->distance = $request->distance;
            else
                $service->distance = 0;

	        $service['capacity'] = $request->capacity;

            if(!empty($request->luggage_capacity))
                $service->luggage_capacity = $request->luggage_capacity;

            $service->save();
			Helper::addServiceType();

            if($request->peak_price){

                foreach ($request->peak_price as $key => $value) {

                    if($request->peak_price[$key]>0){
                        $service_map = ServicePeakHour::create(['service_type_id'=>$service->id,'peak_hours_id'=>$key,'min_price'=>$request->peak_price[$key]]);
                    }

                }

            }

            return back()->with('flash_success', trans('admin.service_type_msgs.service_type_saved'));
        } catch (Exception $e) {
            dd("Exception", $e);
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
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
            return ServiceType::findOrFail($id);
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

            $service = ServiceType::findOrFail($id);

            $Peakhour=PeakHour::with(['servicetimes' => function ($query) use ($id) {
                        $query->where('service_type_id', $id);
                        }])->get();

        /*  echo "<pre>";
            print_r($Peakhour->toArray());exit;*/

            return view('admin.service.edit',compact('service','Peakhour'));
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
            'name' => 'required|max:255',
            'fixed' => 'required|numeric',
            'price' => 'sometimes|nullable|numeric',
            'minute' => 'sometimes|nullable|numeric',
            'hour' => 'sometimes|nullable|numeric',
            'distance' => 'sometimes|nullable|numeric',
            'image' => 'mimes:ico,png',
            'marker' => 'mimes:ico,png',
            'min_price' => 'required|numeric',
        ]);

        try {

            $imgservice=ServiceType::find($id);

            if($request->hasFile('image')) {
                if($imgservice->image) {
                    Helper::delete_picture($imgservice->image);
                }
                $service['image'] = Helper::upload_picture($request->image);
            }
            if($request->hasFile('marker')) {
                if($imgservice->marker) {
                    Helper::delete_picture($imgservice->marker);
                }
                $service['marker'] = Helper::upload_picture($request->marker);
            }
            $service['name'] = $request->name;
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

            $service['capacity'] = $request->capacity;

            if(!empty($request->waiting_free_mins))
                $service['waiting_free_mins'] = $request->waiting_free_mins;
            else
                $service['waiting_min_charge'] = 0;

            if(!empty($request->waiting_min_charge))
                $service['waiting_min_charge'] = $request->waiting_min_charge;
            else
                $service['waiting_min_charge'] = 0;

            if(!empty($request->luggage_capacity))
                $service['luggage_capacity'] = $request->luggage_capacity;

            ServiceType::where('id', $id)->update($service);

            //update peakhours
            if($request->peak_price){

                foreach ($request->peak_price as $key => $value) {

                    if($value['status']==1){
                        //update price
                         if($value['id']){
                            $service_map = ServicePeakHour::where('service_type_id',$id)->where('peak_hours_id',$key)->update(['min_price'=>$value['id'] ]);
                         }
                         else{
                            //delete peakhours
                            ServicePeakHour::where('service_type_id',$id)->where('peak_hours_id',$key)->delete();
                         }
                    }
                    else{
                        if($value['id']){
                            //insert price
                            $service_map = ServicePeakHour::create(['service_type_id'=>$id,'peak_hours_id'=>$key,'min_price'=>$value['id']]);
                        }
                    }
                }

            }

            return redirect()->route('admin.service.index')->with('flash_success', trans('admin.service_type_msgs.service_type_update'));
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
            $provider_service=ProviderService::where('service_type_id',$id)->count();
            if($provider_service>0){
                return back()->with('flash_error', trans('admin.service_type_msgs.service_type_using'));
            }

            ServiceType::find($id)->delete();
            ServicePeakHour::where('service_type_id',$id)->delete();

            return back()->with('flash_success', trans('admin.service_type_msgs.service_type_delete'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.service_type_msgs.service_type_not_found'));
        }
    }
}
