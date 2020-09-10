<?php

namespace App\Http\Controllers;

use Authy;
use Illuminate\Http\Request;

class AuthyController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * requestSMS
     *
     * @return \Illuminate\Http\Response
     */
    public function requestSMS(Request $request)
    {
        $this->validate($request, [
            'cellphone' => 'required',
            'email' => 'required|email',
            'country_code' => 'required',
        ]);
        // // test
        // echo \json_encode(['success' => true, 'message' => 'test', 'authy_id' => '123456']); 
        // return;
        // // test

        $authy_api = new Authy\AuthyApi(env('AUTHY_API_KEY', ''));
        $user = $authy_api->registerUser($request->email, $request->cellphone, $request->country_code); // email, cellphone, country_code
        if ($user->ok()) {
            // printf($user->id());
            $sms = $authy_api->requestSms($user->id(), ['force' => 'true']);
            if ($sms->ok()) {
                // printf($sms->message());
                echo \json_encode(['success' => true, 'message' => $sms->message(), 'authy_id' => strval($user->id())]);
            } else {
                // print_r($sms->errors());
                echo \json_encode(['success' => false, 'message' => $sms->errors()]);
            }
        } else {
            echo \json_encode(['success' => false, 'message' => $user->errors()]);
            // foreach ($user->errors() as $field => $message) {
            //     printf("$field = $message\n");
            // }
        }
        // echo json_encode($request->all());
    }

    /**
     * verifySMS
     *
     * @return \Illuminate\Http\Response
     */
    public function verifySMS(Request $request)
    {
        $this->validate($request, [
            'authy_id' => 'required',
            'auth_token' => 'required',
        ]);
        // // test
        // echo \json_encode(['success' => true, 'message' => 'Success']);
        // return;
        // // test

        $authy_api = new Authy\AuthyApi(env('AUTHY_API_KEY', ''));
        try {
            $verification = $authy_api->verifyToken($request->authy_id, $request->auth_token);

            if ($verification->ok()) {
                echo \json_encode(['success' => true, 'message' => 'Success']);
            } else {
                echo \json_encode(['success' => false, 'message' => $verification->errors()]);
            }
        } catch (\Throwable $th) {
            echo \json_encode(['success' => false, 'message' => 'Something went wrong']);
        }
    }
}
