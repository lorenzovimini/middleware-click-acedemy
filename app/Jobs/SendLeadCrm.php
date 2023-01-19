<?php

namespace App\Jobs;

use App\Http\Controllers\Support\WtLogTrait;
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
    protected string $crmTestUlr = 'https://click-academy-api-81.octohub.it';
    protected string $crmUlr = 'https://crmapi81.appclickacademy.it';
    public array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $url = 'api/external/v1/organic/leads';
        $auth = $this->auth();
        if($auth) {
            $response = Http::withToken($auth->access_token)
                ->withHeaders([
                    'User-Agent' => 'middleware3-abtg/1.0',
                    'Accept' => 'application/json',
                ])->post($this->getUrl($url), [
                    'name' => $this->data['nome'],
                    'surname' => $this->data['cognome'],
                    'master' => $this->data['corso'],
                    'phone' => $this->data['telefono'],
                    'email' => $this->data['email'],
                    'region' => $this->data['regione'],
                    'city' => $this->data['city'],
                    'province' => $this->data['provincia'],
                ]);
        }
    }
}
