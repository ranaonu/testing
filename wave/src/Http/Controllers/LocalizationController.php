<?php

namespace Wave\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Validator;
use Wave\KeyValue;
use Wave\ApiKey;
use TCG\Voyager\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Wave\Announcement;
use App;

class LocalizationController extends \App\Http\Controllers\Controller
{
    public function index(Request $request)
    {
        App::setLocale($request->lang);
        session()->put('locale', $request->lang);

        if(setting('auth.dashboard_redirect', true) != "null"){
    		if(!\Auth::guest()){
    			return redirect('dashboard');
    		}
    	}

        $seo = [

            'title'         => setting('site.title', 'Laravel Wave'),
            'description'   => setting('site.description', 'Software as a Service Starter Kit'),
            'image'         => url('/og_image.png'),
            'type'          => 'website'

        ];

        $announcements = Announcement::orderBy('id', 'desc')->take(3)->get();

        return view('theme::home', compact('seo', 'announcements'));  
    }
}