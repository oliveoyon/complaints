@extends('admin.layouts.admin-layout')

@section('title', 'Departments')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Departments</h2>
        @can('Create Department')
            <button class="btn btn-primary" id="addDepartmentBtn">
                <i class="fas fa-plus"></i> Add Department
            </button>
        @endcan
    </div>

    <table class="table table-striped" id="departmentsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Created At</th>
                @canany(['Edit Department', 'Delete Department'])
                    <th>Actions</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($departments as $department)
                <tr id="department-{{ $department->id }}">
                    <td>{{ $department->id }}</td>
                    <td class="department-name">{{ $department->name }}</td>
                    <td>{{ $department->description }}</td>
                    <td>{{ $department->created_at->format('Y-m-d') }}</td>
                    @canany(['Edit Department', 'Delete Department'])
                        <td>
                            @can('Edit Department')
                                <button class="btn btn-sm btn-info editBtn" data-id="{{ $department->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            @can('Delete Department')
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $department->id }}">
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

<!-- Modal for Add/Edit -->
<div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="departmentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="departmentModalLabel">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="departmentId">
                    <div class="mb-3">
                        <label for="departmentName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="departmentName" name="name" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="departmentDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="departmentDescription" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveDepartmentBtn">Save</button>
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
    const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
    const addBtn = document.getElementById('addDepartmentBtn');
    const form = document.getElementById('departmentForm');
    const nameInput = document.getElementById('departmentName');
    const descriptionInput = document.getElementById('departmentDescription');
    const departmentIdInput = document.getElementById('departmentId');
    const nameError = document.getElementById('nameError');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Open modal for Add
    addBtn.addEventListener('click', () => {
        form.reset();
        departmentIdInput.value = '';
        document.getElementById('departmentModalLabel').textContent = 'Add Department';
        nameError.textContent = '';
        nameInput.classList.remove('is-invalid');
        modal.show();
    });

    // Open modal for Edit
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`/admin/departments/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    departmentIdInput.value = data.id;
                    nameInput.value = data.name;
                    descriptionInput.value = data.description;
                    document.getElementById('departmentModalLabel').textContent = 'Edit Department';
                    nameError.textContent = '';
                    nameInput.classList.remove('is-invalid');
                    modal.show();
                });
        });
    });

    // Save department (create or update)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        nameError.textContent = '';
        nameInput.classList.remove('is-invalid');

        const id = departmentIdInput.value;
        const url = id ? `/admin/departments/${id}` : '/admin/departments';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                name: nameInput.value,
                description: descriptionInput.value
            })
        })
        .then(async res => {
            if (res.status === 422) {
                const data = await res.json();
                nameError.textContent = data.errors.name ? data.errors.name[0] : '';
                nameInput.classList.add('is-invalid');
            } else {
                return res.json();
            }
        })
        .then(data => {
            if (data) {
                const rowId = `department-${data.department.id}`;
                const rowHtml = `
<tr id="${rowId}">
    <td>${data.department.id}</td>
    <td class="department-name">${data.department.name}</td>
    <td>${data.department.description}</td>
    <td>${data.department.created_at.split('T')[0]}</td>
    <td>
        <button class="btn btn-sm btn-info editBtn" data-id="${data.department.id}"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.department.id}"><i class="fas fa-trash"></i></button>
    </td>
</tr>`;

                if (id) {
                    document.getElementById(rowId).outerHTML = rowHtml;
                } else {
                    document.querySelector('#departmentsTable tbody').insertAdjacentHTML('beforeend', rowHtml);
                }
                modal.hide();
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
            }
        });
    });

    // Delete department
    document.addEventListener('click', function(e) {
        if (e.target.closest('.deleteBtn')) {
            const id = e.target.closest('.deleteBtn').dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the department!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/departments/${id}`, {
                        method: 'DELETE',
                        headers: {'X-CSRF-TOKEN': token}
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire('Deleted!', data.message, 'success').then(() => {
                            document.getElementById(`department-${id}`).remove();
                        });
                    });
                }
            });
        }
    });
});
</script>
@endpush
