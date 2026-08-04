<?php

namespace Modules\TripManagement\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; // Add this for file and directory checks
use Illuminate\Support\Facades\Validator;




use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
       
        // Custom error messages for validation
    $messages = [
        'file.required' => 'يجب رفع ملف صوتي.',
        'file.mimes' => 'صيغة الملف يجب أن تكون mp3 أو wav أو ogg أو aac.',
        'file.max' => 'أقصى حجم للملف هو 10 ميجابايت.',
    ];

    // Validation
    $validator = Validator::make($request->all(), [
        'file' => 'required|file|mimes:mp3,wav,ogg,aac|max:10240', // 10MB
    ], $messages);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
      
    //  dd($request->file('file'));
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $directory = storage_path('app/public/uploads/audio');

        // Create directory if not exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0775, true);
        }

        // Store file
        $path = $file->storeAs('public/uploads/audio', $filename);

        // Generate full URL
        $fileUrl = Storage::url($path); // e.g. /storage/uploads/audio/filename.mp3
        $fullUrl = asset($fileUrl);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'url' => $fullUrl,
        ]);
    } catch (\Exception $e) {
        Log::error('Audio upload failed: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء رفع الملف. حاول مرة أخرى لاحقاً.',
           'error' =>$e->getMessage(),
        ], 500);
    }
    }
}
