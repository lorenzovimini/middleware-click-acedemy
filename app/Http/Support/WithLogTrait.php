<?php

namespace App\Http\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

trait WithLogTrait
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
     * @param string $type
     * @param string $name
     * @return void
     */
    public function log(string $msg, string $type = 'info', string $name = 'default'): void
    {
        $fileName = $name . '-' . date('Y-m-d') . '.log';
        $content = date('Y-m-d H:i:s') . ' - ' . $type . ': ' . $msg . "\n";
        File::put($this->pathLog($fileName), $content, true);
    }

    /**
     * @param string $name
     * @return void
     */
    public function emptyOldLog(string $name = 'default'): void
    {
        $files = File::allFiles($this->pathLog());
        foreach ($files as $file){
            if(str_starts_with($file->getFilename(), $name)){
                $dateStr = substr($file->getFilename(), strlen($name));
                $date = Carbon::createFromFormat('Y-m-d', $dateStr);
                $dateEmpty = Carbon::now()->subDays($this->emptyLogDay);
                if($date->lt($dateEmpty)){
                    File::delete($this->pathLog($file->getFilename()));
                }
            }
        }
    }
}