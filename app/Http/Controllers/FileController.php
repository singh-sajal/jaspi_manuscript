<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{

    public function __invoke(Request $request)
    {
        $fileUrl = $request->fileUrl;
        $frameOption = $request->frameOption;
        if ($frameOption == 'modal') {
            return view('fileviewer.file-window', compact('fileUrl'));
        }
        return view('fileviewer.file-window-offcanvas', compact('fileUrl'));
    }
}
