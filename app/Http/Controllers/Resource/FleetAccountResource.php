<?php

namespace App\Http\Controllers\Resource;

use App\Account;
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

class FleetAccountResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['store','update', 'destroy']]);
       /* $this->middleware('permission:account-manager-list', ['only' => ['index']]);
        $this->middleware('permission:account-manager-create', ['only' => ['create','store']]);
        $this->middleware('permission:account-manager-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:account-manager-delete', ['only' => ['destroy']]);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $accounts = Admin::where('created_by', '=', 'fleet_account')->orderBy('id' , 'asc')->role('ACCOUNT')->get();
        return view('fleet.account-manager.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('fleet.account-manager.create');
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
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

            $users = $request->all();

            $users['password'] = bcrypt($request->password);
            $roles = 4; // role ACCOUNT
            if($request->hasFile('picture')) {
                $users['picture'] = $request->picture->store('admin/profile');
            }
	        $users['created_by'] = 'fleet_account';

            $users = Admin::create($users);
            $users->assignRole($roles);

            Helper::welcomeEmailToNewUser('account', Admin::find($users->id));

            return back()->with('flash_success',trans('admin.account_manager_msgs.account_saved'));

        }

        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.account_manager_msgs.account_not_found'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Dispatcher  $account
     * @return Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Account $account
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $account = Admin::findOrFail($id);
            return view('fleet.account-manager.edit',compact('account'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Account $account
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

            return redirect()->route('fleet.account-manager.index')->with('flash_success', trans('admin.account_manager_msgs.account_update'));
        }

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('admin.account_manager_msgs.account_not_found'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Account  $dispatcher
     *
     * @return Response
     */
    public function destroy($id)
    {

        try {
            Admin::find($id)->delete();
            return back()->with('message', trans('admin.account_manager_msgs.account_delete'));
        }
        catch (Exception $e) {
            return back()->with('flash_error', trans('admin.account_manager_msgs.account_not_found'));
        }
    }

}
