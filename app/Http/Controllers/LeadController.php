<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Support\WtLogTrait;
use App\Models\Lead;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{
    use WtLogTrait;

    public string $makeUlr = 'https://hook.eu1.make.com/7npu1raoyayosku4khn4q532kwgdq7y2?course_id=';
    protected bool $debug = true;
    protected string $code = 'Ca4aA-324de-24Lf4-gpMJ3';

    public Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param Request $request
     * @return never
     */
    public function addLead(Request $request)
    {
        if($request->input('code') === $this->code){
            $this->logDebug('Request addLead', [
                'header' => $request->header(),
                'data' => $request->all()
            ]);
            $lead = $this->createLead($request);
            $auth = $this->auth();
            $this->sendLead($auth, $lead);
            $this->sendMake($lead, $request->input('course_id')  ?? 1098);
        }
        return abort(404);

    }

    /**
     * @param Request $request
     * @return Lead
     */
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
            'courseSlug' => $data['slug_corso'] ?? null,
            'course_id' => $data['course_id'] ?? null,
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

    /**
     * @return object|array|null
     */
    protected function auth(): object|array|null
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'middleware-click-academy/1.0',
                'Accept' => 'application/json',
            ])->post(env('CRM_URL') . env('CRM_AUTH_PATH'), [
                'grant_type' => 'client_credentials',
                'scope' => '*',
                'client_id' => env('CRM_CLIENT_ID'),
                'client_secret' => env('CRM_CLIENT_SECRET')
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
            'master' => 'ux-ui-design-graphic-design', //$lead->course,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'region' => $lead->region,
            'city' => $lead->city,
            'province' => $lead->province,
        ];
        try {
            $response = Http::withToken($auth->access_token)
                ->withHeaders([
                    'User-Agent' => 'middleware-click-academy/1.0',
                    'Accept' => 'application/json',
                ])->post(env('CRM_URL') . env('CRM_LEAD_PATH'), $data);
        } catch (\Exception $e) {
            $dataResponse = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ];
            $this->logDebug('Lead Exception', $dataResponse,'ERROR');
            $lead->update([
                'crm_processed_at' => Carbon::now()->toDateTimeString(),
                'response_crm' => json_encode($dataResponse)
            ]);
            return abort(500);
        }
        if($response->successful()) {
            $dataResponse = [
                'data' => $data,
                'header' => $response->headers(),
                'body' => $response->body(),
                'bodyObject' => $response->object(),
                'statusCode' => $response->status(),
                'reason' => $response->reason(),
            ];
            $this->logDebug('Send Lead Success', $dataResponse,'INFO');
            $lead->update([
                'crm_processed_at' => Carbon::now()->toDateTimeString(),
                'response_crm' => json_encode($dataResponse)
            ]);
            return $response->object();
        }

        $dataResponse = [
            'data' => $data,
            'header' => $response->headers(),
            'body' => $response->object(),
            'statusCode' => $response->status(),
            'reason' => $response->reason()
        ];
        $this->logDebug('Lead Fail', $dataResponse,'WARNING');
        $lead->update([
            'crm_processed_at' => Carbon::now()->toDateTimeString(),
            'response_crm' => json_encode($dataResponse)
        ]);
    }

    /**
     * @param Lead $lead
     * @param int $courseId
     * @return array|never|object|void
     */
    protected function sendMake(Lead $lead, int $courseId)
    {
        $data = [
            'referer-page' => $lead->referer,
            'nome' => $lead->name,
            'cognome' => $lead->surname,
            'corso' => $lead->course,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'regione' => $lead->region,
            //'city' => $lead->city,
            'provincia' => $lead->province,
            'acceptance-970' => 1
        ];
        try {
            $response = Http::withHeaders([
                    'User-Agent' => 'middleware-abtg/1.0',
                    'Accept' => 'application/json',
                ])
                ->withBody(json_encode($data), 'application/json')
                ->post($this->makeUlr . $courseId);
        } catch (\Exception $e) {
            $dataResponse = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'trace' => $e->getTrace()
            ];
            $this->logDebug('Make Exception', $dataResponse,'ERROR');
            $lead->update([
                'make_processed_at' => Carbon::now()->toDateTimeString(),
                'response_make' => json_encode($dataResponse)
            ]);
            return abort(500);
        }

        if($response && $response->successful()) {
            $dataResponse = [
                'data' => $data,
                'header' => $response->headers(),
                'body' => $response->body(),
                'bodyObject' => $response->object(),
                'statusCode' => $response->status(),
                'reason' => $response->reason(),
            ];
            $this->logDebug('Send Make Success', $dataResponse,'INFO');
            $lead->update([
                'make_processed_at' => Carbon::now()->toDateTimeString(),
                'response_make' => json_encode($dataResponse)
            ]);
            return $response->object();
        }

        $dataResponse = [
            'data' => $data,
            'header' => $response->headers(),
            'body' => $response->object(),
            'statusCode' => $response->status(),
            'reason' => $response->reason()
        ];
        $this->logDebug('Make Fail', $data,'WARNING');
        $lead->update([
            'make_processed_at' => Carbon::now()->toDateTimeString(),
            'response_make' => json_encode($dataResponse)
        ]);
    }
}
