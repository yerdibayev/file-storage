<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Models\File;
use App\Http\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FileController extends Controller
{
    protected $service;

    public function __construct(FileService $fileService)
    {
        $this->service = $fileService;
    }

    public function index(Request $request)
    {
        $data = $this->service->index($request);

        return Inertia::render('File/Index', $data);
    }

    public function fetch(Request $request): JsonResponse
    {
        $data = $this->service->fetch($request);

        return response()->json($data);
    }

    public function store(FileRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $created_file = $this->service->store($request);

        return response()->json([
            'message' => 'Файл успешно загружен.',
            'file' => $created_file
        ], 201);
    }

    public function destroy(File $file): JsonResponse
    {
        $this->service->delete($file);

        return response()->json(['message' => 'Файл удален.'], 200);
    }
}
