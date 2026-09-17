<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\DocumentationEntry;
use OpenAI\Laravel\Facades\OpenAI; // Pastikan menggunakan Facade yang betul
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str; // Untuk manipulasi string

class GenerateDocumentation extends Command
{
    protected $signature = 'generate:docs';
    protected $description = 'Generate system documentation using OpenAI and save to database';

    public function handle()
    {
        $this->info('Starting documentation generation...');
        // Anda boleh menambah direktori lain seperti Services, Models, dll.
        $controllerFiles = File::allFiles(app_path('Http/Controllers'));
        // $serviceFiles = File::allFiles(app_path('Services'));
        // $allFiles = array_merge($controllerFiles, $serviceFiles);

        foreach ($controllerFiles as $file) { // Tukar kepada $allFiles jika mahu lebih
            $path = $file->getRealPath();
            $classContent = File::get($path);
            
            $namespace = $this->getNamespace($classContent);
            $className = $this->getClassName($classContent);

            if (empty($namespace) || empty($className)) {
                $this->warn("Could not determine namespace or class name for: {$path}");
                continue;
            }

            $fqcn = $namespace . '\\' . $className; // Fully Qualified Class Name

            // Sesetengah kelas mungkin tidak dimuatkan secara automatik
            if (!class_exists($fqcn, false)) { // 'false' untuk tidak autoload
                 require_once $path;
            }
            
            if (!class_exists($fqcn)) {
                $this->warn("Class {$fqcn} not found after requiring path. Skipping.");
                continue;
            }

            $this->line("Processing: {$fqcn}");

            try {
                $reflection = new ReflectionClass($fqcn);
                // Hanya metod yang didefinisikan dalam kelas ini, bukan dari parent class
                $methods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                    ->filter(fn($m) => $m->getDeclaringClass()->getName() === $fqcn)
                    ->map(fn($m) => $m->getName())
                    ->values()->all();

                $views = $this->detectViewFiles($classContent);
                $models = $this->detectModelFiles($classContent);
                $tests = $this->detectTestFiles($className);
                $factories = $this->detectFactoryFiles($models);

                $summary = [
                    'class_name' => $fqcn,
                    'file_path' => Str::replaceFirst(base_path() . DIRECTORY_SEPARATOR, '', $path),
                    'methods' => $methods,
                    'associated_views' => $views,
                    'associated_models' => $models,
                    'associated_test_files' => $tests,
                    'associated_factory_files' => $factories,
                ];
                
                $jsonSummary = json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                $prompt = "Anda adalah seorang penulis teknikal pakar Laravel. Tugas anda adalah untuk menjana dokumentasi teknikal yang jelas dan profesional dalam format Markdown untuk kelas Controller Laravel yang diberikan berdasarkan nama kelasnya, laluan fail, metod-metod, serta fail-fail view, model, ujian, dan factory yang berkaitan.\n\n";
                $prompt .= "Sila ikut struktur ini:\n";
                $prompt .= "1.  **Nama Kelas (Class Name)** – Nama kelas penuh beserta namespace.\n";
                $prompt .= "2.  **Laluan Fail (File Path)** – Lokasi fail controller.\n";
                $prompt .= "3.  **Penerangan (Description)** – Ringkasan tujuan controller dalam 2-3 ayat.\n";
                $prompt .= "4.  **Gambaran Keseluruhan Metod (Methods Overview)** – Senaraikan setiap metod dalam kelas dengan:\n";
                $prompt .= "    - Nama metod\n";
                $prompt .= "    - Tujuan (berdasarkan nama metod dan konteks kelas)\n";
                $prompt .= "    - Parameter (jika dapat dikesan atau inferens umum)\n";
                $prompt .= "    - Nilai Pulangan (apa yang mungkin dipulangkan, cth: View, JSON, Redirect)\n";
                $prompt .= "5.  **Model Digunakan (Used Models)** – Senaraikan dan terangkan setiap model Eloquent yang digunakan dalam controller.\n";
                $prompt .= "6.  **View Digunakan (Used Views)** – Senaraikan fail view Blade yang di-render, dan terangkan secara ringkas apa yang dipaparkan.\n";
                $prompt .= "7.  **Fail Ujian Berkaitan (Associated Test Files)** – Nyatakan jika controller ini diliputi oleh ujian.\n";
                $prompt .= "8.  **Fail Factory (Factory Files)** – Nyatakan fail factory yang berkaitan dan tujuannya.\n\n";
                $prompt .= "Pastikan penjelasan jelas dan membantu untuk pembangun junior atau ahli pasukan baharu. Formatkan output dengan kemas dalam Markdown.\n\n";
                $prompt .= "Berikut adalah ringkasan JSON mengenai kelas tersebut:\n\n";
                $prompt .= "```json\n" . $jsonSummary . "\n```";

                $this->line("Sending request to OpenAI for {$fqcn}...");
                $openaiResponse = OpenAI::chat()->create([
                    'model' => 'gpt-4', // Atau gpt-3.5-turbo jika bajet terhad
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah penjana dokumentasi perisian yang sangat mahir.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ]);

                $documentationContent = $openaiResponse->choices[0]->message->content;

                DocumentationEntry::updateOrCreate(
                    ['class_name' => $fqcn],
                    [
                        'source_path' => $path,
                        'metadata' => $summary, // Simpan array, bukan JSON string
                        'documentation' => $documentationContent,
                    ]
                );

                $this->info("Dokumentasi dijana dan disimpan untuk: {$fqcn}");

            } catch (\Exception $e) {
                $this->error("Gagal memproses {$fqcn}: " . $e->getMessage());
            }
             $this->line("--------------------------------------------------");
        }
        $this->info('Documentation generation complete!');
        return 0; // Berjaya
    }

