<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserRequests;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Advertisement;
use Illuminate\Http\Response;

class AdvertisementResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
        $this->perpage = config('constants.per_page', '10');
	    $this->middleware('permission:advertisement-list', ['only' => ['index']]);
	    $this->middleware('permission:advertisement-create', ['only' => ['create','store']]);
	    $this->middleware('permission:advertisement-edit', ['only' => ['edit','update']]);
	    $this->middleware('permission:advertisement-delete', ['only' => ['destroy']]);

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $advertisements = Advertisement::orderBy('created_at', 'desc')->get();
        return view('admin.advertisement.index', compact('advertisements'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
        return view('admin.advertisement.create');
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
        //
        $this->validate($request, [
            'type' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png|max:5242880',
            'click_url' => 'required',
            'status' => 'required',
        ]);

        try {

            $Advertisement = new Advertisement;
            $Advertisement->type = $request->type;

            if ($request->hasFile('image')) {
                $Advertisement->image = Helper::upload_picture2($request->image);
            }

            $Advertisement->click_url = $request->click_url;
            $Advertisement->status = $request->status;
            $Advertisement->save();

            return back()->with('flash_success', trans('admin.advertisement.message.saved'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.advertisement.message.not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    { }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
        $advertisement = Advertisement::where('id', $id)->first();
        return view('admin.advertisement.edit', compact('advertisement'));
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
        //
        $this->validate($request, [
            'type' => 'required',
            'image' => 'mimes:jpeg,jpg,png|max:5242880',
            'click_url' => 'required',
            'status' => 'required',
        ]);

        try {

            $Advertisement = Advertisement::findOrFail($id);

            $Advertisement->type = $request->type;

            if ($request->hasFile('image')) {
                if ($Advertisement->image) {
                    Helper::delete_picture($Advertisement->image);
                }
                $Advertisement->image = Helper::upload_picture2($request->image);
            }

            $Advertisement->click_url = $request->click_url;
            $Advertisement->status = $request->status;
            $Advertisement->save();

            return redirect()->route('admin.advertisement.index')->with('flash_success', trans('admin.advertisement.message.updated'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.advertisement.message.not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $Advertisement = Advertisement::findOrFail($id);
            $Advertisement->delete();
            return back()->with('flash_success', trans('admin.advertisement.message.deleted'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.something_wrong'));
        }
    }

    public function get_user_advertisement() {
        $advertisements = Advertisement::where('status', 'ACTIVE')->where(function($q){
            $q->where('type', 'ALL')->orWhere('type', 'USER');
        })->get();
        foreach ($advertisements as $adver) {
            $adver->image = url('/').$adver->image;
        }
        return response()->json($advertisements);
    }

    public function get_provider_advertisement() {
        $advertisements = Advertisement::where('status', 'ACTIVE')->where(function($q){
            $q->where('type', 'ALL')->orWhere('type', 'PROVIDER');
        })->get();
        foreach ($advertisements as $adver) {
            $adver->image = url('/').$adver->image;
        }
        return response()->json($advertisements);
    }
}
