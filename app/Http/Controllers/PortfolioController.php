<?php

namespace App\Http\Controllers;

use App\Jobs\ResolveCountry;
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

        $view = PageView::create([
            'page'       => '/',
            'ip'         => $ip,
            'user_agent' => substr($ua, 0, 255),
            'referrer'   => $request->header('referer'),
        ]);

        ResolveCountry::dispatch($view->id);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        $profile = Profile::first();
        if ($profile && $profile->email) {
            Mail::to($profile->email)->send(new ContactNotification($contactMessage));
        }

        return redirect()->back()->with('contact_success', true);
    }
}
