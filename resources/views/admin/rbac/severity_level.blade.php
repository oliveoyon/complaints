@extends('admin.layouts.admin-layout')

@section('title', 'Severity Levels')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Severity Levels</h2>
        @can('Create Severity Level')
            <button class="btn btn-primary" id="addLevelBtn">
                <i class="fas fa-plus"></i> Add Level
            </button>
        @endcan
    </div>

    <table class="table table-striped" id="levelsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Priority</th>
                @canany(['Edit Severity Level', 'Delete Severity Level'])
                    <th>Actions</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($levels as $level)
                <tr id="level-{{ $level->id }}">
                    <td>{{ $level->id }}</td>
                    <td class="level-name">{{ $level->name }}</td>
                    <td>{{ $level->description }}</td>
                    <td>{{ $level->priority }}</td>
                    @canany(['Edit Severity Level', 'Delete Severity Level'])
                        <td>
                            @can('Edit Severity Level')
                                <button class="btn btn-sm btn-info editBtn" data-id="{{ $level->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('Delete Severity Level')
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $level->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    @endcanany
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal (Add/Edit) -->
<div class="modal fade" id="levelModal" tabindex="-1" aria-labelledby="levelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="levelForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="levelModalLabel">Add Level</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="levelId">
                    <div class="mb-3">
                        <label for="levelName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="levelName" name="name" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="levelDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="levelDescription" name="description"></textarea>
                        <div class="invalid-feedback" id="descriptionError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="levelPriority" class="form-label">Priority</label>
                        <input type="number" class="form-control" id="levelPriority" name="priority" min="1" required>
                        <div class="invalid-feedback" id="priorityError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveLevelBtn">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('levelModal'));
    const addBtn = document.getElementById('addLevelBtn');
    const form = document.getElementById('levelForm');
    const nameInput = document.getElementById('levelName');
    const descriptionInput = document.getElementById('levelDescription');
    const priorityInput = document.getElementById('levelPriority');
    const levelIdInput = document.getElementById('levelId');
    const nameError = document.getElementById('nameError');
    const descriptionError = document.getElementById('descriptionError');
    const priorityError = document.getElementById('priorityError');

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Open modal for Add
    addBtn.addEventListener('click', () => {
        form.reset();
        levelIdInput.value = '';
        document.getElementById('levelModalLabel').textContent = 'Add Level';
        [nameError, descriptionError, priorityError].forEach(el => el.textContent = '');
        [nameInput, descriptionInput, priorityInput].forEach(el => el.classList.remove('is-invalid'));
        modal.show();
    });

    // Open modal for Edit
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`/admin/severity-levels/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    levelIdInput.value = data.id;
                    nameInput.value = data.name;
                    descriptionInput.value = data.description;
                    priorityInput.value = data.priority;
                    document.getElementById('levelModalLabel').textContent = 'Edit Level';
                    [nameError, descriptionError, priorityError].forEach(el => el.textContent = '');
                    [nameInput, descriptionInput, priorityInput].forEach(el => el.classList.remove('is-invalid'));
                    modal.show();
                });
        });
    });

    // Save Level (create/update)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        [nameError, descriptionError, priorityError].forEach(el => el.textContent = '');
        [nameInput, descriptionInput, priorityInput].forEach(el => el.classList.remove('is-invalid'));

        const id = levelIdInput.value;
        const url = id ? `/admin/severity-levels/${id}` : '/admin/severity-levels';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                name: nameInput.value,
                description: descriptionInput.value,
                priority: priorityInput.value
            })
        })
        .then(async res => {
            if (res.status === 422) {
                const data = await res.json();
                if(data.errors.name) { nameError.textContent = data.errors.name[0]; nameInput.classList.add('is-invalid'); }
                if(data.errors.description) { descriptionError.textContent = data.errors.description[0]; descriptionInput.classList.add('is-invalid'); }
                if(data.errors.priority) { priorityError.textContent = data.errors.priority[0]; priorityInput.classList.add('is-invalid'); }
            } else {
                return res.json();
            }
        })
        .then(data => {
            if (data) {
                const rowId = `level-${data.level.id}`;
                const rowHtml = `
<tr id="${rowId}">
    <td>${data.level.id}</td>
    <td class="level-name">${data.level.name}</td>
    <td>${data.level.description}</td>
    <td>${data.level.priority}</td>
    <td>
        <button class="btn btn-sm btn-info editBtn" data-id="${data.level.id}"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.level.id}"><i class="fas fa-trash"></i></button>
    </td>
</tr>`;
                if(id) {
                    document.getElementById(rowId).outerHTML = rowHtml;
                } else {
                    document.querySelector('#levelsTable tbody').insertAdjacentHTML('beforeend', rowHtml);
                }
                modal.hide();
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
            }
        });
    });

    // Delete Level
    document.addEventListener('click', function(e) {
        if(e.target.closest('.deleteBtn')) {
            const id = e.target.closest('.deleteBtn').dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the severity level!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if(result.isConfirmed) {
                    fetch(`/admin/severity-levels/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token }
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            document.getElementById(`level-${id}`).remove();
                        });
                    });
                }
            });
        }
    });
});
</script>
@endpush
