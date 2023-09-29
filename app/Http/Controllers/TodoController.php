<?php

namespace App\Http\Controllers;

use App\Clients\ApiResponse;
use App\Clients\SingleflightClient;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Models\Todo;

class TodoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $key = 'get-all-todo';

        $result = SingleflightClient::run($key, function () {
            return Todo::all();
        });

        // Check if the result is false, indicating a cache miss
        if ($result === false) {
            // Handle cache miss scenario
            // Perform the Todo::all() query and return the result
            $result = Todo::all();

            // Cache the result
            SingleflightClient::run($key, function () use ($result) {
                return $result;
            });
        }

        return new ApiResponse([
            'message' => 'Retrieved list',
            'todo' => $result,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return new ApiResponse([
            'message' => 'Todo created successfully',
            'todo' => $todo,
        ]);
    }

    public function show($id)
    {
        // Check if the data exists in the cache
        $cacheKey = 'todo_' . $id;
        $todo = Redis::get($cacheKey);

        if ($todo === null) {
            // If the data is not in the cache, retrieve it from the database
            $todo = Todo::find($id);
            // Store the serialized data in Redis
            Redis::set($cacheKey, json_encode($todo));
        } else {
            // If the data is in the cache, deserialize it back into an object
            $todo = json_decode($todo);
        }

        return new ApiResponse([
            'todo' => $todo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $todo = Todo::find($id);
        $todo->title = $request->title;
        $todo->description = $request->description;
        $todo->save();

        return new ApiResponse([
            'message' => 'Todo updated successfully',
            'data' => $todo,
        ]);
    }

    public function destroy($id)
    {
        $todo = Todo::find($id);
        $todo->delete();

        return new ApiResponse([
            'message' => 'Todo deleted successfully',
            'todo' => $todo,
        ]);
    }
}
