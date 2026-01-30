<?php

declare(strict_types=1);

namespace Arzou\MimeGuard\Listeners;

use Arzou\MimeGuard\Rules\MimeValidator;
use Arzou\MimeGuard\Rules\RuleResolver;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Assets\Asset;
use Statamic\Events\AssetSaving;

class AssetSavingListener
{
    public function __construct(
        protected RuleResolver $resolver = new RuleResolver,
        protected MimeValidator $validator = new MimeValidator
    ) {}

    /**
     * Handle the AssetSaving event.
     * Throws HttpResponseException to reject unauthorized uploads.
     */
    public function handle(AssetSaving $event): void
    {
        $asset = $event->asset;

        // Only validate new uploads (assets that don't exist yet)
        if (! $this->isNewUpload($asset)) {
            return;
        }

        // Get the file path on disk
        $filePath = $this->getFilePath($asset);

        if ($filePath === null) {
            return;
        }

        // Detect MIME type from content (magic bytes)
        $mimeType = $this->validator->getMimeTypeFromContent($filePath);

        // Build context for rule resolution
        $context = $this->resolver->buildContext(
            container: $asset->container()?->handle(),
        );

        // Check if MIME type is allowed
        if (! $this->resolver->isAllowedInContext($mimeType, $context)) {
            $this->logRejection($mimeType, $asset->path(), $context);
            $this->deleteFile($asset);
            $this->rejectUpload($mimeType);
        }
    }

    /**
     * Check if this is a new upload (not an existing asset being modified).
     */
    protected function isNewUpload(Asset $asset): bool
    {
        $existingAsset = $asset->container()?->asset($asset->path());

        return $existingAsset === null;
    }

    /**
     * Get the full file path on disk.
     */
    protected function getFilePath(Asset $asset): ?string
    {
        $disk = $asset->container()?->disk();
        $path = $asset->path();

        if (! $disk || ! $path) {
            return null;
        }

        $fullPath = $disk->path($path);

        return file_exists($fullPath) ? $fullPath : null;
    }

    /**
     * Delete the uploaded file from disk.
     */
    protected function deleteFile(Asset $asset): void
    {
        $disk = $asset->container()?->disk();
        $path = $asset->path();

        if ($disk && $path && $disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Log the rejection if logging is enabled.
     */
    protected function logRejection(string $mimeType, string $filename, array $context): void
    {
        if (! Config::get('mime-guard.logging.enabled', true)) {
            return;
        }

        $channel = Config::get('mime-guard.logging.channel', 'stack');

        Log::channel($channel)->info('[MIME Guard] Upload rejected', [
            'mime_type' => $mimeType,
            'filename' => $filename,
            'container' => $context['container'] ?? null,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Reject the upload with an HTTP response exception.
     */
    protected function rejectUpload(string $mimeType): never
    {
        $message = __('mime-guard::messages.upload_rejected', ['mime_type' => $mimeType]);

        throw new HttpResponseException(
            response()->json([
                'message' => $message,
                'errors' => ['file' => [$message]],
            ], 422)
        );
    }
}
