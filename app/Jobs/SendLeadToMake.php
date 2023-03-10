<?php

namespace App\Jobs;

use App\Http\Controllers\Support\WtLogTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendLeadToMake implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WtLogTrait;

    public string $makeUlr = 'https://hook.eu1.make.com/7npu1raoyayosku4khn4q532kwgdq7y2?course_id=1098';
    /*
    {
        "nome": "??????",
        "cognome": "??????",
        "tipologia": "??????",
        "regione": "??????",
        "provincia": "??????",
        "telefono": "??????",
        "email": "??????",
        "corso": "??????",
        "acceptance-970": "??????",
        "referer-page": "??????"
    }

     */
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dataToSend = [
            'nome' => $this->data['nome'],
            'cognome' => $this->data['cognome'],
            'tipologia' => $this->data['tipologia'],
            'regione' => $this->data['regione'],
            'provincia' => $this->data['provincia'],
            'telefono' => $this->data['telefono'],
            'email' => $this->data['email'],
            'corso' => $this->data['corso'],
            'acceptance-970' => $this->data['acceptance-970'],
            'recaptcha' => $this->data['recaptcha'],
            'referer-page' => $this->data['referer-page'],
        ];

        Http::withBody(json_encode($dataToSend), 'application/json')
            ->post($this->makeUlr);
    }
}
