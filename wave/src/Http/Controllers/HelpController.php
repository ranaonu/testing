<?php

namespace Wave\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Wave\Post;
use Wave\ContactUs;
use Wave\Claim;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Wave\Notifications\ContactEmail;
use Wave\Notifications\ClaimEmail;
use Wave\Shipping;
use Illuminate\Support\Facades\Notification;
use Wave\Countries;


class HelpController extends \App\Http\Controllers\Controller
{
    public function contactUs(){
        $countries = Countries::orderBy('country_name', 'ASC')->get();
        return view('theme::help.contact_us',['countries'=>$countries]);
    }

    public function contactUsSave(Request $request){
        $contact = ContactUs::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'message'=>$request->message,
            'address'=>$request->address,
            'country'=>$request->country,
            'state'=>$request->state,
            'city'=>$request->city,
            'zip'=>$request->zip,
            'phone'=>$request->phone,
        ]);
        Notification::route('mail', 'info@zionshipping.com')->notify(new ContactEmail($contact));
        return redirect(route('wave.contactUsThank'));
    }

    public function contactUsThank(Request $request){
        return view('theme::help.contact_thanks');
    }

    public function fileClaim(){
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        return view('theme::help.file_a_claim');
    }

    public function FileClaimSave(Request $request){
        $claim = Claim::where('auth_code',$request->auth_code)->first();
        
        if(isset($claim->auth_code)){
            $claim->user_id = auth()->user()->id;
            $claim->status = 'Pending';
            $claim->save();
            return view('theme::help.file_a_claim_step2',['claim'=>$claim]);
        }else{
            return redirect()->back()->withInput()->withErrors(['auth_code'=>'Authorization code is invalid']);
        }
    }

    public function FileClaimSave2(Request $request){
        $shippData = Shipping::select('request')->where('tracking_number', $request->shipment_number)->first();
        if($shippData){
            $oldclaimcheck = Claim::where('shipment_number',$request->shipment_number)->where('auth_code','<>',$request->auth_code)->first();
            if(!isset($oldclaimcheck->id)){
                $claim = Claim::where('auth_code',$request->auth_code)->first();
                $claim->description = $request->description;
                $claim->shipment_number = $request->shipment_number;
                $claim->claim_issue = $request->claim_issue;
            
                // $files = $request->file('documents');
                // $filename = $request->docs->getClientOriginalName();
                // if($request->hasFile('docs'))
                // {
                //     $path = 'claims/' . $filename;
                //     Storage::disk(config('voyager.storage.disk'))->put($path, file_get_contents($request->docs));
                //     $claim->documents = $path;
                // }
                $claim->save();
                //Notification::route('mail', 'info@zionshipping.com')->notify(new ClaimEmail($claim));
                return view('theme::help.claim_submit_success',['claim'=>$claim]);
            }else{
                return redirect()->back()->withInput()->withErrors(['shipment_number'=>'There is already a claim on this shipment number.']);
            }
        }else{
            return redirect()->back()->withInput()->withErrors(['shipment_number'=>'Shipment number is invalid.']);
        }
        
    }
    
}
