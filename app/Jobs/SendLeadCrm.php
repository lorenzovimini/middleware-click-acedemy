<?php

namespace App\Jobs;

use App\Http\Controllers\Support\WtLogTrait;
use App\Models\Lead;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendLeadCrm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WtLogTrait;

    protected bool $debug = true;
    protected string $url = 'api/external/v1/organic/leads';
    protected string $crmTestUlr = 'https://click-academy-api-81.octohub.it';
    protected string $crmUlr = 'https://crmapi81.appclickacademy.it';
    public Lead $lead;

    /**
     * Create a new job instance.
     * @param Lead $lead
     * @return void
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * @param string $url
     * @return string
     */
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
            Lead::update(['response_crm' => json_encode($data)]);
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
            Lead::update(['response_crm' => json_encode($data)]);
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
        Lead::update(['response_crm' => json_encode($data)]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $auth = $this->auth();
        if($auth) {
            $this->sendLead($auth, $this->lead);
        }
    }
}
