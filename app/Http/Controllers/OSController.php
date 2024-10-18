<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use Illuminate\Http\Request;
use App\Models\Clientes;

class OSController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $clientes=Clientes::all();
      return view('content.os.criar', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(OrdemServico $ordemServico)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrdemServico $ordemServico)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrdemServico $ordemServico)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrdemServico $ordemServico)
    {
        //
    }
}
