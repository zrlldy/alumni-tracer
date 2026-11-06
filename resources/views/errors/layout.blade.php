<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>@yield('code') | @yield('title')</title>
    <script>
        (() => {
            let theme = 'system';
            try {
                theme = localStorage.getItem('theme') || 'system';
            } catch (error) {
                // Use the system preference when browser storage is unavailable.
            }
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
            const applyTheme = () => {
                document.documentElement.classList.toggle(
                    'dark',
                    theme === 'dark' || (theme !== 'light' && systemTheme.matches),
                );
            };
            applyTheme();
            systemTheme.addEventListener('change', applyTheme);
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="m-0 grid min-h-svh place-items-center bg-[color:var(--alumni-light-bg,#f7f9fb)] px-[18px] py-8 font-sans text-[color:var(--alumni-light-text,#003049)] [.dark_&]:text-[color:var(--alumni-dark-text,#f8fafc)] [color-scheme:light] [.dark_&]:bg-[color:var(--alumni-dark-bg,#071923)] [.dark_&]:[color-scheme:dark] [background-image:radial-gradient(ellipse_at_12%_8%,rgba(252,191,73,0.16),transparent_46%),radial-gradient(ellipse_at_92%_92%,rgba(214,40,40,0.07),transparent_40%)]">
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 bg-[size:32px_32px] [background-image:linear-gradient(rgba(0,48,73,0.025)_1px,transparent_1px),linear-gradient(90deg,rgba(0,48,73,0.025)_1px,transparent_1px)] [.dark_&]:[background-image:linear-gradient(rgba(255,255,255,0.025)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.025)_1px,transparent_1px)]"></div>

    <main class="relative w-full max-w-[510px]" aria-labelledby="error-title">
        <div class="overflow-hidden rounded-[26px] border border-[color:var(--alumni-light-border,rgba(0,48,73,0.12))] [.dark_&]:border-[color:var(--alumni-dark-border,rgba(255,255,255,0.10))] bg-[color:var(--alumni-light-card,rgba(255,255,255,0.94))] shadow-[0_25px_70px_rgba(0,48,73,0.08),0_8px_30px_rgba(0,48,73,0.05)] backdrop-blur-[18px] [.dark_&]:bg-[color:var(--alumni-dark-card,rgba(10,30,42,0.94))] [.dark_&]:shadow-[0_25px_70px_rgba(0,0,0,0.30),0_8px_30px_rgba(0,0,0,0.20)] max-[420px]:rounded-[22px]">
            <div aria-hidden="true" class="h-[5px] bg-[linear-gradient(90deg,var(--alumni-navy,#003049)_0%_55%,var(--alumni-red,#D62828)_55%_70%,var(--alumni-orange,#F77F00)_70%_85%,var(--alumni-gold,#FCBF49)_85%_100%)]"></div>
            <div class="px-[38px] pt-[34px] pb-9 max-[420px]:px-6 max-[420px]:py-7">
                <p class="m-0 mb-8 flex items-center gap-[9px] text-[11px] font-bold leading-normal tracking-[0.13em] uppercase text-[color:var(--alumni-light-muted,#64748b)] [.dark_&]:text-[color:var(--alumni-dark-muted,#94a3b8)]"><span aria-hidden="true" class="size-[7px] shrink-0 rounded-full bg-[color:var(--alumni-red,#D62828)]"></span>@yield('label')</p>
                <div class="mb-6 flex items-center justify-between gap-5">
                    <p class="m-0 text-[clamp(76px,18vw,100px)] font-extrabold leading-[0.95] tracking-[-0.075em]" aria-label="Error @yield('code')">@yield('code')</p>
                    <span class="grid size-[76px] shrink-0 place-items-center rounded-[23px] border border-[#F77F00]/20 bg-[linear-gradient(145deg,rgba(252,191,73,0.22),rgba(247,127,0,0.07))] max-[420px]:size-16 max-[420px]:rounded-[20px]" aria-hidden="true">
                        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            @yield('icon')
                        </svg>
                    </span>
                </div>
                <h1 id="error-title" class="m-0 text-[clamp(25px,6vw,30px)] font-[750] leading-[1.2] tracking-[-0.035em]">@yield('title')</h1>
                <p class="m-0 mt-3.5 text-sm leading-[1.75] text-[color:var(--alumni-light-muted,#64748b)] [.dark_&]:text-[color:var(--alumni-dark-muted,#94a3b8)]">
                    @yield('message')
                </p>
                <nav class="mt-[30px] grid grid-cols-2 gap-3 max-[420px]:grid-cols-1" aria-label="Page recovery">
                    <a href="{{ url('/online') }}" class="inline-flex min-h-[50px] cursor-pointer items-center justify-center gap-2 rounded-[13px] border px-4 py-3 text-[13px] font-bold leading-[1.4] no-underline transition-[background-color,box-shadow,transform] duration-150 active:translate-y-px focus-visible:outline-[3px] focus-visible:outline-offset-4 focus-visible:outline-[#003049] [.dark_&]:focus-visible:outline-[#f8fafc] motion-reduce:transition-none border-transparent bg-[linear-gradient(135deg,var(--alumni-orange,#F77F00),var(--alumni-gold,#FCBF49))] text-[color:var(--alumni-navy,#003049)] shadow-[0_8px_20px_rgba(247,127,0,0.16)] hover:-translate-y-px hover:shadow-[0_10px_26px_rgba(247,127,0,0.26)] forced-colors:border-[ButtonText]">
                        <svg class="size-[17px] shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 10 9-7 9 7" />
                            <path d="M5 9v11h5v-6h4v6h5V9" />
                        </svg>
                        Go home
                    </a>
                    <button type="button" class="inline-flex min-h-[50px] cursor-pointer items-center justify-center gap-2 rounded-[13px] border px-4 py-3 text-[13px] font-bold leading-[1.4] no-underline transition-[background-color,box-shadow,transform] duration-150 active:translate-y-px focus-visible:outline-[3px] focus-visible:outline-offset-4 focus-visible:outline-[#003049] [.dark_&]:focus-visible:outline-[#f8fafc] motion-reduce:transition-none border-[color:var(--alumni-light-border,rgba(0,48,73,0.12))] [.dark_&]:border-[color:var(--alumni-dark-border,rgba(255,255,255,0.10))] text-[color:var(--alumni-light-text,#003049)] [.dark_&]:text-[color:var(--alumni-dark-text,#f8fafc)] bg-transparent hover:bg-[#F77F00]/[0.08] [&[hidden]]:hidden" id="error-back" hidden>
                        <svg class="size-[17px] shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 6-6 6 6 6M4 12h16" />
                        </svg>
                        Go back
                    </button>
                </nav>
            </div>
            <footer class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2.5 border-t border-[color:var(--alumni-light-border,rgba(0,48,73,0.12))] [.dark_&]:border-[color:var(--alumni-dark-border,rgba(255,255,255,0.10))] px-[38px] py-[17px] text-[11px] leading-normal text-[color:var(--alumni-light-muted,#64748b)] [.dark_&]:text-[color:var(--alumni-dark-muted,#94a3b8)] max-[420px]:px-6 max-[420px]:py-4">
                <span class="font-mono tracking-[0.04em]">HTTP @yield('code') / @yield('status')</span>
                <time datetime="{{ now()->toIso8601String() }}">
                    {{ now()->timezone('Asia/Manila')->format('d M Y · h:i A') }} PHT
                </time>
            </footer>
        </div>
    </main>
    <script>
        (() => {
            const backButton = document.getElementById('error-back');
            if (window.history.length > 1) {
                backButton.hidden = false;
                backButton.addEventListener('click', () => window.history.back());
            }
        })();
    </script>
</body>
</html>
