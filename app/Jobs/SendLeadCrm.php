<?php

namespace App\Jobs;

use App\Http\Support\WithLogTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLeadCrm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WithLogTrait;

    protected bool $debug = true;
    protected string $crmTestUlr = 'https://click-academy-api-81.octohub.it';
    protected string $crmUlr = 'https://crmapi81.appclickacademy.it';
    public array $data;
    public Client $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->client = new Client();
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
                'client_id' => 'client_id_supplied_by_click',
                'client_secret' => 'client_secret_supplied_by_click'
            ]
        ]);
        return $response->getBody();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     */
    public function handle()
    {
        $client = new Client();
        $url = 'api/external/v1/' . $this->data['provider'] . '/leads';
        $auth = $this->auth();

        $response = $client->post(
            uri: $this->getUrl($url),
            options: [
                'headers' => [
                    'User-Agent' => 'middleware3-abtg/1.0',
                    'Authorization' => 'Bearer '. $auth['token'],
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'name' => $this->data['name'],
                    'surname' => $this->data['surname'],
                    'master' => $this->data['master'],
                    'phone' => $this->data['phone'],
                    'email' => $this->data['email'],
                    'region' => $this->data['region'],
                    'city' => $this->data['city'],
                    'province' => $this->data['province'],
                ]
            ]
        );
    }
}
