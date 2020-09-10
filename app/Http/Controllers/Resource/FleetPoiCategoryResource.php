<?php

namespace App\Http\Controllers\Resource;

use App\FleetPoiCategory;
use App\PoiCategory;
use App\ServiceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Setting;
use Exception;

class FleetPoiCategoryResource extends Controller
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
        $poi_category = FleetPoiCategory::all()->where('fleet_id',Auth::guard('fleet')->id());

        if($request->ajax()) {
            return $poi_category;
        } else {
            return view('fleet.poi-category.index', compact('poi_category'));
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('fleet.poi-category.create');
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
            'type' => 'required|unique:fleet_poi_categories,type|max:255',
            'image' => 'mimes:ico,png'
        ]);
		$typeCount = FleetPoiCategory::where([['type' , $request->input('type')],['fleet_id',Auth::guard('fleet')->id()]])->count();
		if($typeCount != '0'){
			return back()->with('flash_error', 'The type has already been taken.');
		}
        try {
            $poi_category = new FleetPoiCategory();
	        $poi_category->type = $request->type;

	        $dir = '/storage/user/poi';
	        $base_file_folder = public_path() . $dir;

	        if (!is_dir($base_file_folder)) {
		        if ( ! mkdir( $base_file_folder, 0777, true )
		             && ! is_dir( $base_file_folder )
		        ) {
			        throw new RuntimeException( sprintf( 'Directory "%s" was not created',
				        $base_file_folder ) );
		        }
	        }
            if($request->hasFile('image')) {
	            $poi_category->image = $request->image->store( 'user/poi' );
            }
	        $poi_category->status = $request->status;
	        $poi_category->fleet_id = Auth::guard('fleet')->id();

			$poi_category->save();
            return redirect()->route('fleet.poiCategory.index')->with('flash_success', trans('admin.poi_category_type_msgs.poi_category_saved'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        }
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
            return FleetPoiCategory::findOrFail($id);
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
            $poi_category = FleetPoiCategory::findOrFail($id);

            return view('fleet.poi-category.edit',compact('poi_category'));
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
	        'type' => 'required|max:255',
	        'image' => 'mimes:ico,png'
        ]);

        try {
	        $poi_category = FleetPoiCategory::findOrFail( $id );

	        $poi_category->type = $request->type;
	        if($request->hasFile('image')) {
		        Storage::delete( $poi_category->image );
		        $poi_category->image = $request->image->store( 'user/poi' );
	        }
	        $poi_category->status = $request->status;
			$poi_category->update();
            return redirect()->route('fleet.poiCategory.index')->with('flash_success', trans('admin.poi_category_type_msgs.poi_category_update'));
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
	        $poi_category = FleetPoiCategory::findOrFail( $id );
	        Storage::delete( $poi_category->image );
	        FleetPoiCategory::find( $id )->delete();

            return back()->with('flash_success', trans('admin.poi_category_type_msgs.poi_category_delete'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('admin.poi_category_type_msgs.poi_category_not_found'));
        }
    }
}
