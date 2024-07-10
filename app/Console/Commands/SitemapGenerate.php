<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

class SitemapGenerate extends Command
{
    protected $signature = 'app:sitemap-generate';

    protected $description = 'Generate website sitemap.xml';

    public function handle()
    {
        $url = $this->ensureTrailingSlash(config('app.url'));
        SitemapGenerator::create($url)->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap Generated successfully');
    }

    private function ensureTrailingSlash($url)
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['path']) && !str_ends_with($parsedUrl['path'], '/')) {
            $url .= '/';
        }
        return $url;
    }
}
