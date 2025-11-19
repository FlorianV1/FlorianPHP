<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\NowItem;
use App\Models\Project;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\Settings;

class PortfolioController extends Controller
{
    public function index()
    {
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
}
