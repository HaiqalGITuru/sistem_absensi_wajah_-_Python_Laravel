@if ($paginator->hasPages())
    <div class="pager">
        <span class="muted">
            Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            &middot; {{ $paginator->total() }} data
        </span>

        <div class="pager-links">
            @if ($paginator->onFirstPage())
                <span class="btn btn-light btn-sm disabled">&laquo; Sebelumnya</span>
            @else
                <a class="btn btn-light btn-sm" href="{{ $paginator->previousPageUrl() }}">&laquo; Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="btn btn-light btn-sm" href="{{ $paginator->nextPageUrl() }}">Berikutnya &raquo;</a>
            @else
                <span class="btn btn-light btn-sm disabled">Berikutnya &raquo;</span>
            @endif
        </div>
    </div>
@endif