<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeadCrm;
use App\Jobs\SendLeadToMake;
use App\Models\Lead;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeadController extends Controller
{
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
        //$content = $request->getContent();
        //$data = json_decode($content, true);
        /*
        Lead::create([
            'source' => $request->ip(),
            'referer' => $data['referer-page'],
            'type' => $data['type'],
            'name' => $data['nome'],
            'surname' => $data['cognome'],
            'region' => $data['regione'],
            'state' => $data['provincia'],
            'country' => $data['IT'],
            'phone' => $data['telefono'],
            'email' => $data['email'],
            'course' => $data['corso'],
            'accept970_at' => Carbon::now()->toDateTimeString(),
        ]);
        */
        dd($this->auth());
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

    protected function auth()
    {
        $response = $this->client->post($this->getUrl('api/external/v1/oauth/token'), [
            'headers' => [
                'User-Agent' => 'middleware3-click-accademy/1.0',
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope' => '*',
                'client_id' => '98433ce6-4d8e-458f-87a9-83a472667196',
                'client_secret' => 'qVhNlyr0q49zAa5WuzkY28cu3dec7yOnFyowMaN4'
            ]
        ]);
        return $response->getBody();
    }
}
