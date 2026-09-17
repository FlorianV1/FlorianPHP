<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [[
            'loc' => route('home'),
            'lastmod' => Project::posted()->max('updated_at'),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ]];

        foreach (Project::withCaseStudy()->ordered()->get() as $project) {
            $urls[] = [
                'loc' => route('case-study', $project),
                'lastmod' => $project->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
