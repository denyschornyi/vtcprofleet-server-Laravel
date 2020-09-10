<?php

namespace App\Http\Controllers\Resource;

use App\Fleet;
use App\FleetPeakHour;
use App\Reason;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class FleetPeakHourResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store' ,'update', 'destroy']]);
      /*  $this->middleware('permission:peak-hour-list', ['only' => ['index']]);
        $this->middleware('permission:peak-hour-create', ['only' => ['create','store']]);
        $this->middleware('permission:peak-hour-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:peak-hour-delete', ['only' => ['destroy']]);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $peakhour = FleetPeakHour::where('fleet_id',Auth::guard('fleet')->id())->orderBy('created_at' , 'desc')->get();
        return view('fleet.peakhour.index', compact('peakhour'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('fleet.peakhour.create');
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
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try{
            //PeakHour::create($request->all());
            $PeakHour = new FleetPeakHour();
            $PeakHour->start_time = date('H:i:s', strtotime($request->start_time));
            $PeakHour->end_time = date('H:i:s', strtotime($request->end_time));
            $PeakHour->fleet_id = Auth::guard('fleet')->id();
            $PeakHour->save();

            return back()->with('flash_success', trans('admin.peakhour_msgs.peakhour_saved'));

        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.peakhour_msgs.peakhour_not_found'));
        }
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
            return FleetPeakHour::findOrFail($id);
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
            $peakhour = FleetPeakHour::findOrFail($id);
            return view('fleet.peakhour.edit',compact('peakhour'));
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
        $this->validate($request, [
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try {

            $PeakHour = FleetPeakHour::findOrFail($id);

            $PeakHour->start_time = date('H:i:s', strtotime($request->start_time));
            $PeakHour->end_time = date('H:i:s', strtotime($request->end_time));
            $PeakHour->save();

            return redirect()->route('fleet.peakhour.index')->with('flash_success', trans('admin.peakhour_msgs.peakhour_update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.peakhour_msgs.peakhour_not_found'));
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
	        FleetPeakHour::find($id)->delete();
            return back()->with('flash_success', trans('admin.peakhour_msgs.peakhour_delete'));
        }
        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.peakhour_msgs.peakhour_not_found'));
        }
    }
}
