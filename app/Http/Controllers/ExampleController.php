<?php

namespace App\Http\Controllers;


use Framework\Core\Response;
use Framework\Core\Request;
use App\Models\User;


class ExampleController
{
    public function hello(Request $request)
    {
        return Response::json(['message' => 'Olá mundo']);
    }


    public function index()
    {
        $users = User::all();
        return Response::json($users, 200);
    }
}
