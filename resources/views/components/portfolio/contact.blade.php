@props(['profile'])

<section id="contact" class="py-24 px-6 bg-surface">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-3xl font-bold mb-4 text-text-primary">Get in touch</h2>
        <p class="text-text-secondary mb-8">
            If you want to talk about work, collaboration, or just an idea, send me a message.
        </p>

        @if(session('contact_success'))
            <div class="mb-8 px-5 py-4 rounded-xl border border-green-500/20 bg-green-500/10 text-green-400 text-sm">
                Message sent! I'll get back to you soon.
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 px-5 py-4 rounded-xl border border-red-500/20 bg-red-500/10 text-red-400 text-sm">
                <ul class="space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless(session('contact_success'))
        <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-text-secondary mb-2">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full px-4 py-3 bg-app-bg border {{ $errors->has('name') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl text-text-primary focus:outline-none focus:border-accent transition-colors"
                    required
                >
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-text-secondary mb-2">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-3 bg-app-bg border {{ $errors->has('email') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl text-text-primary focus:outline-none focus:border-accent transition-colors"
                    required
                >
            </div>
            <div>
                <label for="message" class="block text-sm font-medium text-text-secondary mb-2">Message</label>
                <textarea
                    id="message"
                    name="message"
                    rows="5"
                    class="w-full px-4 py-3 bg-app-bg border {{ $errors->has('message') ? 'border-red-500/50' : 'border-white/10' }} rounded-xl text-text-primary focus:outline-none focus:border-accent transition-colors resize-none"
                    required
                >{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="w-full md:w-auto px-8 py-3 bg-accent hover:bg-accent-hover text-white font-medium rounded-xl transition-colors">
                Send Message
            </button>
        </form>
        @endunless

        @if($profile && $profile->email)
            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-text-muted text-sm">
                    Or email me directly at <a href="mailto:{{ $profile->email }}" class="text-accent hover:text-accent-hover transition-colors">{{ $profile->email }}</a>
                </p>
            </div>
        @endif
    </div>
</section>
