@extends('layouts.app')

@section('styles')
    <link href="{{ URL::to('css/onsip.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-phone"></i> Phone <button id="start" class="btn btn-sm btn-success"><i class="fa fa-check"></i> Start</button> <button id="register" class="btn btn-sm btn-primary">Register</button>
                    <p class="pull-right">
                        <span id="status" class="label label-danger">Disconnected</span>
                    </p>
                </div>

                <div class="panel-body">
                    <form action="" id="phone" class="form-horizontal">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <audio src="" id="remoteAudio"></audio>
                        </div>
                        <div class="form-group">
                            <label for="number" class="col-sm-2 control-label">Dial</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="number" name="number" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="button" id="dial" class="btn btn-primary">
                                    <span class="fa-stack">
                                        <i class="fa fa-square-o fa-stack-2x"></i>
                                        <i class="fa fa-phone fa-stack-1x"></i>
                                    </span>
                                     Dial
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="outgoingAlert" class="panel panel-primary">
                <div class="panel-body">
                    <h3>
                        Outgoing Call to <span class="label label-info" id="outgoingNumber">03350362957</span>
                    </h3>
                    <p id="outgoingStatus" class="alert alert-success">
                        Connected
                    </p>
                    <p id="outgoingRequest">
                    <button id="outgoingAccept" class="btn btn-primary">
                        <i class="fa fa-phone"></i> Accept
                    </button>
                    <button id="outgoingReject" class="btn btn-danger">
                        <i class="fa fa-ban"></i> Reject
                    </button>
                    </p>
                    <p id="outgoingRequestAccepted">
                        <button id="outgoingRequestHangup" class="btn btn-danger">
                            <i class="fa fa-ban"></i> Hangup
                        </button>
                        <button id="outgoingRequestHold" class="btn btn-info">
                            <i class="fa fa-circle"></i> Hold
                        </button>
                        <button id="outgoingRequestMute" class="btn btn-warning">
                            <i class="fa fa-volume-off"></i> Mute
                        </button>
                    </p>
                </div>
            </div>
            <div id="incomingAlert" class="panel panel-primary">
                <div class="panel-body">
                    <h3>
                        Incoming Call from <span class="label label-info" id="incomingNumber">03350362957</span>
                    </h3>
                    <p id="incomingStatus" class="alert alert-success">
                        Connected
                    </p>
                    <p id="incomingRequest">
                        <button id="incomingAccept" class="btn btn-primary">
                            <i class="fa fa-phone"></i> Accept
                        </button>
                        <button id="incomingReject" class="btn btn-danger">
                            <i class="fa fa-ban"></i> Reject
                        </button>
                    </p>
                    <p id="incomingRequestAccepted">
                        <button id="incomingRequestHangup" class="btn btn-danger">
                            <i class="fa fa-ban"></i> Hangup
                        </button>
                        <button id="incomingRequestHold" class="btn btn-info">
                            <i class="fa fa-circle"></i> Hold
                        </button>
                        <button id="incomingRequestMute" class="btn btn-warning">
                            <i class="fa fa-volume-off"></i> Mute
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ URL::to('js/sip-0.7.5.js') }}"></script>
    <script>

        function getUserMediaSuccess (stream) {
            console.log('getUserMedia succeeded', stream)
            mediaStream = stream;
        }

        function getUserMediaFailure (e) {
            console.error('getUserMedia failed:', e);
        }

        $(document).ready(function() {

            $("#incomingAlert").hide();
            $("#incomingStatus").hide();
            $("#incomingRequest").hide();
            $("#incomingRequestAccepted").hide();
            $("#outgoingAlert").hide();
            $("#outgoingStatus").hide();
            $("#outgoingRequest").hide();
            $("#outgoingRequestAccepted").hide();

            var config = {
                userAgentString: 'Gorilla VoIP Client',
                traceSip: true,
                register: false,
                uri: "{{ $user->sipuri }}",
                wsServers: ["ws://127.0.0.1:8088/ws"],
                authorizationUser: "{{ $user->sipusername }}",
                password: "{{ $user->sippassword }}",
                stunServers: []
            };

            var ua;
            var sessionUIs = {};

            $("#start").on("click", function (e) {
                e.preventDefault();

                ua = new SIP.UA(config);

                ua.on('connected', function () {
                    $("#register").html("Connected (Unregistered)");
                    $("#start").html("<i class='fa fa-times'></i> Stop");
                });

                ua.on('registered', function () {
                    $("#register").html("Unregister");
                    $("#status").html("Connected (Registered)").removeClass("label-danger").addClass("label-success");
                });

                ua.on('unregistered', function () {
                    $("#register").html("Register");
                    $("#status").html("Connected (Unregistered)").removeClass("label-success").addClass("label-danger");
                });

                ua.on("invite", function (session) {
                    $("#phone").addClass("disabled");
                    $("#incomingNumber").html(session.remoteIdentity.displayName);
                    $("#incomingAlert").show();
                    $("#incomingRequest").show();
                    $("#incomingAccept").on("click", function (e) {
                        //e.preventDefault();
                    var options = {
                        media: {
                            constraints: {
                                audio: true,
                                video: false
                            },
                            render: {
                                remote: document.getElementById('remoteAudio')
                            }
                        }
                    };
                        session.accept(options);
                    });
                    session.on("accepted", function (data) {
                        $("#incomingRequest").hide();
                        $("#incomingStatus").show();
                        $("#incomingRequestAccepted").show();
                    });
                    $("#incomingRequestHangup").on("click", function (e) {
                        e.preventDefault();
                        session.bye();
                    });
                    session.on("rejected", function (data) {
                        //$("#incomingAlert").hide();
                    });
                    session.on("bye", function (data) {
                        //$("#incomingAlert").hide();
                    });
                    session.on("terminated", function(message, cause) {
                        $("#incomingAlert").hide();
                        $("#incomingStatus").hide();
                        $("#incomingRequestAccepted").hide();
                        $("#phone").removeClass("disabled");
                    });
                    $("#incomingRequestHold").on("click", function (e) {
                        e.preventDefault();
                        session.isOnHold().local ? session.unhold() : session.hold();
                    });
                    $("#incomingRequestMute").on("click", function (e) {
                        e.preventDefault();
                        session.toggleMuteAudio();
                    });
                    //console.log(session);
                });


            });

            $("#register").on("click", function (e) {
                e.preventDefault();
                if (!ua) return;

                if (ua.isRegistered()) {
                    ua.unregister();
                } else {
                    ua.register();
                }
            });

            $("#dial").on("click", function (e) {
                e.preventDefault();

                var number = $("#number").val();
                var options = {
                    media: {
                        constraints: {
                            audio: true,
                            video: false
                        },
                        render: {
                            remote: document.getElementById('remoteAudio')
                        }
                    }
                };
                session = ua.invite(number, options);

                session.on("progress", function (data) {
                    $("#outgoingAlert").show();
                    $("#phone").addClass("disabled");
                    console.log(session.remoteIdentity);
                    $("#outgoingNumber").html(session.remoteIdentity.number);
                });

                session.on("accepted", function (data) {
                    $("#outgoingRequest").hide();
                    $("#outgoingStatus").show();
                    $("#outgoingRequestAccepted").show();
                });
                $("#outgoingRequestHangup").on("click", function (e) {
                    e.preventDefault();
                    session.bye();
                });
                session.on("rejected", function (data) {
                    //$("#outgoingAlert").hide();
                });
                session.on("bye", function (data) {
                    //$("#outgoingAlert").hide();
                });
                session.on("terminated", function(message, cause) {
                    $("#outgoingAlert").hide();
                    $("#outgoingStatus").hide();
                    $("#outgoingRequestAccepted").hide();
                    $("#phone").removeClass("disabled");
                });

                $("#outgoingRequestHold").on("click", function (e) {
                    e.preventDefault();
                    session.isOnHold().local ? session.unhold() : session.hold();
                });
                $("#outgoingRequestMute").on("click", function (e) {
                    e.preventDefault();
                    session.toggleMuteAudio();
                });
            });
        });
    </script>
@endsection
