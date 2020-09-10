<?php

namespace App\Http\Controllers\Resource;

use anlutro\LaravelSettings\ArrayUtil;
use App\PoiCategory;
use App\PointInterest;
use App\PolygonShape;
use App\ServiceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mdanter\Ecc\Primitives\Point;
use Setting;
use Exception;

class PointInterestResource extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'demo',
			[ 'only' => [ 'store', 'update', 'destroy' ] ] );
		$this->middleware( 'permission:point-interest-list',
			[ 'only' => [ 'index' ] ] );
		$this->middleware( 'permission:point-interest-create',
			[ 'only' => [ 'create', 'store' ] ] );
		$this->middleware( 'permission:point-interest-edit',
			[ 'only' => [ 'edit', 'update' ] ] );
		$this->middleware( 'permission:point-interest-delete',
			[ 'only' => [ 'destroy' ] ] );

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index( Request $request ) {
		$point_interest = DB::table( 'point_interests' )
			->join( 'poi_categories',
				'poi_categories.id',
				'=',
				'point_interests.start_poicategory_id' )
			->join( 'poi_categories as pc',
				'pc.id',
				'=',
				'point_interests.dest_poicategory_id' )
			->select( 'point_interests.*',
				'poi_categories.type as start_type',
				'pc.type as destination_type' )
			->where( [ 'poi_categories.status' => '1', 'pc.status' => '1' ] )
			->orderBy( 'point_interests.id', 'desc' )->get();
		if ( $request->ajax() ) {
			return $point_interest;
		} else {
			return view( 'admin.point-interest.index',
				compact( 'point_interest' ) );
		}

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create() {
		$poi_category = PoiCategory::all()->where( 'status', 1 );
		$service_type =
			ServiceType::select( 'id', 'name' )->where( 'fleet_id', 0 )->get();
		$shape_data   = PolygonShape::all();

		return view( 'admin.point-interest.create',
			compact( 'poi_category', 'service_type', 'shape_data' ) );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function store( Request $request ) {
		$this->validate( $request,
			[
				'type' => 'required|max:255',
				'poi_category_val'=>'required',
				'poi_category_val1'=>'required'
			] );

		try {

			$point_interest            = new PointInterest();
			$point_interest->rule_name = $request->input( 'type' );

			$bound = $request->input( 'bound' );
			if ( $bound === '' ) {
				$point_interest->start_coordinate = json_encode( array(
					'lat' => $request->input( 'lat' ),
					'lng' => $request->input( 'lng' ),
				) );
			} else {
				$reuslt                           =
					substr( $request->input( 'bound' ), 1, - 1 );
				$point_interest->start_coordinate = json_encode( array(
					'lat' => explode( ',', $reuslt )[0],
					'lng' => explode( ',', $reuslt )[1],
				) );
			}
			$point_interest->start_mapdata        =
				$this->generateShape( $request->input( 'geofence_latlng' ) );
			$point_interest->start_mapdata_origin =
				$request->input( 'geofence_latlng' );
			$point_interest->start_mapdata_latlng =
				$request->input( 'geofence_latlng_dispatcher' );

			$point_interest->start_poicategory_id =
				$request->input( 'poi_category_val' );

			$bound1 = $request->input( 'bound1' );
			if ( $bound1 === '' ) {
				$point_interest->dest_coordinate = json_encode( array(
					'lat' => $request->input( 'lat_dest' ),
					'lng' => $request->input( 'lng_dest' ),
				) );
			} else {
				$reuslt                          =
					substr( $request->input( 'bound1' ), 1, si - 1 );
				$point_interest->dest_coordinate = json_encode( array(
					'lat' => explode( ',', $reuslt )[0],
					'lng' => explode( ',', $reuslt )[1],
				) );
			}

			$point_interest->dest_mapdata        =
				$this->generateShape( $request->input( 'geofence_other_latlng' ) );
			$point_interest->dest_mapdata_origin =
				$request->input( 'geofence_other_latlng' );
			$point_interest->dest_mapdata_latlng =
				$request->input( 'geofence_other_latlng_dispatcher' );

			$point_interest->dest_poicategory_id =
				$request->input( 'poi_category_val1' );
			if ( $request->input( 'ignore_surge_pricing_rule' ) === null ) {
				$point_interest->ignore_surge_price = 0;
			} else {
				$point_interest->ignore_surge_price =
					$request->input( 'ignore_surge_pricing_rule' );
			}

			$point_interest->price =
				$request->input( 'quickcab_geofence_fixed_price_amount' );

			if ( $request->input( 'vehicle' ) !== null ) {
				$vehicle = '';
				foreach ( $request->input( 'vehicle' ) as $val ) {
					$vehicle .= $val . ',';
				}
				$point_interest->service_type_id = rtrim( $vehicle, ',' );
			} else {
				$point_interest->service_type_id = 0;
			}

			$point_interest->direction_state =
				$request->input( 'quickcab_geofence_direction' );
			$point_interest->status          = $request->status;
			$point_interest->save();

			return redirect()->route( 'admin.pointinterest.index' )
				->with( 'flash_success',
					trans( 'admin.poi_category_type_msgs.poi_category_saved' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.poi_category_type_msgs.poi_category_not_found' ) );
		}
	}

	public function generateShape( $shape ) {
		if ( $shape !== '' ) {
			$last = $shape . explode( ',', $shape )[0];

			$result = '';
			for ( $i = 0; $i < count( explode( ',', $last ) ); $i ++ ) {
				if ( $i + 1 === count( explode( ',', $last ) ) ) {
					$result .= '[' . str_replace( ':',
							',',
							explode( ',', $last )[ $i ] ) . ']';
				} else {
					$result .= '[' . str_replace( ':',
							',',
							explode( ',', $last )[ $i ] ) . ']' . ',';
				}
			}

			return '[[' . $result . ']]';
		} else {
			return '';
		}

	}

	/**
	 * Display the specified resource.
	 *
	 * @param ServiceType $serviceType
	 *
	 * @return Response
	 */
	public function show( $id ) {
		try {
			$point_interest   = PointInterest::where( 'id', $id )->first();
			$service_type_ids =
				explode( ',', $point_interest->service_type_id );
			$service_type     = ServiceType::whereIn( 'id', $service_type_ids )
				->select( 'name' )->get();

			return view( 'admin.point-interest.show',
				compact( 'point_interest', 'service_type' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.poi_category_type_msgs.poi_category_not_found' ) );
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param ServiceType $serviceType
	 *
	 * @return Response
	 */
	public function edit( $id ) {
		try {
			$poi_category     = PoiCategory::all()->where( 'status', 1 );
			$point_interest   = PointInterest::where( 'id', $id )->first();
			$service_type_ids =
				explode( ',', $point_interest->service_type_id );
			$service_type     =
				ServiceType::whereIn( 'id', $service_type_ids )->pluck( 'id' );
			$service_types    =
				ServiceType::select( 'id', 'name' )->where( 'fleet_id', 0 )
					->get();
			$shape_data       = PolygonShape::all();

			return view( 'admin.point-interest.edit',
				compact( 'point_interest',
					'service_type',
					'service_types',
					'shape_data',
					'poi_category' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.poi_category_type_msgs.poi_category_not_found' ) );
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
	public function update( Request $request, $id ) {

		$this->validate( $request,
			[
				'type' => 'required|max:255',
				'poi_category_val'=>'required',
				'poi_category_val1'=>'required'
			] );

		try {
			$point_interest = PointInterest::findorFail( $id );

			$point_interest->rule_name = $request->input( 'type' );

			$bound = $request->input( 'bound' );
			if ( $bound === '' ) {
				$point_interest->start_coordinate = json_encode( array(
					'lat' => $request->input( 'lat' ),
					'lng' => $request->input( 'lng' ),
				) );
			} else {
				$reuslt                           =
					substr( $request->input( 'bound' ), 1, - 1 );
				$point_interest->start_coordinate = json_encode( array(
					'lat' => explode( ',', $reuslt )[0],
					'lng' => explode( ',', $reuslt )[1],
				) );
			}
			$point_interest->start_mapdata        =
				$this->generateShape( $request->input( 'geofence_latlng' ) );
			$point_interest->start_mapdata_origin =
				$request->input( 'geofence_latlng' );
			$point_interest->start_mapdata_latlng =
				$request->input( 'geofence_latlng_dispatcher' );
			$point_interest->start_poicategory_id =
				$request->input( 'poi_category_val' );

			$bound1 = $request->input( 'bound1' );
			if ( $bound1 === '' ) {
				$point_interest->dest_coordinate = json_encode( array(
					'lat' => $request->input( 'lat_dest' ),
					'lng' => $request->input( 'lng_dest' ),
				) );
			} else {
				$reuslt                          =
					substr( $request->input( 'bound1' ), 1, - 1 );
				$point_interest->dest_coordinate = json_encode( array(
					'lat' => explode( ',', $reuslt )[0],
					'lng' => explode( ',', $reuslt )[1],
				) );
			}

			$point_interest->dest_mapdata        =
				$this->generateShape( $request->input( 'geofence_other_latlng' ) );
			$point_interest->dest_mapdata_origin =
				$request->input( 'geofence_other_latlng' );
			$point_interest->dest_mapdata_latlng =
				$request->input( 'geofence_other_latlng_dispatcher' );
			$point_interest->dest_poicategory_id =
				$request->input( 'poi_category_val1' );

			if ( $request->input( 'ignore_surge_pricing_rule' ) === null ) {
				$point_interest->ignore_surge_price = 0;
			} else {
				$point_interest->ignore_surge_price =
					$request->input( 'ignore_surge_pricing_rule' );
			}

			$point_interest->price =
				$request->input( 'quickcab_geofence_fixed_price_amount' );

			if ( $request->input( 'vehicle' ) !== null ) {
				$vehicle = '';
				foreach ( $request->input( 'vehicle' ) as $val ) {
					$vehicle .= $val . ',';
				}
				$point_interest->service_type_id = rtrim( $vehicle, ',' );
			} else {
				$point_interest->service_type_id = 0;
			}

			$point_interest->direction_state =
				$request->input( 'quickcab_geofence_direction' );
			$point_interest->status          = $request->status;

			$point_interest->update();

			return redirect()->route( 'admin.pointinterest.index' )
				->with( 'flash_success',
					trans( 'admin.point.Point_Interest_update' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.point.Point_Interest_not_found' ) );
		}
	}

	//import shape according to one data

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param ServiceType $serviceType
	 *
	 * @return Response
	 */
	public function destroy( $id ) {
		try {
			PointInterest::find( $id )->delete();

			return back()->with( 'flash_success',
				trans( 'admin.point.Point_Interest_delete' ) );
		} catch ( ModelNotFoundException $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.point.Point_Interest_not_found' ) );
		} catch ( Exception $e ) {
			return back()->with( 'flash_error',
				trans( 'admin.point.Point_Interest_not_found' ) );
		}
	}

	//get entire import shape data

	public function getShape( Request $request ) {
		$id   = $request->input( 'id' );
		$data = PolygonShape::where( 'id', $id )->first();

		return json_encode( $data );
	}

	//save shape

	public function getShapeData() {
		$data = PolygonShape::all();

		return json_encode( $data );
	}

	public function saveShape( Request $request ) {

		$lat          = $request->input( 'lat' );
		$lng          = $request->input( 'lng' );
		$coordinate   = [ 'lat' => $lat, 'lng' => $lng ];
		$geofence_val = $request->input( 'geofence_val' );
		$shape        = $this->generateShape( $geofence_val );
		$title        = $request->input( 'title' );

		$obj                  = new PolygonShape();
		$obj->title           = $title;
		$obj->coordinate      = json_encode( $coordinate );
		$obj->shape           = $shape;
		$obj->shape_origin    = $geofence_val;
		$obj->poi_category_id = $request->input( 'poi_category_val' );
		$obj->save();

		return response()->json( [ 'success' => true ] );
	}


}
