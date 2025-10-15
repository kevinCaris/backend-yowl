<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadImageController extends Controller
{
    //

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $path=$request->file('image')->store('images','public');
        
        return  Storage::url($path);
        //return $request;
        
        // return $request;
    }
}
