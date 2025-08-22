<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApiVersion extends Command
{
    protected $signature = 'api-version';
    protected $description = 'Muestra la versión actual de la API definida en config/app.php';

    public function handle()
    {
        $version = config('site.software_version');
        $this->info('API version: ' . $version);
    }
}
