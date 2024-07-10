<?php

namespace App\View\Components;

use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use Illuminate\View\Component;

class Captcha extends Component
{
    public function render()
    {
        if (extension('google_recaptcha')->status) {
            $scripts = NoCaptcha::renderJs(getLocale());
            return theme_view('components.captcha', ['scripts' => $scripts]);
        }
    }
}
