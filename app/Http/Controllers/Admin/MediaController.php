<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaAssetRequest;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    /**
     * Daftar key media terkelola beserta label tampilannya.
     *
     * @var array<string, string>
     */
    private const MANAGED = [
        'screening_illustration' => 'Ilustrasi Halaman Prediksi/Skrining',
    ];

    
    public function index(): Response
    {
        $assets = collect(self::MANAGED)
            ->map(function (string $label, string $key): array {
                $asset = MediaAsset::query()->firstOrCreate(
                    ['key' => $key],
                    ['type' => 'image'],
                );

                return [
                    'key' => $asset->key,
                    'label' => $label,
                    'url' => $asset->url,
                    'type' => $asset->type,
                    'alt' => $asset->alt,
                ];
            })
            ->values()
            ->all();

        return Inertia::render('admin/media/index', [
            'assets' => $assets,
        ]);
    }

    
    public function update(MediaAssetRequest $request, string $key): RedirectResponse
    {
        $asset = MediaAsset::query()->firstOrNew(['key' => $key]);

        if ($request->hasFile('file')) {
            if ($asset->path) {
                Storage::disk('public')->delete($asset->path);
            }

            $file = $request->file('file');
            $asset->path = $file->store('media', 'public');
            $asset->type = $this->deriveType($file->getMimeType(), $file->getClientOriginalExtension());
        }

        $asset->alt = $request->validated()['alt'] ?? $asset->alt;
        $asset->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Media berhasil diperbarui.']);

        return back();
    }

    
    private function deriveType(?string $mime, ?string $extension): string
    {
        $mime = (string) $mime;
        $extension = strtolower((string) $extension);

        if ($mime === 'image/gif' || $extension === 'gif') {
            return 'gif';
        }

        if (str_starts_with($mime, 'video/') || in_array($extension, ['mp4', 'webm'], true)) {
            return 'video';
        }

        return 'image';
    }
}
