<?php

namespace App\Http\Controllers;

use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\NowItem;
use App\Models\PageView;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Settings;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $this->trackPageView($request);

        $profile     = Profile::first();
        $nowItems    = NowItem::active()->ordered()->get();
        $projects    = Project::ongoing()->ordered()->get();
        $experiences = Experience::active()->ordered()->get();
        $skills      = Skill::active()->ordered()->get();

        // THEME / COLORS
        $colors = Settings::get('custom_colors', [
            'app_bg'         => '#0E0E10',
            'surface'        => '#111418',
            'accent'         => '#4A9FFF',
            'accent_hover'   => '#2D7CE8',
            'text_primary'   => '#E7EAF0',
            'text_secondary' => '#A8ACB3',
            'text_muted'     => '#6F737A',
        ]);

        $overlay           = Settings::get('overlay', 'none');
        $overlayIntensity  = Settings::get('overlay_intensity', 50);

        // SECTIONS ORDER – if nothing saved yet, use a default
        $sectionsOrder = Settings::get('sections_order', null);
        if (! is_array($sectionsOrder) || empty($sectionsOrder)) {
            $sectionsOrder = [
                ['section' => 'hero',       'enabled' => true],
                ['section' => 'now',        'enabled' => true],
                ['section' => 'projects',   'enabled' => true],
                ['section' => 'experience', 'enabled' => true],
                ['section' => 'skills',     'enabled' => true],
                ['section' => 'about',      'enabled' => true],
                ['section' => 'contact',    'enabled' => true],
            ];
        }

        // NAVBAR LINKS – fallback if no settings yet
        $navbarLinks = Settings::get('navbar_links', [
            ['label' => 'Projects',   'url' => '#projects',   'enabled' => true],
            ['label' => 'Experience', 'url' => '#experience', 'enabled' => true],
            ['label' => 'About',      'url' => '#about',      'enabled' => true],
            ['label' => 'Contact',    'url' => '#contact',    'enabled' => true],
        ]);

        return view('portfolio', [
            'profile'          => $profile,
            'nowItems'         => $nowItems,
            'projects'         => $projects,
            'experiences'      => $experiences,
            'skills'           => $skills,
            'colors'           => $colors,
            'overlay'          => $overlay,
            'overlayIntensity' => $overlayIntensity,
            'sectionsOrder'    => $sectionsOrder,
            'navbarLinks'      => $navbarLinks,
        ]);
    }

    private function trackPageView(Request $request): void
    {
        $ip = $request->ip();

        // Skip private/local IPs and common bots
        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return;
        }

        $ua = $request->userAgent() ?? '';
        if (preg_match('/bot|crawl|spider|slurp|facebookexternalhit/i', $ua)) {
            return;
        }

        PageView::create([
            'page'       => '/',
            'ip'         => $ip,
            'user_agent' => substr($ua, 0, 255),
            'referrer'   => $request->header('referer'),
        ]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Anti-spam: quarantine anything that trips a signal — store it flagged
        // (never lost, reviewable in admin) but skip the inbox notification, and
        // still show "success" so bots don't learn they were blocked.
        $isSpam = $this->looksLikeSpam($request, $validated);

        $contactMessage = ContactMessage::create($validated + ['is_spam' => $isSpam]);

        if (! $isSpam) {
            $profile = Profile::first();
            if ($profile && $profile->email) {
                Mail::to($profile->email)->send(new ContactNotification($contactMessage));
            }
        }

        return redirect()->back()->with('contact_success', true);
    }

    /**
     * Layered, no-friction spam heuristics for the contact form.
     * Real visitors never trip these; automated bots reliably do.
     */
    private function looksLikeSpam(Request $request, array $data): bool
    {
        // 1. Honeypot — a hidden field no human ever sees or fills.
        if (filled($request->input('website'))) {
            return true;
        }

        // 2. Time-trap — bots submit near-instantly. Require a few seconds
        //    on the page (and reject absurdly stale/forged timestamps).
        $renderedAt = (int) $request->input('form_ts', 0);
        $elapsed    = time() - $renderedAt;
        if ($renderedAt <= 0 || $elapsed < 3 || $elapsed > 86400) {
            return true;
        }

        // 3. Content signals — this form is for a human to reach out; real
        //    messages almost never carry links. Spam here always does.
        $message = (string) ($data['message'] ?? '');
        $name    = (string) ($data['name'] ?? '');

        $linkCount = preg_match_all('~https?://|www\.|\b[a-z0-9-]+\.(link|top|xyz|shop|club|online|site|casino|bet|win|loan)\b~i', $message . ' ' . $name);
        if ($linkCount >= 1) {
            return true;
        }

        // 4. Common spam keywords seen in these blasts.
        if (preg_match('~\b(jackpot|casino|crypto|viagra|cialis|\$\d[\d,]{3,}|bit\.ly|tinyurl|forex|binary option|sex|porn|escort)\b~i', $message . ' ' . $name)) {
            return true;
        }

        // 5. A name that is itself an email address is a bot tell.
        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }
}
