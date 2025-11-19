<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\NowItem;
use App\Models\Project;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\PageView;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        // Track page view
        PageView::create([
            'page' => '/',
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
        ]);

        $profile = Profile::first();
        $nowItems = NowItem::active()->ordered()->get();
        $projects = Project::posted()->ordered()->get();
        $experiences = Experience::active()->ordered()->get();
        $skills = Skill::active()->ordered()->get();

        return view('portfolio', compact(
            'profile',
            'nowItems',
            'projects',
            'experiences',
            'skills'
        ));
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        return back()->with('success', 'Message sent successfully!');
    }
}
