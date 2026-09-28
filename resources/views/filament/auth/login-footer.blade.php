<div class="alumni-auth-footer">

    {{-- Forgot password --}}
    @if (filament()->hasPasswordReset())
        <a
            href="{{ filament()->getRequestPasswordResetUrl() }}"
            class="alumni-auth-footer-link alumni-auth-forgot"
        >
            <span class="alumni-auth-footer-icon">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 5.25a3.75 3.75 0 1 1-6.62 2.414
                           M12 17.25h.008v.008H12v-.008Z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 19.5a9 9 0 1 1 15-6.708"
                    />
                </svg>
            </span>

            <span>
                Forgot password?
            </span>
        </a>
    @endif


    {{-- Registration --}}
    @if (filament()->hasRegistration())
        <div class="alumni-auth-register">

            <span>
                Don't have an account?
            </span>

            <a
                href="{{ filament()->getRegistrationUrl() }}"
                class="alumni-auth-footer-link"
            >
                Create an account
            </a>

        </div>
    @endif

</div>