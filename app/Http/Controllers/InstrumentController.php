<?php

namespace App\Http\Controllers;

use App\Forms\Admin\InstrumentForm;
use App\Forms\Admin\InstrumentEditForm;
use App\Models\Instrument;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class InstrumentController extends Controller
{
    use FormBuilderTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = Instrument::orderBy('order', 'asc')->get();
        return view('admin.instrument.index', compact('collection'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = $this->form(InstrumentForm::class, [
            'method' => 'POST',
            'class' => 'form-horizontal',
            'url' => route('instrument.store')
        ]);
        return view('admin.instrument_category.create', compact('form'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $form = $this->form(InstrumentForm::class);

        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        $instrument = new Instrument();
        $instrument->name = $request->name;
        $instrument->status = $request->status;
        if ($request->has('image')) {
            $name = 'images/' . Str::random(20) . '.' . $request->image->getClientOriginalExtension();
            Image::make($request->image)->resize(128, 128, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($name);
            $instrument->logo = $name;
        }
        $instrument->save();
        return redirect()->route('instrument.index')->with('status', 'Instrument Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Instrument  $instrument
     * @return \Illuminate\Http\Response
     */
    public function show(Instrument $instrument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Instrument  $instrument
     * @return \Illuminate\Http\Response
     */
    public function edit(Instrument $instrument)
    {
        $form = $this->form(InstrumentEditForm::class, [
            'method' => 'PUT',
            'class' => 'form-horizontal',
            'url' => route('instrument.update', [$instrument->id]),
            'model' => $instrument
        ]);

        return view('admin.instrument.edit', compact('form', 'instrument'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Instrument  $instrument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instrument $instrument)
    {
        $form = $this->form(InstrumentEditForm::class);
        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        if ($request->has('image')) {
            $name = 'images/' . Str::random(20) . '.' . $request->image->getClientOriginalExtension();
            Image::make($request->image)->resize(128, 128, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($name);
            $instrument->logo = $name;
        }
        $instrument->name = $request->name;
        $instrument->status = $request->status;
        $instrument->save();
        return redirect()->back()->with('status', 'Instrument updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Instrument  $instrument
     * @return \Illuminate\Http\Response
     */
    public function destroy(Instrument $instrument)
    {
        $instrument->delete();
        return redirect()->back()->with('status', 'Instrument Deleted!');
    }

    public function updateOrder(Request $request)
    {
        $instruments = Instrument::all();

        foreach ($instruments as $post) {
            foreach ($request->order as $order) {
                if ($order['id'] == $post->id) {
                    $post->update(['order' => $order['position']]);
                }
            }
        }

        return response('Update Successfully.', 200);
    }
}
