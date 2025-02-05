<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductMoreEccomerceDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductMoreEccomerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductMoreEccomerceDataTable $dataTable)
    {
        
        return $dataTable->render('admin.product.more-eccomerce.index' );

    }


    public function create()
    {
        
        return view('admin.product.more-eccomerce.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
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
