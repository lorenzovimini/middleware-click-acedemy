<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Support\WtLogTrait;
use App\Jobs\SendLeadCrm;
use App\Jobs\SendLeadToMake;
use App\Models\Lead;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{
    use WtLogTrait;

    protected bool $debug = true;
    protected string $code = 'Ca4aA-324de-24Lf4-gpMJ3';
    protected string $crmTestUlr = 'https://click-academy-api-81.octohub.it';
    protected string $crmUlr = 'https://crmapi81.appclickacademy.it';
    public Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function addLead(Request $request)
    {
        \Log::log('info',json_encode($request->all()));
        $content = $request->getContent();
        $data = json_decode($content, true);
        $dataLead = [
            'source' => $request->ip(),
            'referer' => $data['referer-page'],
            'type' => $data['type'],
            'name' => $data['nome'],
            'surname' => $data['cognome'],
            'region' => $data['regione'],
            'state' => $data['provincia'],
            'country' => 'IT',
            'phone' => $data['telefono'],
            'email' => $data['email'],
            'course' => $data['corso'],
            'accept970_at' => Carbon::now()->toDateTimeString(),
        ];
        Lead::create($dataLead);
        $this->logDebug('Create lead', [
            'header' => $request->header(),
            'data' => $request->all(),
            'lead' => $dataLead
        ],'INFO');


        //$auth = $this->auth();
        //SendLeadToMake::dispatch($data);
        //SendLeadCrm::dispatch($data);
    }

    protected function getUrl(string $url): string
    {
        if($this->debug){
            return $this->crmTestUlr . '/' . $url;
        }
        return $this->crmUlr . '/' . $url;
    }

    /**
     * @return object|array|null
     */
    protected function auth(): object|array|null
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'middleware3-abtg/1.0',
                'Accept' => 'application/json',
            ])->post($this->getUrl('api/external/v1/oauth/token'), [
                'grant_type' => 'client_credentials',
                'scope' => '*',
                'client_id' => '98433ce6-4d8e-458f-87a9-83a472667196',
                'client_secret' => 'qVhNlyr0q49zAa5WuzkY28cu3dec7yOnFyowMaN4'
            ]);
        } catch (\Exception $e){
            $this->logDebug('Auth Exception', [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                '' => $e->getTrace()
            ],'ERROR (EXCEPTION)');
            return null;
        }
        if($response->successful()) {
            $this->logDebug('Auth Success', [
                'header' => $response->headers(),
                'body' => $response->object(),
                'statusCode' => $response->status(),
                'reason' => $response->reason(),
            ],'INFO');
            return $response->object();
        }

        $this->logDebug('Auth Fail', [
            'header' => $response->headers(),
            'body' => $response->object(),
            'statusCode' => $response->status(),
            'reason' => $response->reason()
        ],'ERROR (FAIL)');
        return null;
    }

    protected function sendDataToCrm($data)
    {

    }
}
