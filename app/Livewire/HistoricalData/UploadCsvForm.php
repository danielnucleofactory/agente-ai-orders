<?php

namespace App\Livewire\HistoricalData;

use App\Services\HistoricalDataImportService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class UploadCsvForm extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $uploading = false;
    public $importResult = null;
    public $showModal = false;

    protected $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:51200', // 50MB max
    ];

    protected $messages = [
        'csvFile.required' => 'Debe seleccionar un archivo CSV.',
        'csvFile.mimes' => 'El archivo debe ser un CSV (.csv o .txt).',
        'csvFile.max' => 'El archivo no puede ser mayor a 50MB.',
    ];

    public function openModal()
    {
        $this->showModal = true;
        $this->reset(['csvFile', 'importResult', 'uploading']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['csvFile', 'importResult', 'uploading']);
    }

    public function uploadCsv()
    {
        $this->validate();

        if (!$this->csvFile) {
            session()->flash('error', 'No se seleccionó ningún archivo.');
            return;
        }

        $this->uploading = true;
        $this->importResult = null;

        try {
            // Obtener la ruta real del archivo temporal de Livewire
            $fullPath = $this->csvFile->getRealPath();

            if (!file_exists($fullPath)) {
                throw new \Exception('El archivo no se pudo cargar correctamente.');
            }

            // Procesar el CSV usando el servicio
            $importService = app(HistoricalDataImportService::class);
            $result = $importService->importFromCsv($fullPath);

            $this->importResult = $result;
            $this->uploading = false;

            // Emitir evento para refrescar la tabla
            $this->dispatch('refreshTable');

            if ($result['errors'] > 0 && $result['imported'] === 0) {
                session()->flash('error', 'Error al importar el archivo. Verifique el formato del CSV.');
            } elseif ($result['imported'] > 0) {
                session()->flash('message', "Importación completada: {$result['imported']} registros importados, {$result['skipped']} duplicados saltados.");
            } else {
                session()->flash('warning', 'No se importaron registros nuevos. Todos los registros ya existían.');
            }

        } catch (\Exception $e) {
            $this->uploading = false;
            Log::error('Error al importar CSV histórico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.historical-data.upload-csv-form');
    }
}

