<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use phpari;

class StatisController extends Controller
{
    public function generateCall(Request $request)
    {
        $ari = new phpari("hello-world");
        $response = $ari->channels()->originate(
            'SIP/22' . $request->endpoint . '@box1',
            uniqid("channel_"),
            [
                "extension"      => "6002",
                "context"        => 'default',
                "priority"       => 1,
                "app"            => "hello-world",
                "appArgs"        => "",
                "callerId"       => "Abdullah <2138797850>",
                "timeout"        => 30,
                "channelId"      => uniqid("channel_"),
                "otherChannelId" => ""
            ],
            ["CALLERID(name)" => "Abdullah", "CALLERID(number)" => "2138797850"]
        );
        return $ari->lasterror;
    }

    private function init($appname = NULL)
    {

    }
}
