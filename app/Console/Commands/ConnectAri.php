<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use phpari;

class ConnectAri extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ari:connect {appname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connects to ARI';

    private $ariEndpoint;
    private $stasisClient;
    private $stasisLoop;
    private $phpariObject;
    private $stasisChannelID;
    private $dtmfSequence = "";

    public $stasisLogger;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init($appname = NULL)
    {
        try {
            if (is_null($appname))
                throw new \Exception("[" . __FILE__ . ":" . __LINE__ . "] Stasis application name must be defined!", 500);

            $this->phpariObject = new phpari($appname);

            $this->ariEndpoint  = $this->phpariObject->ariEndpoint;
            $this->stasisClient = $this->phpariObject->stasisClient;
            $this->stasisLoop   = $this->phpariObject->stasisLoop;
            $this->stasisLogger = $this->phpariObject->stasisLogger;
            $this->stasisEvents = $this->phpariObject->stasisEvents;
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit(99);
        }
    }

    public function setDtmf($digit = NULL)
    {
        try {

            $this->dtmfSequence .= $digit;

            return TRUE;

        } catch (\Exception $e) {
            return FALSE;
        }
    }

    // process stasis events
    public function StasisAppEventHandler()
    {
        $this->stasisEvents->on('StasisStart', function ($event) {
            $this->info("Event received: StasisStart");
            $this->stasisChannelID = $event->channel->id;
            $result = $this->phpariObject->channels()->channel_playback($this->stasisChannelID, "sound:demo-congrats", null, null, null, "playback1");

        });

        $this->stasisEvents->on('StasisEnd', function ($event) {
            /*
             * The following section will produce an error, as the channel no longer exists in this state - this is intentional
             */
            $this->info("Event received: StasisEnd");
            if (!$this->phpariObject->channels()->channel_delete($this->stasisChannelID))
                $this->info("Error occurred: " . $this->phpariObject->lasterror);
        });


        $this->stasisEvents->on('PlaybackStarted', function ($event) {
            $this->info("+++ PlaybackStarted +++ " . json_encode($event->playback) . "\n");
        });

        $this->stasisEvents->on('PlaybackFinished', function ($event) {
            $this->info("+++ PlaybackFinished +++ " . json_encode($event->playback) . "\n");
        });

        $this->stasisEvents->on('ChannelDtmfReceived', function ($event) {
            $this->setDtmf($event->digit);
            //$this->info(json_encode($event));
            $this->info("+++ DTMF Received +++ [" . $event->digit . "] [" . $this->dtmfSequence . "]\n");
            switch ($event->digit) {
                case "1":
                    if (!$this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:auth-thankyou', NULL, NULL, NULL, 'end1'))
                        $this->info(json_encode($this->phpariObject->lasterror));
                    break;
                case "2":
                    if (!$this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:auth-thankyou', NULL, NULL, NULL, 'end2'))
                        $this->info(json_encode($this->phpariObject->lasterror));
                    break;
                default:
                    if (!$this->phpariObject->channels()->channel_playback($this->stasisChannelID, 'sound:invalid', NULL, NULL, NULL, 'end3'))
                        $this->info(json_encode($this->phpariObject->lasterror));
                    break;
            }
        });
    }

    public function StasisAppConnectionHandlers()
    {
        try {
            $this->stasisClient->on("request", function ($headers) {
                $this->info("Request received!");
            });

            $this->stasisClient->on("handshake", function () {
                $this->info("Handshake received!");
            });

            $this->stasisClient->on("message", function ($message) {
                $event = json_decode($message->getData());
                $this->info('Received event: ' . $event->type);
                $this->stasisEvents->emit($event->type, array($event));
            });

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit(99);
        }
    }

    public function _execute()
    {
        try {
            $this->stasisClient->open();
            $this->stasisLoop->run();
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit(99);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init($this->argument("appname"));
        $this->info("Starting Stasis Program... Waiting for handshake...");
        $this->StasisAppEventHandler();
        $this->info("Initializing Handlers... Waiting for handshake...");
        $this->StasisAppConnectionHandlers();
        $this->info("Connecting... Waiting for handshake...");
        $this->_execute();
    }
}
