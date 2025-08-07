<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTelegramUsersRequest;
use App\Http\Requests\UpdateTelegramUsersRequest;
use App\Models\TelegramUsers;

class TelegramUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreTelegramUsersRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TelegramUsers $telegramUsers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TelegramUsers $telegramUsers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTelegramUsersRequest $request, TelegramUsers $telegramUsers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TelegramUsers $telegramUsers)
    {
        //
    }
}
