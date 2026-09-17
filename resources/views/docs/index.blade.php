@extends('layout.app') {{-- Atau templat utama anda --}}

@section('title', 'Dokumentasi Sistem')

@section('content')
    <input type="text" id="searchBox" placeholder="Cari dokumentasi...">

            <div id="docsContainer">
                @forelse ($docs as $doc)
                    <div class="doc-section" data-search="{{ strtolower($doc->class_name . ' ' . $doc->documentation) }}">
                        <div class="doc-header" onclick="toggleDoc(this)">
                            <div class="doc-title">
                                <span class="doc-badge">
                                    {{-- Logik untuk badge berdasarkan nama kelas --}}
                                    @if (Str::contains(strtolower($doc->class_name), 'controller'))
                                        CONTROLLER
                                    @elseif (Str::contains(strtolower($doc->class_name), 'service'))
                                        SERVICE
                                    @elseif (Str::contains(strtolower($doc->class_name), 'model'))
                                        MODEL
                                    @else
                                        CLASS
                                    @endif
                                </span>
                                {{ $doc->class_name }}
                            </div>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div class="doc-content">
                            <div class="doc-body">
                                <div class="doc-meta">
                                    <div class="meta-title">📋 Maklumat Kelas</div>
                                    <div class="meta-content">
                                        <strong>Nama Kelas:</strong> {{ $doc->class_name }}<br>
                                        <strong>Laluan Sumber:</strong> {{ $doc->source_path }}<br>
                                        <strong>Dijana pada:</strong> {{ $doc->updated_at ? $doc->updated_at->format('d M Y, H:i') : 'N/A' }}
                                    </div>
                                </div>

                                {{-- Paparkan HTML yang telah ditukar dari Markdown --}}
                                {!! $doc->documentation_html !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="no-results">
                        <h3>🚫 Tiada dokumentasi dijumpai</h3>
                        <p>Sila jalankan perintah <code class="command">php artisan generate:docs</code> untuk menjana dokumentasi.</p>
                    </div>
                @endforelse
            </div>

            <div id="noResults" class="no-results" style="display: none;">
                <h3>🔍 Tiada hasil carian dijumpai</h3>
                <p>Cuba cari dengan kata kunci yang berbeza.</p>
            </div>
        {{-- ... (bahagian bawah HTML anda: skrip JavaScript) ... --}}
    <script>
        function toggleDoc(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.collapse-icon');

            document.querySelectorAll('.doc-content.active').forEach(el => {
                if (el !== content) {
                    el.classList.remove('active');
                    el.previousElementSibling.classList.remove('active');
                    const prevIcon = el.previousElementSibling.querySelector('.collapse-icon');
                    if (prevIcon) {
                        prevIcon.classList.remove('active');
                    }
                }
            });

            content.classList.toggle('active');
            header.classList.toggle('active');
            if (icon) {
                icon.classList.toggle('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.getElementById('searchBox');
            if (searchBox) {
                searchBox.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const docSections = document.querySelectorAll('.doc-section');
                    const noResults = document.getElementById('noResults');
                    const docsContainer = document.getElementById('docsContainer');
                    let hasResults = false;

                    docSections.forEach(section => {
                        const searchData = section.getAttribute('data-search') || '';

                        if (searchData.includes(searchTerm) || searchTerm === '') {
                            section.style.display = 'block';
                            hasResults = true;
                        } else {
                            section.style.display = 'none';
                        }
                    });

                    const emptyState = document.querySelector('.no-results h3');
                    if (emptyState && emptyState.textContent.startsWith('🚫')) {
                        return;
                    }

                    if (docsContainer) {
                        docsContainer.style.display = hasResults ? 'block' : 'none';
                    }
                    if (noResults) {
                        noResults.style.display = hasResults ? 'none' : 'block';
                    }
                });
            }

            document.querySelectorAll('.doc-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.doc-header').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.collapse-icon').forEach(el => el.classList.remove('active'));

            const firstDocHeader = document.querySelector('.doc-header');
            if (firstDocHeader) {
                firstDocHeader.classList.add('active');
                firstDocHeader.nextElementSibling.classList.add('active');
                const firstIcon = firstDocHeader.querySelector('.collapse-icon');
                if (firstIcon) {
                    firstIcon.classList.add('active');
                }
            }
        });
    </script>
@endsection