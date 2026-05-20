<div class="modal fade" id="{{ $id ?? 'deleteModal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">{{ $title ?? 'Confirm Delete' }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="{{ $nameId ?? 'name' }}"></strong>?</p>
                <p class="text-danger mb-0">This action cannot be undone.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>

                <form id="{{ $formId ?? 'deleteForm' }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </form>
            </div>

        </div>
    </div>
</div>
