<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SitemapController;

Route::get('/', [PortfolioController::class, 'index'])->name('home');
Route::get('/work/{project:slug}', [PortfolioController::class, 'caseStudy'])->name('case-study');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::post('/contact', [PortfolioController::class, 'submitContact'])->name('contact.submit')->middleware('throttle:5,1');
