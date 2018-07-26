<?php

namespace App\Http\Controllers;

use App\Models\Host;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
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
        $metadata = $bucket->getMetadata($path);
        $headers = $this->buildHeadersFromMetadata($metadata);

        return response($bucket->get($path), Response::HTTP_OK, $headers);
    }

    private function retrieveBucketForHost(string $hostname): Filesystem
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

    private function buildHeadersFromMetadata(array $metadata): array
    {
        $mimetype = $metadata['mimetype'] ?? 'text/plain';

        $headers = [];
        $headers['Content-Type'] = $mimetype;
        $headers['Cache-Control'] = $this->buildCacheControlHeader($mimetype);
        $headers['Last-Modified'] = Carbon::createFromTimestamp($metadata['timestamp'] ?? time())->toRfc7231String();

        return $headers;
    }

    private function buildCacheControlHeader(string $mimetype): string
    {
        $cacheableMimeTypes = ['application/javascript', 'text/css', 'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/x-icon'];

        return in_array($mimetype, $cacheableMimeTypes) ? 'private, max-age=604800' : 'private, no-cache';
    }
}
