<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeadCrm;
use App\Jobs\SendLeadToMake;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeadController extends Controller
{
    public function addLead(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
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
        SendLeadToMake::dispatch($data);
        SendLeadCrm::dispatch($data);
    }
}
