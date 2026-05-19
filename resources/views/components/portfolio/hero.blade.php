@props(['profile'])

<section id="hero" class="relative min-h-screen flex items-start overflow-x-hidden px-6 pt-96 pb-25">

    @if($profile)
    {{-- Inject profile data for terminal --}}
    <script>
        window._heroProfile = {
            name: {{ json_encode(strtolower($profile->name ?? 'florian')) }},
            displayName: {{ json_encode($profile->name ?? 'Florian') }},
            role: {{ json_encode($profile->role ?? 'Software Developer') }},
        };
    </script>

    <div class="w-full max-w-6xl mx-auto relative">

        {{-- Left content --}}
        <div class="relative z-10 max-w-[540px]">

            {{-- Role label + rule --}}
            <div class="hero-fade flex items-center gap-4 mb-7">
                <span class="font-mono text-xs tracking-[0.2em] uppercase text-white/30">
                    {{ $profile->role }}
                </span>
                <span class="block w-10 h-px bg-white/15"></span>
            </div>

            {{-- Headline --}}
            <div class="hero-fade" style="animation-delay:.1s">
                <h1 class="font-display font-black text-white leading-none mb-8"
                    style="font-size: clamp(52px, 5.5vw, 80px); line-height: 1.02;">
                    Hi, I'm <br>{{ $profile->name }}.
                </h1>
            </div>

            {{-- Description --}}
            <div class="hero-fade" style="animation-delay:.22s">
                <p class="font-mono text-sm leading-relaxed text-white/40 mb-8 max-w-[440px]">
                    {{ $profile->tagline }}
                </p>
            </div>

            {{-- CTA buttons --}}
            <div class="hero-fade flex flex-wrap items-center gap-3 mb-8" style="animation-delay:.34s">
                <a href="#projects"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-black font-mono text-xs font-medium hover:bg-white/90 transition-colors rounded-sm">
                    View my work
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="#contact"
                   class="inline-flex items-center gap-2 px-5 py-2.5 border border-white/15 text-white/50 font-mono text-xs hover:border-white/35 hover:text-white/75 transition-colors rounded-sm">
                    Get in touch
                </a>
            </div>

            {{-- Social links --}}
            @if($profile->social_links && count($profile->social_links) > 0)
                <div class="hero-fade flex items-center gap-5 pt-6 border-t border-white/8 mb-6" style="animation-delay:.46s">
                    @foreach($profile->social_links as $social)
                        @php $platform = strtolower($social['platform'] ?? ''); @endphp
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="text-white/25 hover:text-white/65 transition-colors"
                           title="{{ $social['platform'] }}">
                            @if(str_contains($platform, 'github'))
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                                </svg>
                            @elseif(str_contains($platform, 'linkedin'))
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            @elseif(str_contains($platform, 'discord'))
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057.1 18.082.114 18.105.135 18.12a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                            @elseif(str_contains($platform, 'twitter') || str_contains($platform, 'x.com') || $platform === 'x')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Stack badges — revealed after terminal finishes --}}
            <div id="hero-stack-badges"
                 class="flex flex-wrap gap-2"
                 style="opacity:0; transition: opacity 0.9s ease; animation-delay:.58s">
                @foreach(['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker', 'Git', 'Linux', 'Vue.js'] as $badge)
                    <span class="font-mono text-xs px-2 py-0.5 border border-white/10 text-white/22 rounded-sm">
                        {{ $badge }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Terminal window — absolutely positioned, behind content --}}
        <div class="terminal-float hidden lg:block" aria-hidden="true">
            <div class="terminal-window">
                {{-- Titlebar --}}
                <div class="terminal-titlebar">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:#FF5F57;"></span>
                        <span class="w-2.5 h-2.5 rounded-full" style="background:#FEBC2E;"></span>
                        <span class="w-2.5 h-2.5 rounded-full" style="background:#28C840;"></span>
                    </div>
                    <span class="terminal-title-text">florian@dev — bash</span>
                    <span style="width:56px;display:inline-block;"></span>
                </div>
                {{-- Body --}}
                <div id="terminal-body" class="terminal-body">
                    <div id="terminal-lines"></div>
                    <div class="terminal-input-line">
                        <span class="terminal-prompt">florian@dev:~$&nbsp;</span><span
                            id="terminal-cmd-text"></span><span
                            class="terminal-cursor" id="terminal-cursor"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @else
        <p class="font-mono text-sm text-white/30">No profile found.</p>
    @endif
</section>

