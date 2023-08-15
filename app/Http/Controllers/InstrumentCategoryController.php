<?php

namespace App\Http\Controllers;

use App\Models\InstrumentCategory;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use App\Forms\Admin\InstrumentCategoryForm;

class InstrumentCategoryController extends Controller
{
    use FormBuilderTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = InstrumentCategory::with(['instrument'])->get();
        return view('admin.instrument_category.index', compact('collection'));
    }

    public function create()
    {
        $form = $this->form(InstrumentCategoryForm::class, [
            'method' => 'POST',
            'class' => 'form-horizontal',
            'url' => route('category.store')
        ]);
        return view('admin.instrument_category.create', compact('form'));
    }

    public function store(Request $request)
    {
        $form = $this->form(InstrumentCategoryForm::class);

        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        $category = new InstrumentCategory();
        $category->name = $request->name;
        $category->instrument_id = $request->instrument_id;
        $category->save();
        return redirect()->route('category.index')->with('status', 'Category Created Successfully!');
    }

    public function edit($id)
    {
        $category = InstrumentCategory::findOrFail($id);
        $form = $this->form(InstrumentCategoryForm::class, [
            'method' => 'PUT',
            'class' => 'form-horizontal',
            'url' => route('category.update', [$id]),
            'model' => $category
        ]);

        return view('admin.instrument_category.edit', compact('form', 'category'));
    }

    public function update(Request $request, InstrumentCategory $category)
    {
        $form = $this->form(InstrumentCategoryForm::class);
        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        $category->name = $request->name;
        $category->instrument_id = $request->instrument_id;
        $category->save();
        return redirect()->back()->with('status', 'Category Unit Updated!');
    }

    public function show(InstrumentCategory $unit)
    {
        return view('ingredient-unit.view', compact('unit'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InstrumentCategory  $instrumentCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(InstrumentCategory $instrumentCategory)
    {
        //
    }
}
