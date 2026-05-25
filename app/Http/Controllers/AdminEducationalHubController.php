<?php

namespace App\Http\Controllers;

use App\Models\EducationalContent;
use App\Models\EducationalVideo;

class AdminEducationalHubController extends Controller
{
    public function index()
    {
        $textCount = EducationalContent::count();
        $videoCount = EducationalVideo::count();

        return view('admin.educational.index', compact('textCount', 'videoCount'));
    }
}
