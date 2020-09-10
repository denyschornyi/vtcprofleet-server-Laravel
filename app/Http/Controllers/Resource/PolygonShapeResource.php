<?php

namespace App\Http\Controllers\Resource;

use App\PoiCategory;
use App\PolygonShape;
use App\ServiceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Setting;
use Exception;

class PolygonShapeResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
//        $this->middleware('permission:poi-category-list', ['only' => ['index']]);
//        $this->middleware('permission:poi-category-create', ['only' => ['create','store']]);
//        $this->middleware('permission:poi-category-edit', ['only' => ['edit','update']]);
//        $this->middleware('permission:poi-category-delete', ['only' => ['destroy']]);

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        $polygon_shape = PoiCategory::join('polygon_shapes','polygon_shapes.poi_category_id','=','poi_categories.id')->select('polygon_shapes.*','poi_categories.type')->get();

        if($request->ajax()) {
            return $polygon_shape;
        } else {
            return view('admin.polygon-shape.index', compact('polygon_shape'));
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('');
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
     * @param  ServiceType  $serviceType
     * @return Response
     */
    public function show($id)
    {
        try {
            $obj = PolygonShape::findOrFail($id);

	        return view('admin.polygon-shape.show',compact('obj'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ServiceType  $serviceType
     * @return Response
     */
    public function edit($id)
    {
        try {
            $obj = PolygonShape::findOrFail($id);
	        $poi_category = PoiCategory::all()->where('status',1);

            return view('admin.polygon-shape.edit',compact('obj','poi_category'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request      $request
     * @param  ServiceType $serviceType
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
	        'title' => 'required|max:255'
        ]);
		$pointInterest = new PointInterestResource();
        try {
	        $bound = $request->input('bound');
	        $temp_bound = substr($bound,1,-1);
	        $lat = explode(',',$temp_bound)[0];
	        $lng = explode(',',$temp_bound)[1];
	        $coordinate = ['lat'=>$lat,'lng'=>$lng];
	        $geofence_val = $request->input('geofence_latlng');
//	        dd($geofence_val);
	        $shape = $pointInterest->generateShape($geofence_val);
	        $title = $request->input('title');

	        $obj = PolygonShape::findorFail($id);
	        $obj->title = $title;
	        $obj->coordinate = json_encode($coordinate);
	        $obj->shape = $shape;
	        $obj->shape_origin = $geofence_val;
	        $obj->poi_category_id = $request->input('poi_category_val');

	        $obj->update();

            return redirect()->route('admin.polygonshape.index')->with('flash_success', trans('admin.point_shape.point_shape_update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ServiceType  $serviceType
     * @return Response
     */
    public function destroy($id)
    {
        try {
	        PolygonShape::find( $id )->delete();

            return back()->with('flash_success', trans('admin.point_shape.point_shape_delete'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        }
    }
}