<style>
    /* Hero entrance animations */
    .hero-fade {
        opacity: 0;
        transform: translateY(18px);
        animation: heroFadeIn 0.65s ease-out forwards;
    }
    @keyframes heroFadeIn {
        to { opacity: 1; transform: translateY(0); }
    }

    /* Terminal float — absolute, pushed right, perspective tilt */
    .terminal-float {
        position: absolute;
        right: -90px;
        top: 0;
        transform: perspective(1100px) rotateY(-8deg) scale(0.91);
        transform-origin: right top;
        opacity: 0.45;
        width: 580px;
        z-index: 0;
        pointer-events: none;
        user-select: none;
    }

    /* Terminal chrome */
    .terminal-window {
        background: #0c0c0e;
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 30px 70px rgba(0,0,0,0.65), 0 0 0 1px rgba(255,255,255,0.03);
    }

    .terminal-titlebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 14px;
        background: #101012;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .terminal-title-text {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
        color: rgba(255,255,255,0.2);
    }

    .terminal-body {
        padding: 14px 18px 20px;
        min-height: 310px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        line-height: 1.75;
        color: rgba(255,255,255,0.4);
    }

    .terminal-input-line {
        display: flex;
        align-items: baseline;
    }

    .terminal-prompt {
        color: rgba(255,255,255,0.18);
        font-size: 11px;
        flex-shrink: 0;
    }

    .terminal-output-line {
        min-height: 1.5em;
        padding-left: 0;
    }

    .terminal-completed-line {
        display: flex;
        align-items: baseline;
        gap: 0;
        margin-bottom: 1px;
    }

    .terminal-cursor {
        display: inline-block;
        width: 6px;
        height: 13px;
        background: rgba(255,255,255,0.55);
        vertical-align: text-bottom;
        margin-left: 1px;
        animation: termBlink 1s step-end infinite;
    }

    @keyframes termBlink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0; }
    }

    /* Terminal output syntax colors */
    .t-path    { color: #6B9EFF; }
    .t-key     { color: #E2C08D; }
    .t-str     { color: #B48EAD; }
    .t-bool    { color: #6B9EFF; }
    .t-dim     { color: rgba(255,255,255,0.28); }
    .t-comment { color: rgba(255,255,255,0.2); }
    .t-ok      { color: #6FCF97; background: rgba(111,207,151,0.1); padding: 1px 5px; border-radius: 3px; font-size: 10px; letter-spacing:.04em; }
</style>

<script>
(function () {
    const p = window._heroProfile || { name: 'florian', displayName: 'Florian', role: 'Software Developer' };

    const COMMANDS = [
        {
            cmd: 'whoami',
            outputs: [
                '<span class="t-str">' + p.name + '</span>',
            ]
        },
        {
            cmd: 'cat info.json',
            outputs: [
                '<span class="t-dim">{</span>',
                '&nbsp;&nbsp;<span class="t-key">"name"</span><span class="t-dim">:</span> <span class="t-str">"' + p.displayName + '"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"role"</span><span class="t-dim">:</span> <span class="t-str">"' + p.role + '"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"location"</span><span class="t-dim">:</span> <span class="t-str">"Netherlands"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"available"</span><span class="t-dim">:</span> <span class="t-bool">true</span>',
                '<span class="t-dim">}</span>',
            ]
        },
        {
            cmd: 'ls projects/',
            outputs: [
                '<span class="t-path">florianphp/</span>   <span class="t-path">api-gateway/</span>   <span class="t-path">herd-tools/</span>',
                '<span class="t-path">dashboard/</span>    <span class="t-path">portfolio/</span>',
            ]
        },
        {
            cmd: 'git status',
            outputs: [
                'On branch <span class="t-path">main</span>',
                "Your branch is up to date with '<span class=\"t-str\">origin/main</span>'.",
                '',
                '<span class="t-comment">nothing to commit, working tree clean</span>',
            ]
        },
        {
            cmd: 'php artisan serve',
            outputs: [
                '',
                '&nbsp;&nbsp;&nbsp;<span class="t-ok">INFO</span>&nbsp; Server running on <span class="t-path">[http://127.0.0.1:8000]</span>.',
                '',
                '&nbsp;&nbsp;Press <span class="t-dim">Ctrl+C</span> to stop the server',
            ]
        },
    ];

    const linesEl  = document.getElementById('terminal-lines');
    const cmdEl    = document.getElementById('terminal-cmd-text');
    const cursorEl = document.getElementById('terminal-cursor');
    const badgesEl = document.getElementById('hero-stack-badges');

    if (!linesEl || !cmdEl) return;

    const sleep = ms => new Promise(r => setTimeout(r, ms));
    const rand  = (lo, hi) => Math.floor(Math.random() * (hi - lo + 1)) + lo;

    function appendCompleted(cmd) {
        const el = document.createElement('div');
        el.className = 'terminal-completed-line';
        el.innerHTML = '<span class="terminal-prompt">florian@dev:~$&nbsp;</span><span>' + cmd + '</span>';
        linesEl.appendChild(el);
    }

    function appendOutput(html) {
        const el = document.createElement('div');
        el.className = 'terminal-output-line';
        el.innerHTML = html;
        linesEl.appendChild(el);
    }

    async function typeCmd(cmd) {
        cmdEl.textContent = '';
        cursorEl.style.display = 'inline-block';

        for (let i = 0; i < cmd.length; i++) {
            cmdEl.textContent += cmd[i];
            let d = rand(38, 92);
            if (Math.random() < 0.07) d += rand(90, 220);
            await sleep(d);
        }
    }

    async function runTerminal() {
        await sleep(700);

        for (let i = 0; i < COMMANDS.length; i++) {
            const { cmd, outputs } = COMMANDS[i];

            await typeCmd(cmd);
            await sleep(rand(180, 340));

            appendCompleted(cmd);
            cmdEl.textContent = '';

            for (const line of outputs) {
                await sleep(rand(55, 125));
                appendOutput(line);
            }

            if (i < COMMANDS.length - 1) {
                await sleep(rand(380, 650));
                appendOutput('');
            }
        }

        cursorEl.style.display = 'none';

        await sleep(350);
        if (badgesEl) badgesEl.style.opacity = '1';
    }

    document.addEventListener('DOMContentLoaded', runTerminal);
})();
</script>
