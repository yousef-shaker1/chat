<?php

namespace App\Http\Controllers;

use Ably\AblyRest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    protected $ably;

    public function __construct()
    {
        $this->ably = new AblyRest(env('ABLY_API_KEY'));
    }

    public function startCall(Request $request)
    {
        $receiverId = $request->input('receiver_id'); // ID المستلم
        $offer = $request->input('offer'); // إشعار WebRTC (الاتصال)

        // نشر العرض إلى القناة الخاصة بالمستلم عبر Ably
        $this->ably->channels->get("calls-channel-{$receiverId}")
            ->publish('offer', [
                'sender_id' => Auth::id(),
                'offer' => $offer,
            ]);

        return response()->json(['status' => 'Calling...']);
    }

    public function answerCall(Request $request)
    {
        $callerId = $request->input('caller_id');
        $answer = $request->input('answer'); 

        $this->ably->channels->get("calls-channel-{$callerId}")
            ->publish('answer', [
                'receiver_id' => Auth::id(),
                'answer' => $answer,
            ]);

        return response()->json(['status' => 'Call Answered']);
    }

    public function endCall(Request $request)
    {
        $receiverId = $request->input('receiver_id');

        $this->ably->channels->get("calls-channel-{$receiverId}")
            ->publish('call-end', [
                'sender_id' => Auth::id(),
            ]);

        return response()->json(['status' => 'Call Ended']);
    }
}