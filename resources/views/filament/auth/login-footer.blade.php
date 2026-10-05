<div class="alumni-auth-footer">

    {{-- Alternative Sign In Methods --}}
    <div class="mb-4">
        <div class="relative mb-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>

            <div class="relative flex justify-center">
                <span
                    class="rounded-full px-1 text-xs font-bold text-gray-600"
                    style="background: rgb(255, 255, 255);"
                >
                    or sign in with
                </span>
            </div>
        </div>


        {{-- Passkey + Google --}}
        <div class="mt-4 grid grid-cols-2 gap-3">

            {{-- Passkey --}}
            <x-authenticate-passkey>
                <button
                    type="button"
                    class="passkey-login-btn group inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:border-gray-400 hover:bg-gray-50 hover:shadow-md active:scale-[0.97]"
                >
                    <svg
                        class="h-4 w-4 shrink-0 text-gray-500 transition-colors duration-200 group-hover:text-[#0f3089]"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.75"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"
                        />
                    </svg>

                    <span class="truncate">Passkey</span>
                </button>
            </x-authenticate-passkey>


            {{-- Google --}}
            <a
                href="{{ route('auth.google.redirect') }}"
                class="group inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:border-gray-400 hover:bg-gray-50 hover:shadow-md active:scale-[0.97]"
            >
                <svg
                    class="h-4 w-4 shrink-0"
                    viewBox="0 0 24 24"
                >
                    <path
                        fill="#4285F4"
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                    />
                    <path
                        fill="#34A853"
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                    />
                    <path
                        fill="#FBBC05"
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                    />
                    <path
                        fill="#EA4335"
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                    />
                </svg>

                <span class="truncate">Google</span>
            </a>

        </div>
    </div>


    {{-- Forgot Password 
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

            <span>Forgot password?</span>
        </a>
    @endif
    --}}

    {{-- Registration --}}
    @if (filament()->hasRegistration())
        <p class="mt-5 text-center text-sm text-gray-600">
            Don't have an account?
            <a
                href="{{ filament()->getRegistrationUrl() }}"
                class="font-semibold text-primary-700 underline hover:text-primary-600"
            >
                Sign up
            </a>
        </p>
    @endif

</div>
