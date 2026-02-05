<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Descargar o servir un archivo de media
     */
    public function download($mediaId)
    {
        try {
            $media = Media::findOrFail($mediaId);

            // Obtener el disco configurado
            $disk = Storage::disk($media->disk);
            
            // getPath() devuelve la ruta absoluta completa
            $fullPath = $media->getPath();
            $diskRoot = $disk->path('');
            
            // Extraer la ruta relativa desde la raíz del disco (para discos remotos como S3)
            $relativePath = str_replace($diskRoot, '', $fullPath);
            $relativePath = ltrim($relativePath, '/\\');
            
            // Verificar que el archivo existe físicamente (usar la ruta absoluta)
            if (!file_exists($fullPath)) {
                \Log::warning('Archivo de media no encontrado físicamente', [
                    'media_id' => $mediaId,
                    'disk' => $media->disk,
                    'full_path' => $fullPath,
                    'relative_path' => $relativePath,
                    'media_collection' => $media->collection_name
                ]);
                abort(404, 'Archivo no encontrado');
            }

            // Para discos locales ('public' o 'local'), usar la ruta absoluta directamente
            if (in_array($media->disk, ['public', 'local'])) {
                // Servir el archivo con los headers apropiados usando la ruta absoluta
                return response()->file($fullPath, [
                    'Content-Type' => $media->mime_type ?? 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                ]);
            } else {
                // Para otros discos (como S3), usar download con la ruta relativa
                return $disk->download($relativePath, $media->file_name);
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Media no encontrado', [
                'media_id' => $mediaId,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Archivo no encontrado');
        } catch (\Exception $e) {
            \Log::error('Error al descargar archivo de media', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Error al descargar el archivo');
        }
    }

    /**
     * Obtener la URL de descarga de un archivo de media
     */
    public function getDownloadUrl($mediaId)
    {
        return route('media.download', $mediaId);
    }
}
