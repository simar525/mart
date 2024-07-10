<?php

namespace App\View\Components\Reviewer;

use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use Illuminate\View\Component;

class Captcha extends Component
{
    public function render()
    {
        if (extension('google_recaptcha')->status) {
            $scripts = NoCaptcha::renderJs(getLocale());
            return view('reviewer.components.captcha', ['scripts' => $scripts]);
        }
    }
}
