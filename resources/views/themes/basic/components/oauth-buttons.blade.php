@if ($oauthProviders->count() > 0)
    <div class="login-with mt-3">
        <div class="login-with-divider">
            <span>{{ translate('Or With') }}</span>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 g-3">
            @foreach ($oauthProviders as $oauthProvider)
                <div class="col">
                    <a href="{{ route('oauth.login', $oauthProvider->alias) }}"
                        class="btn btn-social btn-md w-100 text-center">
                        @if ($oauthProvider->alias == 'facebook')
                            <svg xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision"
                                text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd"
                                clip-rule="evenodd" viewBox="0 0 509 509">
                                <g fill-rule="nonzero">
                                    <path fill="#0866FF"
                                        d="M509 254.5C509 113.94 395.06 0 254.5 0S0 113.94 0 254.5C0 373.86 82.17 474 193.02 501.51V332.27h-52.48V254.5h52.48v-33.51c0-86.63 39.2-126.78 124.24-126.78 16.13 0 43.95 3.17 55.33 6.33v70.5c-6.01-.63-16.44-.95-29.4-.95-41.73 0-57.86 15.81-57.86 56.91v27.5h83.13l-14.28 77.77h-68.85v174.87C411.35 491.92 509 384.62 509 254.5z">
                                    </path>
                                    <path fill="#fff"
                                        d="M354.18 332.27l14.28-77.77h-83.13V227c0-41.1 16.13-56.91 57.86-56.91 12.96 0 23.39.32 29.4.95v-70.5c-11.38-3.16-39.2-6.33-55.33-6.33-85.04 0-124.24 40.16-124.24 126.78v33.51h-52.48v77.77h52.48v169.24c19.69 4.88 40.28 7.49 61.48 7.49 10.44 0 20.72-.64 30.83-1.86V332.27h68.85z">
                                    </path>
                                </g>
                            </svg>
                        @elseif ($oauthProvider->alias == 'google')
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 326667 333333"
                                shape-rendering="geometricPrecision" text-rendering="geometricPrecision"
                                image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd">
                                <path
                                    d="M326667 170370c0-13704-1112-23704-3518-34074H166667v61851h91851c-1851 15371-11851 38519-34074 54074l-311 2071 49476 38329 3428 342c31481-29074 49630-71852 49630-122593m0 0z"
                                    fill="#4285f4"></path>
                                <path
                                    d="M166667 333333c44999 0 82776-14815 110370-40370l-52593-40742c-14074 9815-32963 16667-57777 16667-44074 0-81481-29073-94816-69258l-1954 166-51447 39815-673 1870c27407 54444 83704 91852 148890 91852z"
                                    fill="#34a853"></path>
                                <path
                                    d="M71851 199630c-3518-10370-5555-21482-5555-32963 0-11482 2036-22593 5370-32963l-93-2209-52091-40455-1704 811C6482 114444 1 139814 1 166666s6482 52221 17777 74814l54074-41851m0 0z"
                                    fill="#fbbc04"></path>
                                <path
                                    d="M166667 64444c31296 0 52406 13519 64444 24816l47037-45926C249260 16482 211666 1 166667 1 101481 1 45185 37408 17777 91852l53889 41853c13520-40185 50927-69260 95001-69260m0 0z"
                                    fill="#ea4335"></path>
                            </svg>
                        @endif
                        {{ translate($oauthProvider->name) }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
