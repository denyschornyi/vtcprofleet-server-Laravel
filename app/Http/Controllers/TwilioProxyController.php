<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class TwilioProxyController extends Controller
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
     * createPhoneCall
     *
     * @return \Illuminate\Http\Response
     */
    public function createPhoneCall(Request $request)
    {
        // $this->validate($request, [
        //     'cellphone' => 'required',
        //     'email' => 'required|email',
        //     'country_code' => 'required',
        // ]);
        // // test
        // echo \json_encode(['success' => true, 'message' => 'test', 'authy_id' => '123456']);
        // return;
        // // test

        // try {
            $sid = env('TWILIO_SID', '');
            $token = env('TWILIO_TOKEN', '');
            $twilio = new Client($sid, $token);

            $proxy_sid = env('TWILIO_PROXY_SERVICE_ID', '');

            // create session for user & provider
            $session = $twilio->proxy->v1->services($proxy_sid)
                ->sessions
                ->create(["uniqueName" => "MyFirstSession1"]);

            echo json_encode($session);

            $participant = $twilio->proxy->v1->services($proxy_sid)
                ->sessions($session->sid)
                ->participants
                ->create("+33629957457", // identifier
                    ["friendlyName" => "Weichong1"]
                );

            echo json_encode($participant);

            $participant2 = $twilio->proxy->v1->services($proxy_sid)
                ->sessions($session->sid)
                ->participants
                ->create("+33760910900", // identifier
                    ["friendlyName" => "Weichong2"]
                );

            echo json_encode($participant2);

        // } catch (\Throwable $th) {
        //     echo json_encode($th);
        // }

        // echo json_encode($request->all());
    }

}
