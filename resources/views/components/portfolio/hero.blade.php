@props(['profile', 'skills' => null, 'projects' => null])

@php
    use App\Support\SiteBranding;

    $fullName = SiteBranding::get('full_name');
    $prompt = SiteBranding::terminalPrompt();
    $promptPath = SiteBranding::get('terminal_path');
@endphp

<section id="hero" class="hero">

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
            prompt:      {{ json_encode($prompt) }},
            promptPath:  {{ json_encode($promptPath) }},
            projects:    {{ json_encode($heroProjects) }},
        };
    </script>

    <div class="hero__wrap">

        {{-- LEFT COLUMN --}}
        <div class="hero__col">

            {{-- Role label --}}
            <div class="h-fade hero__eyebrow">
                <span class="hero__eyebrow-rule"></span>
                <span class="hero__eyebrow-text">{{ $profile->role ?? 'Software Developer' }}</span>
            </div>

            {{-- Headline --}}
            <div class="h-fade h-fade--d1 hero__headline">
                <h1 class="hero__title">
                    <span class="hero__title-greeting">Hi, I'm</span>
                    {{ $fullName }}.
                </h1>
            </div>

            {{-- Description --}}
            <div class="h-fade h-fade--d2 hero__lede-wrap">
                <p class="hero__lede">
                    @if($profile->tagline)
                        {{ $profile->tagline }}
                    @else
                        I build web applications with a focus on <strong>reliability</strong>, <strong>performance</strong>, and clear code.
                    @endif
                </p>
            </div>

            {{-- CTA buttons --}}
            <div class="h-fade h-fade--d3 hero__ctas">
                <a href="{{ $profile->hero_cta_primary_url ?? '#projects' }}" class="hero__cta hero__cta--primary">
                    {{ $profile->hero_cta_primary_label ?? 'View my work' }}
                </a>
                <a href="{{ $profile->hero_cta_secondary_url ?? '#contact' }}" class="hero__cta hero__cta--secondary">
                    {{ $profile->hero_cta_secondary_label ?? 'Get in touch' }}
                </a>
            </div>

            {{-- Divider --}}
            <div class="hero__rule"></div>

            {{-- Social links --}}
            @if($profile->social_links && count($profile->social_links) > 0)
                <div class="h-fade h-fade--d4 hero__socials">
                    @foreach($profile->social_links as $social)
                        @php $platform = strtolower($social['platform'] ?? ''); @endphp
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="hero__social"
                           title="{{ $social['platform'] }}">
                            <span class="hero__social-icon">
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
                            <span class="hero__social-label">{{ $social['platform'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Stack badges — revealed after terminal completes --}}
            <div id="hero-stack-badges" class="hero__badges">
                @if($skills && $skills->count())
                    @foreach($skills->take(10) as $skill)
                        <span class="tag tag--lg">{{ $skill->name }}</span>
                    @endforeach
                @else
                    @foreach(['PHP','Laravel','MySQL','Redis','Docker','Git','Linux','Vue.js'] as $badge)
                        <span class="tag tag--lg">{{ $badge }}</span>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- TERMINAL — absolutely positioned behind left column --}}
        <div class="hero__terminal hidden lg:block" aria-hidden="true">
            <div class="hero__terminal-frame">
                {{-- Titlebar --}}
                <div class="hero__terminal-bar">
                    <div class="hero__terminal-dots">
                        <span class="hero__terminal-dot hero__terminal-dot--red"></span>
                        <span class="hero__terminal-dot hero__terminal-dot--amber"></span>
                        <span class="hero__terminal-dot hero__terminal-dot--green"></span>
                    </div>
                    <span class="hero__terminal-title">{{ $prompt }} — {{ $promptPath }} — zsh</span>
                    <span class="hero__terminal-spacer"></span>
                </div>
                {{-- Body --}}
                <div id="terminal-body" class="hero__terminal-body">
                    <div id="terminal-lines"></div>
                    <div class="hero__terminal-input">
                        <span id="t-prompt-user" class="t-prompt">{{ $prompt }}</span><span class="t-colon">:</span><span class="t-cpath">{{ $promptPath }}</span><span class="t-dollar">&nbsp;$&nbsp;</span>
                        <span id="terminal-cmd-text"></span><span id="terminal-cursor" class="hero__terminal-cursor"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @else
        <p class="hero__empty">No profile found.</p>
    @endif
</section>

{{-- Wave divider: hero dark (#0b0b0d) → projects surface (#111114) --}}
<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="wave wave--from-dark">
    <path d="M0,40 C180,80 360,0 540,40 C720,80 900,0 1080,38 C1260,76 1380,20 1440,40 L1440,80 L0,80 Z" fill="#111114"/>
</svg>

<script>
(function () {
    // The terminal only renders when a profile exists, so this fallback is
    // here purely so the script cannot throw. No branding in it — every
    // visible string comes from the CMS.
    const p = window._heroProfile || { name:'', displayName:'', role:'', location:'', available:false, prompt:'', promptPath:'', projects:[] };

    const CMDS = [
        {
            cmd: 'whoami',
            out: [
                '<span class="t-green">→</span> <span class="t-name">' + p.displayName + '</span>  <span class="t-comment">// ' + p.role + '</span>',
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
                '&nbsp;&nbsp;<span class="t-info">INFO</span>&nbsp; Server running on <span class="t-link">http://localhost:8000</span>',
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
            '<span class="t-prompt">' + p.prompt + '</span><span class="t-colon">:</span><span class="t-cpath">' + p.promptPath + '</span><span class="t-dollar">&nbsp;$&nbsp;</span><span>' + cmd + '</span>',
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
