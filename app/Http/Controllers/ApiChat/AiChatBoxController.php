<?php

namespace App\Http\Controllers\ApiChat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatBoxController extends Controller
{

    public function getBotResponse(Request $request){
        $response = ['status'=>false, 'message'=>''];
        try {
             $user_message = $request->input('msg');
             $resP = Http::post('http://127.0.0.1:5000/get', [
                'msg' => $user_message
             ]);
            
            // Check if response is valid and contains 'response' key
             if ($resP->successful()) {
                $resP_data = $resP->json();
                if (isset($resP_data['response'])) {
                    $response['status']= false;
                    $response['message'] =  $resP_data['response'];
                } else {
                    $response['message'] = 'Sorry, I could not understand your request. Please try again.';
                }
             } else {
                $response['message'] = 'Sorry, something went wrong with the chatbot service.';
             }
        } catch (\Exception $e) {
            $response['message'] = 'Error connecting to chatbot service: ' . $e->getMessage();
        }
        return response()->json($response);
    }
}
