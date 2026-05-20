@props(['profile'])

<section id="contact" style="background:#111114;padding:5rem 0 6rem;overflow:hidden;">
    <div style="max-width:1152px;margin:0 auto;padding:0 2.5rem;">
        <div class="contact-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start;">

            {{-- Left --}}
            <div>
                <div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:1.75rem;">
                    <h2 style="font-family:'Syne',sans-serif;font-weight:800;font-size:clamp(36px,5vw,56px);letter-spacing:-0.03em;margin:0;color:#f1f5f9;">Get in touch</h2>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.18);margin-bottom:6px;letter-spacing:0.04em;">/ contact</span>
                </div>

                <p style="font-family:'JetBrains Mono',monospace;font-size:14px;color:rgba(255,255,255,0.45);line-height:1.75;margin:0 0 2rem;">
                    {{ $profile->contact_intro ?? 'If you want to talk about work, collaboration, or just an idea — I\'m all ears.' }}
                </p>

                {{-- Direct email --}}
                @if($profile && $profile->email)
                    <div style="margin-bottom:1.5rem;">
                        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.25);margin-bottom:0.5rem;">Or email directly</div>
                        <a href="mailto:{{ $profile->email }}"
                           style="font-family:'JetBrains Mono',monospace;font-size:14px;color:rgba(255,255,255,0.5);text-decoration:none;transition:color 0.2s;"
                           onmouseover="this.style.color='rgba(255,255,255,1)'"
                           onmouseout="this.style.color='rgba(255,255,255,0.5)'">
                            {{ $profile->email }}
                        </a>
                    </div>
                @endif

                {{-- Divider --}}
                <div style="height:1px;background:rgba(255,255,255,0.07);margin-bottom:1.5rem;"></div>

                {{-- Social links --}}
                @if($profile && $profile->social_links)
                    <div style="display:flex;flex-direction:column;gap:0.75rem;">
                        @foreach($profile->social_links as $social)
                            @php $platform = strtolower($social['platform'] ?? ''); @endphp
                            @if(str_contains($platform, 'github') || str_contains($platform, 'discord'))
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                   style="display:inline-flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:12px;color:rgba(255,255,255,0.35);text-decoration:none;transition:color 0.2s;width:fit-content;"
                                   onmouseover="this.style.color='rgba(255,255,255,1)'"
                                   onmouseout="this.style.color='rgba(255,255,255,0.35)'">
                                    @if(str_contains($platform, 'github'))
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                                        GitHub
                                    @else
                                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057.1 18.082.114 18.105.135 18.12a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                                        Discord
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: form card --}}
            <div style="background:var(--bg);border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:2rem;display:flex;flex-direction:column;gap:1.25rem;">

                @if(session('contact_success'))
                    <div style="padding:1rem 1.25rem;border-radius:8px;border:1px solid rgba(134,239,172,0.2);background:rgba(134,239,172,0.08);font-family:'JetBrains Mono',monospace;font-size:13px;color:#86efac;">
                        Message sent! I'll get back to you soon.
                    </div>
                @endif

                @if($errors->any())
                    <div style="padding:1rem 1.25rem;border-radius:8px;border:1px solid rgba(239,68,68,0.2);background:rgba(239,68,68,0.08);font-family:'JetBrains Mono',monospace;font-size:13px;color:#fca5a5;">
                        <ul style="margin:0;padding:0;list-style:disc inside;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @unless(session('contact_success'))
                <form action="{{ route('contact.submit') }}" method="POST" style="display:contents;">
                    @csrf

                    <div>
                        <label for="name" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:0.5rem;">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               style="width:100%;background:var(--bg2);border:1px solid {{ $errors->has('name') ? 'rgba(239,68,68,0.4)' : 'rgba(255,255,255,0.1)' }};border-radius:8px;padding:0.65rem 0.875rem;font-family:'JetBrains Mono',monospace;font-size:13px;color:#f1f5f9;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                               placeholder=""
                               onfocus="this.style.borderColor='rgba(255,255,255,0.35)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>

                    <div>
                        <label for="email" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:0.5rem;">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               style="width:100%;background:var(--bg2);border:1px solid {{ $errors->has('email') ? 'rgba(239,68,68,0.4)' : 'rgba(255,255,255,0.1)' }};border-radius:8px;padding:0.65rem 0.875rem;font-family:'JetBrains Mono',monospace;font-size:13px;color:#f1f5f9;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                               onfocus="this.style.borderColor='rgba(255,255,255,0.35)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>

                    <div>
                        <label for="message" style="display:block;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:0.5rem;">Message</label>
                        <textarea id="message" name="message" required
                                  style="width:100%;background:var(--bg2);border:1px solid {{ $errors->has('message') ? 'rgba(239,68,68,0.4)' : 'rgba(255,255,255,0.1)' }};border-radius:8px;padding:0.65rem 0.875rem;font-family:'JetBrains Mono',monospace;font-size:13px;color:#f1f5f9;outline:none;transition:border-color 0.2s;min-height:120px;resize:vertical;box-sizing:border-box;"
                                  onfocus="this.style.borderColor='rgba(255,255,255,0.35)'"
                                  onblur="this.style.borderColor='rgba(255,255,255,0.1)'">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <button type="submit"
                                style="font-family:'JetBrains Mono',monospace;font-size:13px;font-weight:600;padding:12px 24px;background:#f1f5f9;color:#0b0b0d;border:none;border-radius:8px;cursor:pointer;transition:background 0.2s,transform 0.15s;"
                                onmouseover="this.style.background='#cbd5e1';this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.background='#f1f5f9';this.style.transform=''">
                            Send message
                        </button>
                    </div>
                </form>
                @endunless
            </div>

        </div>
    </div>
</section>

<style>
@media (max-width: 900px) {
    .contact-grid { grid-template-columns: 1fr !important; gap: 2.5rem !important; }
}
@media (max-width: 767px) {
    #contact > div { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
    .contact-grid { gap: 2rem !important; }
}
</style>
