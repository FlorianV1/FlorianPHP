@props(['profile', 'skills' => null, 'projects' => null])

<style>
    @keyframes tBlink { 0%,100%{opacity:1} 50%{opacity:0} }

    .h-fade {
        opacity: 0;
        transform: translateY(16px);
        animation: hFadeIn 0.6s ease-out forwards;
    }
    @keyframes hFadeIn { to { opacity:1; transform:translateY(0); } }

    .t-green  { color: #86efac; }
    .t-dim    { color: rgba(255,255,255,0.25); }
    .t-key    { color: #fcd34d; }
    .t-str    { color: #c4b5fd; }
    .t-bool   { color: #93c5fd; }
    .t-path   { color: #93c5fd; }
    .t-yellow { color: #fcd34d; }
    .t-comment{ color: rgba(255,255,255,0.2); }
    .t-line   { min-height:1.5em; }
    .t-row    { display:flex;align-items:baseline;gap:0; }
    .t-prompt { color:rgba(255,255,255,0.75);font-size:11px;flex-shrink:0; }
    .t-colon  { color:rgba(255,255,255,0.2);font-size:11px; }
    .t-cpath  { color:#93c5fd;font-size:11px; }
    .t-dollar { color:rgba(255,255,255,0.3);font-size:11px; }
</style>

<section id="hero" style="position:relative;overflow:hidden;background:#0b0b0d;">
<style>
    #hero { min-height: 100vh; }
    .hero-wrap { padding: 0 2.5rem; padding-top: max(5rem, calc(50vh - 250px)); padding-bottom: 6rem; }
    @media (max-width: 767px) {
        #hero { min-height: 0; }
        .hero-wrap { padding-top: 5rem; padding-bottom: 3rem; padding-left: 1.25rem; padding-right: 1.25rem; }
    }
</style>

    @if($profile)
    @php
        $heroProjects = $projects && $projects->count()
            ? $projects->take(3)->pluck('title')->toArray()
            : ['api-core', 'dashboard', 'portfolio'];
    @endphp
    <script>
        window._heroProfile = {
            name:        {{ json_encode(strtolower($profile->name ?? 'florian')) }},
            displayName: {{ json_encode($profile->name ?? 'Florian') }},
            role:        {{ json_encode($profile->role ?? 'Software Developer') }},
            location:    {{ json_encode($profile->location ?? 'Netherlands') }},
            available:   {{ json_encode($profile->status_available ?? true) }},
            projects:    {{ json_encode($heroProjects) }},
        };
    </script>

    <div class="hero-wrap" style="max-width:1152px;margin:0 auto;position:relative;">

        {{-- LEFT COLUMN --}}
        <div style="position:relative;z-index:2;width:460px;max-width:100%;">

            {{-- Role label --}}
            <div class="h-fade" style="display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;">
                <span style="display:block;width:20px;height:1px;background:#94a3b8;flex-shrink:0;"></span>
                <span style="font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:400;letter-spacing:0.14em;text-transform:uppercase;color:#94a3b8;">{{ $profile->role ?? 'Software Developer' }}</span>
            </div>

            {{-- Headline --}}
            <div class="h-fade" style="animation-delay:0.1s;margin-bottom:1.25rem;">
                <h1 style="font-family:'Syne',sans-serif;font-weight:800;line-height:0.95;letter-spacing:-0.04em;margin:0;font-size:clamp(48px,7vw,80px);">
                    <span style="display:block;font-weight:400;opacity:0.4;font-size:0.72em;letter-spacing:-0.02em;">Hi, I'm</span>
                    {{ $profile->name ?? 'Florian' }}.
                </h1>
            </div>

            {{-- Description --}}
            <div class="h-fade" style="animation-delay:0.2s;margin-bottom:2rem;">
                <p style="font-family:'JetBrains Mono',monospace;font-size:15px;font-weight:300;line-height:1.7;color:rgba(255,255,255,0.45);max-width:380px;margin:0;">
                    @if($profile->tagline)
                        {{ $profile->tagline }}
                    @else
                        I build web applications with a focus on <strong style="font-weight:500;color:#f1f5f9;">reliability</strong>, <strong style="font-weight:500;color:#f1f5f9;">performance</strong>, and clear code.
                    @endif
                </p>
            </div>

            {{-- CTA buttons --}}
            <div class="h-fade" style="animation-delay:0.3s;display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem;">
                <a href="{{ $profile->hero_cta_primary_url ?? '#projects' }}" style="font-family:'JetBrains Mono',monospace;font-size:13px;font-weight:600;padding:11px 22px;background:#e2e8f0;color:#0b0b0d;border-radius:4px;text-decoration:none;transition:background 0.2s,transform 0.15s;"
                   onmouseover="this.style.background='#f1f5f9';this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.background='#e2e8f0';this.style.transform=''">
                    {{ $profile->hero_cta_primary_label ?? 'View my work' }}
                </a>
                <a href="{{ $profile->hero_cta_secondary_url ?? '#contact' }}" style="font-family:'JetBrains Mono',monospace;font-size:13px;font-weight:400;padding:11px 22px;background:transparent;color:rgba(255,255,255,0.45);border:1px solid rgba(255,255,255,0.12);border-radius:4px;text-decoration:none;transition:border-color 0.2s,color 0.2s,transform 0.15s;"
                   onmouseover="this.style.borderColor='rgba(255,255,255,0.45)';this.style.color='rgba(255,255,255,1)';this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.borderColor='rgba(255,255,255,0.12)';this.style.color='rgba(255,255,255,0.45)';this.style.transform=''">
                    {{ $profile->hero_cta_secondary_label ?? 'Get in touch' }}
                </a>
            </div>

            {{-- Divider --}}
            <div style="width:100%;height:1px;background:rgba(255,255,255,0.07);margin-bottom:1.5rem;"></div>

            {{-- Social links --}}
            @if($profile->social_links && count($profile->social_links) > 0)
                <div class="h-fade" style="animation-delay:0.4s;display:flex;align-items:center;gap:1.5rem;margin-bottom:1.75rem;">
                    @foreach($profile->social_links as $social)
                        @php $platform = strtolower($social['platform'] ?? ''); @endphp
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:rgba(255,255,255,0.5);transition:color 0.2s;"
                           onmouseover="this.style.color='rgba(255,255,255,1)'"
                           onmouseout="this.style.color='rgba(255,255,255,0.5)'"
                           title="{{ $social['platform'] }}">
                            <span style="opacity:0.5;display:inline-flex;">
                            @if(str_contains($platform, 'github'))
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                            @elseif(str_contains($platform, 'linkedin'))
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            @elseif(str_contains($platform, 'discord'))
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057.1 18.082.114 18.105.135 18.12a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                            @else
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            @endif
                            </span>
                            <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.2);">{{ $social['platform'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Stack badges — revealed after terminal completes --}}
            <div id="hero-stack-badges" style="display:flex;flex-wrap:wrap;gap:0.5rem;opacity:0;transition:opacity 0.5s ease;">
                @if($skills && $skills->count())
                    @foreach($skills->take(10) as $skill)
                        <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.04em;padding:3px 10px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.12);border-radius:9999px;color:rgba(255,255,255,0.55);">{{ $skill->name }}</span>
                    @endforeach
                @else
                    @foreach(['PHP','Laravel','MySQL','Redis','Docker','Git','Linux','Vue.js'] as $badge)
                        <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.04em;padding:3px 10px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.12);border-radius:9999px;color:rgba(255,255,255,0.55);">{{ $badge }}</span>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- TERMINAL — absolutely positioned behind left column --}}
        <div class="hidden lg:block" aria-hidden="true" style="position:absolute;right:-40px;top:max(5rem, calc(50vh - 250px));width:58%;max-width:720px;z-index:1;pointer-events:none;user-select:none;opacity:0.45;transform:perspective(800px) rotateY(-4deg) scale(0.93);transform-origin:right top;">
            <div style="background:#0d0d10;border:1px solid rgba(255,255,255,0.12);border-radius:10px;overflow:hidden;box-shadow:0 32px 72px rgba(0,0,0,0.7);">
                {{-- Titlebar --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 14px;background:#111115;border-bottom:1px solid rgba(255,255,255,0.06);">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></span>
                        <span style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></span>
                        <span style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></span>
                    </div>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:11px;color:rgba(255,255,255,0.2);">florian@dev — ~/portfolio — zsh</span>
                    <span style="width:56px;"></span>
                </div>
                {{-- Body --}}
                <div id="terminal-body" style="padding:14px 18px 22px;min-height:320px;font-family:'JetBrains Mono',monospace;font-size:12px;line-height:1.75;color:rgba(255,255,255,0.4);">
                    <div id="terminal-lines"></div>
                    <div style="display:flex;align-items:baseline;">
                        <span id="t-prompt-user" style="color:rgba(255,255,255,0.75);font-size:11px;flex-shrink:0;">florian@dev</span><span style="color:rgba(255,255,255,0.2);font-size:11px;flex-shrink:0;">:</span><span style="color:#93c5fd;font-size:11px;flex-shrink:0;">~/portfolio</span><span style="color:rgba(255,255,255,0.3);font-size:11px;flex-shrink:0;">&nbsp;$&nbsp;</span>
                        <span id="terminal-cmd-text"></span><span id="terminal-cursor" style="display:inline-block;width:6px;height:13px;background:rgba(255,255,255,0.55);vertical-align:text-bottom;margin-left:1px;animation:tBlink 1s step-end infinite;"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @else
        <p style="font-family:'JetBrains Mono',monospace;font-size:14px;color:rgba(255,255,255,0.3);padding:8rem 2.5rem;">No profile found.</p>
    @endif
</section>

{{-- Wave divider: hero dark (#0b0b0d) → projects surface (#111114) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:80px;margin-top:-1px;background:#0b0b0d;">
    <path d="M0,40 C180,80 360,0 540,40 C720,80 900,0 1080,38 C1260,76 1380,20 1440,40 L1440,80 L0,80 Z" fill="#111114"/>
</svg>

<script>
(function () {
    const p = window._heroProfile || { name:'florian', displayName:'Florian', role:'Software Developer' };

    const CMDS = [
        {
            cmd: 'whoami',
            out: [
                '<span class="t-green">→</span> <span style="color:rgba(255,255,255,0.75);">' + p.displayName + '</span>  <span class="t-comment">// ' + p.role + '</span>',
            ]
        },
        {
            cmd: 'cat info.json',
            out: [
                '<span class="t-dim">{</span>',
                '&nbsp;&nbsp;<span class="t-key">"name"</span><span class="t-dim">:</span> <span class="t-str">"' + p.displayName + '"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"role"</span><span class="t-dim">:</span> <span class="t-str">"' + p.role + '"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"location"</span><span class="t-dim">:</span> <span class="t-str">"' + p.location + '"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"focus"</span><span class="t-dim">:</span> <span class="t-str">"web applications"</span><span class="t-dim">,</span>',
                '&nbsp;&nbsp;<span class="t-key">"available"</span><span class="t-dim">:</span> <span class="t-bool">' + p.available + '</span>',
                '<span class="t-dim">}</span>',
            ]
        },
        {
            cmd: 'ls projects/',
            out: [
                (function() {
                    const colors = ['t-green', 't-path', 't-yellow'];
                    return p.projects.map((name, i) =>
                        '<span class="' + colors[i % colors.length] + '">' + name + '/</span>'
                    ).join('&nbsp;&nbsp;&nbsp;');
                })()
            ]
        },
        {
            cmd: 'git status',
            out: [
                'On branch <span class="t-path">main</span>',
                '<span class="t-green">✓</span> nothing to commit, working tree clean',
                '<span class="t-path">↑</span> 3 commits ahead of origin/main',
            ]
        },
        {
            cmd: 'php artisan serve',
            out: [
                '',
                '&nbsp;&nbsp;<span style="background:rgba(134,239,172,0.12);color:#86efac;padding:1px 5px;border-radius:3px;font-size:10px;letter-spacing:.04em;">INFO</span>&nbsp; Server running on <span style="color:#93c5fd;text-decoration:underline;">http://localhost:8000</span>',
                '<span class="t-green">✓</span> All systems go.',
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

    function addLine(html, cls) {
        const el = document.createElement('div');
        el.className = cls || 't-line';
        el.innerHTML = html;
        linesEl.appendChild(el);
    }

    function addPromptLine(cmd) {
        addLine(
            '<span class="t-prompt">florian@dev</span><span class="t-colon">:</span><span class="t-cpath">~/portfolio</span><span class="t-dollar">&nbsp;$&nbsp;</span><span>' + cmd + '</span>',
            't-row'
        );
    }

    async function typeCmd(cmd) {
        cmdEl.textContent = '';
        cursorEl.style.display = 'inline-block';
        for (let i = 0; i < cmd.length; i++) {
            cmdEl.textContent += cmd[i];
            let d = rand(28, 48);
            if (Math.random() < 0.07) d += rand(80, 180);
            await sleep(d);
        }
    }

    async function run() {
        await sleep(600);
        for (let i = 0; i < CMDS.length; i++) {
            const { cmd, out } = CMDS[i];
            await typeCmd(cmd);
            await sleep(rand(160, 300));
            addPromptLine(cmd);
            cmdEl.textContent = '';
            for (const line of out) {
                await sleep(rand(45, 110));
                addLine(line);
            }
            if (i < CMDS.length - 1) {
                await sleep(rand(350, 600));
                addLine('');
            }
        }
        if (badgesEl) badgesEl.style.opacity = '1';
    }

    document.addEventListener('DOMContentLoaded', run);

    // Safety net: force .h-fade elements visible if animation never fires
    setTimeout(function () {
        document.querySelectorAll('.h-fade').forEach(function (el) {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }, 1500);
})();
</script>