    // Fungsi bantuan (helper functions)
    protected function getNamespace($content)
    {
        if (preg_match('/namespace\s+([^;]+);/m', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function getClassName($content)
    {
        if (preg_match('/class\s+(\w+)/m', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function detectViewFiles($content)
    {
        // Mencari view('nama.view') atau View::make('nama.view')
        preg_match_all('/(?:view|View::make)\s*\(\s*[\'"]([a-zA-Z0-9_.-]+)[\'"]/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }

    protected function detectModelFiles($content)
    {
        // Mencari `use App\Models\NamaModel;` atau `new NamaModel(` atau `NamaModel::`
        preg_match_all('/(?:use\s+App\\\Models\\\([A-Za-z0-9_]+);|new\s+([A-Z][A-Za-z0-9_]+)\s*\(|([A-Z][A-Za-z0-9_]+)::)/m', $content, $matches);
        $models = array_filter(array_merge($matches[1], $matches[2], $matches[3]));
        // Saring untuk memastikan hanya model yang wujud di App/Models
        return collect($models)->unique()->filter(function ($modelName) {
            return File::exists(app_path("Models/{$modelName}.php"));
        })->values()->all();
    }
    
    protected function detectTestFiles($className)
    {
        $potentialTestName = $className . 'Test.php';
        $featureTestPath = base_path("tests/Feature/{$potentialTestName}");
        $unitTestPath = base_path("tests/Unit/{$potentialTestName}");
        
        $foundTests = [];
        if (File::exists($featureTestPath)) {
            $foundTests[] = Str::replaceFirst(base_path() . DIRECTORY_SEPARATOR, '', $featureTestPath);
        }
        if (File::exists($unitTestPath)) {
            $foundTests[] = Str::replaceFirst(base_path() . DIRECTORY_SEPARATOR, '', $unitTestPath);
        }
        return $foundTests;
    }

    protected function detectFactoryFiles($models)
    {
        return collect($models)
            ->map(fn($model) => "database/factories/{$model}Factory.php")
            ->filter(fn($path) => File::exists(base_path($path)))
            ->values()
            ->all();
    }
}