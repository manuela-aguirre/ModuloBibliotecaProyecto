<?php

namespace App\Services;

use App\Models\Ejemplar;
use App\Models\Editorial;
use App\Models\Genero;
use App\Models\Libro;
use App\Models\Ubicacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser;

class CatalogoImportService
{
    private const HEADERS = [
        'codigo_de_barras', 'signatura', 'titulo', 'isbn', 'editorial',
        'coleccion', 'edicion', 'anio', 'tipo', 'ubicacion', 'estado',
        'fecha_adquisicion', 'factura', 'valor', 'numero_de_entrada',
        'paginas', 'medio_adquisicion', 'recibido_por', 'fecha_ingreso',
        'palabras_clave',
    ];

    public function import(UploadedFile $file, InventoryService $inventory): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $data = match ($extension) {
            'csv' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readExcel($file->getRealPath()),
            'pdf' => $this->readPdf($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'archivo' => 'Formato no admitido. Usa CSV, Excel (.xlsx) o PDF digital con la plantilla de inventario.',
            ]),
        };

        if (count($data['rows']) > 2000) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo supera el límite de 2.000 ejemplares por importación.',
            ]);
        }

        $result = [
            'creados' => 0,
            'libros_creados' => 0,
            'duplicados' => 0,
            'invalidos' => 0,
            'issues' => [],
            'issues_total' => 0,
        ];

        foreach ($data['rows'] as $index => $values) {
            $rowNumber = $index + 2;
            if (count($values) > count(self::HEADERS)
                && collect(array_slice($values, count(self::HEADERS)))->contains(fn ($value) => filled($value))) {
                $result['invalidos']++;
                $this->addIssue($result, "Fila {$rowNumber}: contiene columnas adicionales a la plantilla.");

                continue;
            }

            $row = array_combine(self::HEADERS, array_pad(array_slice($values, 0, count(self::HEADERS)), count(self::HEADERS), null));
            $row = array_map(
                fn ($value) => is_string($value) && trim($value) === '' ? null : $value,
                $row,
            );
            if (collect($row)->every(fn ($value) => blank($value))) {
                continue;
            }

            $row = $this->normalizeValues($row);
            $validator = Validator::make($row, [
                'codigo_de_barras' => ['required', 'string', 'max:50'],
                'signatura' => ['nullable', 'string', 'max:50'],
                'titulo' => ['required', 'string', 'max:150'],
                'isbn' => ['nullable', 'string', 'max:20'],
                'editorial' => ['required', 'string', 'max:100'],
                'coleccion' => ['required', 'in:general,reserva,otra'],
                'edicion' => ['nullable', 'string', 'max:50'],
                'anio' => ['nullable', 'integer', 'between:1400,'.date('Y')],
                'tipo' => ['required', 'string', 'max:30'],
                'ubicacion' => ['nullable', 'string', 'max:100'],
                'estado' => ['required', 'in:disponible,danado,baja'],
                'fecha_adquisicion' => ['nullable', 'date'],
                'factura' => ['nullable', 'string', 'max:50'],
                'valor' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                'numero_de_entrada' => ['nullable', 'string', 'max:30'],
                'paginas' => ['nullable', 'integer', 'min:1', 'max:65535'],
                'medio_adquisicion' => ['nullable', 'in:compra,donacion,canje'],
                'recibido_por' => ['nullable', 'string', 'max:100'],
                'fecha_ingreso' => ['nullable', 'date'],
                'palabras_clave' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $result['invalidos']++;
                $this->addIssue($result, "Fila {$rowNumber}: ".$validator->errors()->first());

                continue;
            }

            $data = $validator->validated();
            foreach (['fecha_adquisicion', 'fecha_ingreso'] as $dateField) {
                $data[$dateField] = filled($data[$dateField] ?? null)
                    ? \Illuminate\Support\Carbon::parse($data[$dateField])->toDateString()
                    : null;
            }
            $ubicacion = filled($data['ubicacion'] ?? null)
                ? Ubicacion::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower(trim($data['ubicacion']))])->first()
                : null;

            if (filled($data['ubicacion'] ?? null) && ! $ubicacion) {
                $result['invalidos']++;
                $this->addIssue($result, "Fila {$rowNumber}: la ubicación «{$data['ubicacion']}» no está registrada.");

                continue;
            }

            if (Ejemplar::query()->where('codigo_barras', trim($data['codigo_de_barras']))->exists()) {
                $result['duplicados']++;
                $this->addIssue($result, "Fila {$rowNumber}: se omitió el código de barras duplicado {$data['codigo_de_barras']}.");

                continue;
            }

            try {
                $createdBook = DB::transaction(function () use ($data, $ubicacion, $inventory): bool {
                    $isbn = filled($data['isbn'] ?? null) ? trim($data['isbn']) : null;
                    $editorial = $this->findOrCreateCatalogValue(Editorial::class, $data['editorial']);
                    $libro = $this->findExistingBook($data['titulo'], $isbn, $editorial->id);
                    $created = ! $libro;

                    if (! $libro) {
                        $genero = $this->findOrCreateCatalogValue(Genero::class, 'Sin clasificar');
                        $libro = Libro::create([
                            'titulo' => trim($data['titulo']),
                            'isbn' => $isbn,
                            'editorial_id' => $editorial->id,
                            'genero_id' => $genero->id,
                            'palabras_clave' => $data['palabras_clave'] ?? null,
                            'stock' => 0,
                        ]);
                    } elseif (blank($libro->palabras_clave) && filled($data['palabras_clave'] ?? null)) {
                        $libro->update(['palabras_clave' => $data['palabras_clave']]);
                    }

                    $inventory->createExemplar($libro, [
                        'codigo_barras' => trim($data['codigo_de_barras']),
                        'signatura' => $data['signatura'] ?? null,
                        'ubicacion_id' => $ubicacion?->id,
                        'coleccion' => $data['coleccion'],
                        'edicion' => $data['edicion'] ?? null,
                        'anio_edicion' => $data['anio'] ?? null,
                        'tipo_material' => trim($data['tipo']),
                        'estado' => $data['estado'],
                        'fecha_adquisicion' => $data['fecha_adquisicion'] ?? null,
                        'numero_factura' => $data['factura'] ?? null,
                        'valor' => $data['valor'] ?? null,
                        'numero_entrada' => $data['numero_de_entrada'] ?? null,
                        'paginas' => $data['paginas'] ?? null,
                        'medio_adquisicion' => $data['medio_adquisicion'] ?? null,
                        'recibido_por' => $data['recibido_por'] ?? null,
                        'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
                    ]);

                    return $created;
                });

                $result['creados']++;
                if ($createdBook) {
                    $result['libros_creados']++;
                }
            } catch (\Illuminate\Database\QueryException $exception) {
                if (Ejemplar::query()->where('codigo_barras', trim($data['codigo_de_barras']))->exists()) {
                    $result['duplicados']++;
                    $this->addIssue($result, "Fila {$rowNumber}: el código de barras {$data['codigo_de_barras']} ya fue importado.");

                    continue;
                }

                throw $exception;
            }
        }

        return $result;
    }

    private function normalizeValues(array $row): array
    {
        $coleccion = Str::of((string) ($row['coleccion'] ?? ''))
            ->ascii()
            ->lower()
            ->trim()
            ->toString();
        $row['coleccion'] = match ($coleccion) {
            'general', 'coleccion general' => 'general',
            'reserva', 'coleccion reserva' => 'reserva',
            'otra', 'otras colecciones', 'otra coleccion' => 'otra',
            default => $coleccion,
        };

        $estado = Str::of((string) ($row['estado'] ?? ''))
            ->ascii()
            ->lower()
            ->trim()
            ->replace(' ', '_')
            ->toString();
        $row['estado'] = match ($estado) {
            'danado' => 'danado',
            default => $estado,
        };

        return $row;
    }

    private function findExistingBook(string $title, ?string $isbn, int $editorialId): ?Libro
    {
        if ($isbn !== null) {
            $byIsbn = Libro::query()->whereRaw('LOWER(isbn) = ?', [mb_strtolower($isbn)])->first();
            if ($byIsbn) {
                return $byIsbn;
            }
        }

        return Libro::query()
            ->whereRaw('LOWER(titulo) = ?', [mb_strtolower(trim($title))])
            ->where('editorial_id', $editorialId)
            ->first();
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['archivo' => 'No se pudo leer el archivo CSV.']);
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            throw ValidationException::withMessages(['archivo' => 'El archivo CSV está vacío.']);
        }
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $rows = [];
        while (($row = fgetcsv($handle, null, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $this->normalizeRows($rows);
    }

    private function readExcel(string $path): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        return $this->normalizeRows($rows);
    }

    private function readPdf(string $path): array
    {
        $text = (new Parser)->parseFile($path)->getText();
        $lines = preg_split('/\R/u', $text) ?: [];
        $headerIndex = null;
        $rows = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $cells = array_map('trim', explode('|', $line));
            if ($headerIndex === null && str_contains($line, '|')
                && $this->normalizeHeaders($cells) === self::HEADERS) {
                $headerIndex = $index;
            } elseif ($headerIndex !== null && $index > $headerIndex) {
                $rows[] = $cells;
            }
        }

        if ($headerIndex === null) {
            throw ValidationException::withMessages([
                'archivo' => 'El PDF no contiene una tabla de texto con los encabezados de la plantilla. Los PDF escaneados no son compatibles.',
            ]);
        }

        return ['rows' => $rows];
    }

    private function normalizeRows(array $rows): array
    {
        if ($rows === []) {
            throw ValidationException::withMessages(['archivo' => 'El archivo no contiene filas.']);
        }

        $headerIndex = null;
        foreach ($rows as $index => $row) {
            if ($this->normalizeHeaders($row) === self::HEADERS) {
                $headerIndex = $index;
                break;
            }
        }

        if ($headerIndex === null) {
            throw ValidationException::withMessages([
                'archivo' => 'Los encabezados no coinciden con la plantilla de inventario. Descárgala de esta página.',
            ]);
        }

        return ['rows' => array_values(array_slice($rows, $headerIndex + 1))];
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header): string {
            return Str::of(trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B"))
                ->ascii()
                ->lower()
                ->trim()
                ->replace([' ', '-'], '_')
                ->toString();
        }, $headers);
    }

    private function findOrCreateCatalogValue(string $model, string $name): object
    {
        $normalized = mb_strtolower(trim($name));

        return $model::query()->whereRaw('LOWER(nombre) = ?', [$normalized])->first()
            ?? $model::create(['nombre' => trim($name)]);
    }

    private function addIssue(array &$result, string $issue): void
    {
        $result['issues_total']++;
        if (count($result['issues']) < 50) {
            $result['issues'][] = $issue;
        }
    }
}
