<?php

namespace App\Http\Controllers;

use App\Models\DocumentationEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Untuk Markdown parser

class DocumentationController extends Controller
{
    public function index()
    {
        // Ambil semua entri, susun mengikut nama kelas
        $docs = DocumentationEntry::orderBy('class_name')->get();
        
        // Tukar Markdown ke HTML untuk setiap entri dokumentasi
        // Anda mungkin mahu buat ini hanya apabila entri itu dipaparkan
        // untuk mengelakkan pemprosesan berlebihan jika banyak entri.
        // Di sini kita akan buat di controller untuk memudahkan templat Blade.
        $docs->transform(function ($doc) {
            // Gantikan blok kod Markdown dengan tag pre yang digayakan
            $doc->documentation_html = Str::markdown($doc->documentation);
            // Contoh penggantian asas untuk blok kod:
            // Regex ini mungkin perlu diperhalusi
            $doc->documentation_html = preg_replace_callback(
                '/<pre><code(?: class="language-(.*?)")?>(.*?)<\/code><\/pre>/s',
                function ($matches) {
                    $language = $matches[1] ?? 'plaintext';
                    $code = htmlspecialchars_decode($matches[2]); // Decode HTML entities
                    return '<div class="code-block language-'.e($language).'"><pre><code>'.e($code).'</code></pre></div>';
                },
                $doc->documentation_html
            );
            return $doc;
        });

        return view('docs.index', ['docs' => $docs]);
    }
}