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
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    use WtLogTrait;

    protected string $url = 'api/external/v1/organic/leads';
    protected bool $debug = true;
    protected string $code = 'Ca4aA-324de-24Lf4-gpMJ3';
    protected string $crmTestUlr = 'https://click-academy-api-81.octohub.it';
    protected string $crmUlr = 'https://crmapi81.appclickacademy.it';
    public Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendWebHook()
    {

    }

    public function addLead(Request $request)
    {
        if($request->input('code') === $this->code){
            $lead = $this->createLead($request);
            $auth = $this->auth();
            $this->sendLead($auth, $lead);
            //SendLeadToMake::dispatch($data);

        }
        return abort(404);

    }

    protected function createLead(Request $request): Lead
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $dataLead = [
            'source' => $request->ip(),
            'referer' => $data['referer-page'],
            'name' => $data['nome'],
            'surname' => $data['cognome'],
            'region' => $data['regione'],
            'province' => $data['provincia'],
            'city' => $data['city'] ?? 'UNDEFINED',
            'country' => 'IT',
            'phone' => $data['telefono'],
            'email' => $data['email'],
            'course' => $data['corso'],
            'accept970_at' => Carbon::now()->toDateTimeString(),
            'request_webhook' => json_encode([
                'header' => $request->header(),
                'data' => $request->all(),
            ])
        ];
        $lead = Lead::create($dataLead);
        $this->logDebug('Create lead', [
            'header' => $request->header(),
            'data' => $request->all(),
            'lead' => $lead->toArray()
        ],'INFO');
        return $lead;
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
                'trace' => $e->getTrace()
            ],'ERROR');
            return null;
        }
        if($response->successful()) {
            $this->logDebug('Auth Success', [
                'header' => $response->headers(),
                'body' => $response->body(),
                'bodyObject' => $response->object(),
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
        ],'WARNING');
        return null;
    }

    /**
     * @param object|array|null $auth
     * @param Lead $lead
     * @return array|object|void
     */
    protected function sendLead(object|array|null $auth, Lead $lead)
    {
        $data = [
            'name' => $lead->name,
            'surname' => $lead->surname,
            'master' => $lead->course,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'region' => $lead->region,
            'city' => $lead->city,
            'province' => $lead->province,
        ];
        try {
            $response = Http::withToken($auth->access_token)
                ->withHeaders([
                    'User-Agent' => 'middleware3-abtg/1.0',
                    'Accept' => 'application/json',
                ])->post($this->getUrl($this->url), $data);
        } catch (\Exception $e) {
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ];
            $this->logDebug('Auth Exception', $data,'ERROR');
            Lead::update([
                'response_processed' => Carbon::now()->toDateTimeString(),
                'response_crm' => json_encode($data)
            ]);
            exit();
        }
        if($response->successful()) {
            $data = [
                'data' => $data,
                'header' => $response->headers(),
                'body' => $response->body(),
                'bodyObject' => $response->object(),
                'statusCode' => $response->status(),
                'reason' => $response->reason(),
            ];
            $this->logDebug('Send Lead Success', $data,'INFO');
            Lead::update([
                'response_processed' => Carbon::now()->toDateTimeString(),
                'response_crm' => json_encode($data)
            ]);
            return $response->object();
        }

        $data = [
            'data' => $data,
            'header' => $response->headers(),
            'body' => $response->object(),
            'statusCode' => $response->status(),
            'reason' => $response->reason()
        ];
        $this->logDebug('Auth Fail', $data,'WARNING');
        Lead::update([
            'response_processed' => Carbon::now()->toDateTimeString(),
            'response_crm' => json_encode($data)
        ]);
    }
}
