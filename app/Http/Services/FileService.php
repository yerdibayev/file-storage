<?php

namespace App\Http\Services;

use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as FileFacade;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileService
{
    public function index($request): array
    {
        $totalFilesCount = File::count();

        $files = $this->getFiles($request);

        $currentPageFilesCount = $files->count();

        foreach ($files as $file) {
            $this->createProps($file);
        }

        return [
            'files' => $files,
            'totalFilesCount' => $totalFilesCount,
            'currentPageFilesCount' => $currentPageFilesCount,
        ];
    }

    public function fetch($request): array
    {
        $nameFilter = $request->input('name');
        $totalFilesCount = File::count();

        $files = $this->getFiles($request);
        $currentPageFilesCount = $files->count();

        foreach ($files as $file) {
            $this->createProps($file);
        }

        return [
            'name' => $nameFilter,
            'files' => $files,
            'totalFilesCount' => $totalFilesCount,
            'currentPageFilesCount' => $currentPageFilesCount,
        ];
    }

    public function store($request)
    {
        $file = $request->file('file');
        $fileName = $request->input('name', $file->getClientOriginalName());
        $fileName = str_replace(' ', '_', $fileName);

        $created_file = File::create([
            'name' => $fileName,
            'path' => $fileName,
        ]);
        $created_file->path = $created_file->id . '_' . $created_file->path;
        $created_file->save();

        $file->storeAs('public/uploads', $created_file->path);

        $this->createProps($created_file);

        return $created_file;
    }

    public function delete(File $file): void
    {
        if (Storage::exists('public/uploads/' . $file->path)) {
            Storage::delete('public/uploads/' . $file->path);
        }

        if (FileFacade::exists('thumbnails/' . $file->path)) {
            FileFacade::delete('thumbnails/' . $file->path);
        }

        // Delete the record from the database
        $file->delete();
    }

    protected function getFiles($request)
    {
        if ($request->has('name')) {
            return File::where('name', 'like', '%' . $request->name . '%')->paginate(50);
        } else {
            return File::paginate(50);
        }
    }

    public function createProps(File $file): void
    {
        if (Storage::exists('/public/uploads/' . $file->path)) {
            $file->size = round(Storage::size('/public/uploads/' . $file->path) / 1048576, 1) . 'Mb';
        } else {
            $file->size = 'doesn\'t exist';
        }

        $file->extension = pathinfo($file->name, PATHINFO_EXTENSION);
        $file->url = Storage::url('/uploads/' . $file->path);
        $file->mime = Storage::mimeType('/public/uploads/' . $file->path);

        if (str_contains($file->mime, 'image')) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read('storage/uploads/' . $file->path);
            $image->cover(100, 100);
            $image->encodeByExtension()->save('thumbnails/' . $file->path);
            $file->thumbnail = '/thumbnails/' . $file->path;
        }
    }
}
