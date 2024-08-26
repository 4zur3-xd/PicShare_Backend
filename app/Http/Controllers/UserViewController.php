<?php

namespace App\Http\Controllers;

use App\Models\UserView;
use App\Http\Requests\StoreUserViewRequest;
use App\Http\Requests\UpdateUserViewRequest;

class UserViewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserViewRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserView $userView)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserViewRequest $request, UserView $userView)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserView $userView)
    {
        //
    }
}
