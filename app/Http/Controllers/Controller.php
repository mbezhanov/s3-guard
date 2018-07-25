<?php

namespace App\Http\Controllers;

use App\Models\Host;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
    }

    public function index(Request $request)
    {
        $host = $request->getHost();
        $path = $request->getPathInfo();
        $bucket = $this->retrieveBucketForHost($host);

        if ($path === '/') {
            $path = 'index.html';
        }

        if (!$bucket->exists($path)) {
            throw new NotFoundHttpException();
        }

        return response($bucket->get($path), Response::HTTP_OK, ['Content-Type' => $bucket->getMimetype($path)]);
    }

    private function retrieveBucketForHost(string $hostname)
    {
        $bucket = Host::where('name', $hostname)->first();

        if (!$bucket) {
            throw new NotFoundHttpException();
        }

        return $this->filesystemManager->createS3Driver([
            'key' => $bucket->access_key,
            'secret' => $bucket->secret_key,
            'region' => $bucket->region_name,
            'bucket' => $bucket->bucket_name,
        ]);
    }
}
