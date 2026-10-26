<section id="about" class="landing-container landing-record-section" aria-labelledby="landing-purpose-title" tabindex="-1">
    <div class="landing-section-heading">
        <h2 id="landing-purpose-title">Start with the person.<br>Keep the details together.</h2>
        <p>Contact information, academic background, and tracing updates belong to the same record. Find what you need without losing the context.</p>
    </div>

    <div id="directory-details" class="landing-record-sheet" tabindex="-1">
        <div class="landing-record-heading">
            <h3>The alumni record</h3>
            <p>What an alumni record contains</p>
        </div>
        <dl class="landing-record-fields">
            <div>
                <dt>Personal & contact</dt>
                <dd>Name and student number</dd>
                <dd>Email and phone number</dd>
                <dd>Current address</dd>
            </div>
            <div>
                <dt>Academic background</dt>
                <dd>Program or department</dd>
                <dd>Graduation date</dd>
                <dd>Education history</dd>
            </div>
            <div>
                <dt>Employment & tracing</dt>
                <dd>Employment status</dd>
                <dd>Date traced and tracer</dd>
                <dd>Remarks and employment history</dd>
            </div>
        </dl>
        <div class="landing-record-footer">
            <p>Search by name or student number. Narrow the directory by program, employment status, or graduation date.</p>
            <a href="{{ route('filament.online.auth.login') }}" class="landing-text-link">Sign in to the directory</a>
        </div>
    </div>
</section>

<section id="tracing-details" class="landing-tracing-section" aria-labelledby="landing-tracing-title" tabindex="-1">
    <div class="landing-container landing-tracing-layout">
        <div>
            <h2 id="landing-tracing-title">Keep the next<br>follow-up in view.</h2>
            <p class="landing-section-copy">Tracing is ongoing work. Record the employment status, note when the graduate was last traced, and leave context for the next person following up.</p>
        </div>
        <div class="landing-tracing-content">
            <div class="landing-status-key">
                <h3>Employment status</h3>
                <ul aria-label="Available employment statuses">
                    <li>Employed</li>
                    <li>Unemployed</li>
                    <li>Untraced</li>
                </ul>
            </div>
            <dl class="landing-tracing-fields">
                <div><dt>Date traced</dt><dd>When the record was followed up</dd></div>
                <div><dt>Traced by</dt><dd>Who carried out the follow-up</dd></div>
                <div><dt>Remarks</dt><dd>Details to carry into the next conversation</dd></div>
            </dl>
        </div>
    </div>
</section>

<section id="report-details" class="landing-container landing-report-section" aria-labelledby="landing-report-title" tabindex="-1">
    <div class="landing-section-heading">
        <h2 id="landing-report-title">Look across records.<br>Take a focused report with you.</h2>
        <p>Review outcomes in the dashboard, or use the Alumni Data Report to choose a group of graduates and download their records.</p>
    </div>
    <div class="landing-report-workflow">
        <div class="landing-report-filters">
            <h3>Choose your report filters</h3>
            <ul>
                <li>Graduation year</li>
                <li>Program or department</li>
                <li>Employment status</li>
            </ul>
        </div>
        <div class="landing-report-output">
            <span class="landing-file-type">.xlsx</span>
            <div>
                <h3>Alumni Data Report</h3>
                <p>Alumni details, tracing information, and education and employment histories in one workbook.</p>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="landing-workflow-section" aria-labelledby="landing-how-title" tabindex="-1">
    <div class="landing-container landing-workflow-layout">
        <div>
            <h2 id="landing-how-title">A working routine<br>for your alumni team.</h2>
            <a href="{{ route('filament.online.auth.login') }}" class="landing-text-link">Sign in to get started</a>
        </div>
        <ol class="landing-steps">
            <li>
                <h3>Bring the records in</h3>
                <p>Create an individual record or import your existing directory with the Excel template.</p>
            </li>
            <li>
                <h3>Maintain the details</h3>
                <p>Update contact information, employment status, tracing dates, and follow-up notes.</p>
            </li>
            <li>
                <h3>Review and report</h3>
                <p>Explore dashboard insights, filter alumni records, and download the report you need.</p>
            </li>
        </ol>
    </div>
</section>
