<?php

namespace App\Http\Controllers\api;

use App\Models\Upload;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400',
        ]);

        $user = Auth::user();
        $file = $request->file('file');

        $fileType = $file->getClientOriginalExtension();
        $fileName = time() . '_' . $file->getClientOriginalName();

        $uploadDir = public_path('uploads');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Save directly in public/uploads
        $file->move(public_path('uploads'), $fileName);

        $filePath = 'uploads/' . $fileName; // Relative path for URL

        $upload = Upload::create([
            'user_id' => $user->id,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'files uploaded successfully',
            'data' => $upload,
            'url' => url($filePath),
        ]);
    }

    // Get all uploaded files for authenticated user
    public function list()
    {
        $user = Auth::user();

        $uploads = Upload::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($upload) {
                return [
                    'id' => $upload->id,
                    'name' => $upload->file_name,
                    'type' => $upload->file_type,
                    'url' => url($upload->file_path),
                    'uploaded_at' => $upload->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Files list fetched successfully',
            'data' => $uploads
        ]);
    }
}
