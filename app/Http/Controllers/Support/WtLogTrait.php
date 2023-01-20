<?php

namespace App\Http\Controllers\Support;
use Illuminate\Support\Facades\File;

trait WtLogTrait
{
    private int $emptyLogDay = 30;

    /**
     * @param string|null $fileName
     * @return string
     */
    private function pathLog(?string $fileName = null): string
    {
        return storage_path('app/logs' . ($fileName ? '/' . $fileName : ''));
    }


    /**
     * @param string $msg
     * @param array|null $data
     * @param string $type
     * @param string $fileName
     * @return void
     */
    protected function logDebug(
        string $msg,
        ?array $data = [],
        string $type = 'DEBUG',
        string $fileName = 'app.log'
    ): void
    {
        if($this->debug || $type === 'ERROR') {
            $this->wtLog($msg, $data, $type, $fileName);
        }
    }

    /**
     * @param string $msg
     * @param array|null $data
     * @param string $type
     * @param string $fileName
     * @return void
     */
    protected function wtLog(
        string $msg, ?array $data = [], string $type = 'LOG', string $fileName = 'app.log'): void
    {
        $msg = date('d/m/Y H:i:s') . ' [' . $type . '] - ' . $msg;
        if($data && count($data) > 0) {
            $msg .= ' - DATA: ' . json_encode($data);
        }
        $msg .= "\n";
        File::append($this->pathLog($fileName), $msg);
    }
}