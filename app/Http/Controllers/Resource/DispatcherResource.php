<?php

namespace App\Http\Controllers\Resource;

use App\Dispatcher;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use Setting;
use App\Admin;
use App\Helpers\Helper;

class DispatcherResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store','update', 'destroy']]);
        $this->middleware('permission:dispatcher-list', ['only' => ['index']]);
        $this->middleware('permission:dispatcher-create', ['only' => ['create','store']]);
        $this->middleware('permission:dispatcher-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:dispatcher-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $dispatchers = Admin::where('id', '!=', Auth::id())->where('fleet_id','0')->orderBy('id' , 'asc')->role('DISPATCHER')->get();
        foreach($dispatchers as $dispatcher) {
            $dd = Admin::where('id', $dispatcher->guard_id)->first();
            if ($dd)
                $dispatcher->guard_name = $dd->name;
        }
        // $dispatchers = Dispatcher::orderBy('created_at' , 'desc')->get();
        return view('admin.dispatcher.index', compact('dispatchers'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.dispatcher.create');
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
            'email' => 'required|unique:admins,email|email|max:255',
            'country_code' => 'required|max:25',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $users = $request->all();

            $users['password'] = bcrypt($request->password);
            $roles = 2; // role DISPATCHER
            if($request->hasFile('picture')) {
                $users['picture'] = $request->picture->store('admin/profile');
            }
            $users['guard_id'] = Auth::id();

            $users = Admin::create($users);
            $users->assignRole($roles);

            Helper::welcomeEmailToNewUser('dispatcher', Admin::find($users->id));

            return back()->with('flash_success', trans('admin.dispatcher_msgs.dispatcher_saved'));

        }

        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.dispatcher_msgs.dispatcher_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Dispatcher $dispatcher
     *
     * @return Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Dispatcher $dispatcher
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $dispatcher = Admin::findOrFail($id);
            return view('admin.dispatcher.edit',compact('dispatcher'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request    $request
     * @param Dispatcher $dispatcher
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
        ]);

        try {

            $user = Admin::findOrFail($id);

            if($request->hasFile('picture')) {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('admin/profile');
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->save();

            return redirect('/admin/dispatch-manager')->with('flash_success', trans('admin.dispatcher_msgs.dispatcher_update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.dispatcher_msgs.dispatcher_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Dispatcher  $dispatcher
     *
     * @return Response
     */
    public function destroy($id)
    {

        try {
            Admin::find($id)->delete();
            return back()->with('message', trans('admin.dispatcher_msgs.dispatcher_delete'));
        }
        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.dispatcher_msgs.dispatcher_not_found'));
        }
    }

}
