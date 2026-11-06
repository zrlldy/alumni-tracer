<x-filament-panels::page>
    <div class="get-started">
        <aside class="get-started-status" aria-labelledby="account-status-heading">
            <div>
                <span class="get-started-status-label"><span aria-hidden="true"></span> Account created</span>
                <h2 id="account-status-heading">Waiting for a role</h2>
                <p>Your account is ready. A system administrator needs to assign your role before you can use the alumni system.</p>
            </div>

            <div class="get-started-account">
                <p>Signed in as</p>
                <strong>{{ auth()->user()->email }}</strong>
                <p>Use this email when requesting access.</p>
            </div>

            <div class="get-started-status-note">
                <x-filament::icon icon="heroicon-o-lock-closed" class="size-5 shrink-0" />
                <p>Your role determines which pages and records you can access.</p>
            </div>
        </aside>

        <div class="get-started-guide">
            <header class="get-started-intro">
                <h2>Welcome to Alumni Tracer</h2>
                <p>A shared space for graduates and school staff to keep alumni information up to date.</p>
            </header>

            <section class="get-started-audience" aria-labelledby="audience-heading">
                <h3 id="audience-heading">Who uses this system?</h3>
                <dl>
                    <div>
                        <dt>Alumni and graduates</dt>
                        <dd>Keep your profile, employment and further education information up to date once you receive access.</dd>
                    </div>
                    <div>
                        <dt>School staff</dt>
                        <dd>Manage alumni records and tracing activities within the access assigned by an administrator.</dd>
                    </div>
                </dl>
            </section>

            <section class="get-started-steps" aria-labelledby="access-heading">
                <h3 id="access-heading">How to get access</h3>
                <ol>
                    <li>
                        <span class="get-started-step-number" aria-hidden="true">1</span>
                        <div>
                            <h4>Contact a system administrator</h4>
                            <p>Share your full name, the email shown here, and whether you are an alumnus or a staff member.</p>
                        </div>
                    </li>
                    <li>
                        <span class="get-started-step-number" aria-hidden="true">2</span>
                        <div>
                            <h4>Wait for your role to be assigned</h4>
                            <p>The administrator will review your request and assign the appropriate access. You cannot choose your own role.</p>
                        </div>
                    </li>
                    <li>
                        <span class="get-started-step-number" aria-hidden="true">3</span>
                        <div>
                            <h4>Sign in again after approval</h4>
                            <p>Once a role is assigned, this page disappears and your permitted pages become available.</p>
                        </div>
                    </li>
                </ol>
            </section>

            <section class="get-started-retention" aria-labelledby="retention-heading">
                <x-filament::icon icon="heroicon-o-clock" class="size-6 shrink-0" />
                <div>
                    <h3 id="retention-heading">Get your role assigned within 30 days</h3>
                    <p>Accounts that still have no assigned role after 30 days will be deleted. Contact a system administrator before this period ends.</p>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
