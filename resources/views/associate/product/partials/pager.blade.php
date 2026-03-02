<div class="pagination-dataTable">
    <button class="button-left" type="button"
        data-page="{{ $productAssociate->currentPage() - 1 }}"
        @disabled($productAssociate->onFirstPage())
    >
        <i class="bi bi-chevron-left"></i>
    </button>

    <button class="button-right" type="button"
        data-page="{{ $productAssociate->currentPage() + 1 }}"
        @disabled(!$productAssociate->hasMorePages())
    >
        <i class="bi bi-chevron-right"></i>
    </button>

    <p>
        {{ $productAssociate->firstItem() }}-{{ $productAssociate->lastItem() }}
        de {{ $productAssociate->total() }}
    </p>
</div>
