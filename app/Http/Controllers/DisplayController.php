<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function display()
    {
        // Read JSON file
        $filePath = storage_path('app/public/converted_data.json');
        if (file_exists($filePath)) {
            $jsonData = json_decode(file_get_contents($filePath), true);
            return view('display', ['jsonData' => $jsonData]);
        } else {
            // Handle file not found
            return back()->with('error', 'JSON file not found.');
        }
    }
}
