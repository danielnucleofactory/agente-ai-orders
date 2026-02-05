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
            
            // Obtener la ruta relativa del archivo usando el método correcto de Spatie Media Library
            // El método getPath() devuelve la ruta relativa desde la raíz del disco
            $filePath = $media->getPath();
            
            // Verificar que el archivo existe en el disco
            if (!$disk->exists($filePath)) {
                \Log::warning('Archivo de media no encontrado en disco', [
                    'media_id' => $mediaId,
                    'disk' => $media->disk,
                    'path' => $filePath,
                    'media_collection' => $media->collection_name
                ]);
                abort(404, 'Archivo no encontrado');
            }

            // Para discos locales ('public' o 'local'), obtener la ruta física
            if (in_array($media->disk, ['public', 'local'])) {
                $physicalPath = $disk->path($filePath);
                
                // Verificar que el archivo existe físicamente
                if (!file_exists($physicalPath)) {
                    \Log::warning('Archivo de media no existe físicamente', [
                        'media_id' => $mediaId,
                        'physical_path' => $physicalPath,
                        'disk_path' => $filePath
                    ]);
                    abort(404, 'Archivo no encontrado');
                }

                // Servir el archivo con los headers apropiados
                return response()->file($physicalPath, [
                    'Content-Type' => $media->mime_type ?? 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                ]);
            } else {
                // Para otros discos (como S3), usar download
                return $disk->download($filePath, $media->file_name);
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
