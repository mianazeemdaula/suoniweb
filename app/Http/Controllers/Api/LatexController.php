<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ismaelw\LaraTeX\LaraTeX;
use Illuminate\Support\Facades\File;

class LatexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return (new LaraTeX)->dryRun();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return (new LaraTeX)->dryRun();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:tex',
            // 'text' => 'required|string',
        ]);
        $fileName = time() . '.' . $request->file->getClientOriginalExtension();
        if(!File::exists('latex')){
            File::makeDirectory('latex');
        }
        $request->file->move('latex', $fileName);
        $latexFile = 'latex/' . $fileName;
        $files =  File::get($latexFile);
        $data =  (new LaraTeX('latex'))->with([
            'tex' => $files,
        ]);
        File::delete($latexFile);
        return $data->download('test.pdf');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
