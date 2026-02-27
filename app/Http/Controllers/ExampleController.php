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


    public function index(Request $request)
    {
        $query = new User();

        $request->applyFilters(
            $query,
            ['id', 'name', 'email', 'created_at', 'updated_at'],
            ['id', 'name', 'email', 'created_at', 'updated_at']
        );

        if ($request->has('page') || $request->has('per_page')) {
            $pagination = $request->pagination(15, 100);
            $users = $query->paginate($pagination['per_page'], $pagination['page']);
            return Response::json($users, 200);
        }

        $users = $query->get();
        return Response::json($users, 200);
    }
}
