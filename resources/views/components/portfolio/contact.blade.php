@props(['profile'])

<section id="contact" class="contact">
    <div class="pf-container pf-container--narrow-mobile">
        <div class="contact-grid">

            {{-- Left --}}
            <div>
                <div class="section-head contact__head">
                    <h2 class="section-head__title">Get in touch</h2>
                    <span class="section-head__slug">/ contact</span>
                </div>

                <p class="contact__intro">
                    {{ $profile->contact_intro ?? 'If you want to talk about work, collaboration, or just an idea — I\'m all ears.' }}
                </p>

                {{-- Direct email --}}
                @if($profile && $profile->email)
                    <div class="contact__direct">
                        <div class="contact__label">Or email directly</div>
                        <a href="mailto:{{ $profile->email }}" class="contact__email">
                            {{ $profile->email }}
                        </a>
                    </div>
                @endif

                {{-- Divider --}}
                <div class="contact__rule"></div>

                {{-- Social links --}}
                @if($profile && $profile->social_links)
                    <div class="contact__socials">
                        @foreach($profile->social_links as $social)
                            @php $platform = strtolower($social['platform'] ?? ''); @endphp
                            @if(str_contains($platform, 'github') || str_contains($platform, 'discord'))
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="contact__social">
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
            <div class="contact__card">

                @if(session('contact_success'))
                    <div class="contact__alert contact__alert--success">
                        Message sent! I'll get back to you soon.
                    </div>
                @endif

                @if($errors->any())
                    <div class="contact__alert contact__alert--error">
                        <ul class="contact__alert-list">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @unless(session('contact_success'))
                <form action="{{ route('contact.submit') }}" method="POST" class="contact__form">
                    @csrf

                    {{-- Anti-spam honeypot: hidden from humans, bots fill it in.
                         Not display:none (some bots skip those) — pushed off-screen,
                         removed from the accessibility tree, and unreachable by
                         keyboard, so no real visitor can land in it by accident. --}}
                    <div class="contact__honeypot" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Anti-spam time-trap: when the form was rendered. --}}
                    <input type="hidden" name="form_ts" value="{{ time() }}">

                    <div>
                        <label for="name" class="contact__field-label">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="contact__input @error('name') contact__input--invalid @enderror"
                               placeholder="">
                    </div>

                    <div>
                        <label for="email" class="contact__field-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="contact__input @error('email') contact__input--invalid @enderror">
                    </div>

                    <div>
                        <label for="message" class="contact__field-label">Message</label>
                        <textarea id="message" name="message" required
                                  class="contact__input @error('message') contact__input--invalid @enderror">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <button type="submit" class="contact__submit">
                            Send message
                        </button>
                    </div>
                </form>
                @endunless
            </div>

        </div>
    </div>
</section>
