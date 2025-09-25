@extends('admin.layouts.admin-layout')

@section('title', 'Complaint Categories')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Complaint Categories</h2>
        @can('Create Complaint Category')
            <button class="btn btn-primary" id="addCategoryBtn">
                <i class="fas fa-plus"></i> Add Category
            </button>
        @endcan
    </div>

    <table class="table table-striped" id="categoriesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created At</th>
                @canany(['Edit Complaint Category','Delete Complaint Category','Create Complaint Category'])
                    <th>Actions</th>
                @endcanany
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
            <tr id="category-{{ $category->id }}">
                <td>{{ $category->id }}</td>
                <td class="category-name">{{ $category->name }}</td>
                <td>{{ $category->created_at->format('Y-m-d') }}</td>
                @canany(['Edit Complaint Category','Delete Complaint Category'])
                <td>
                    @can('Edit Complaint Category')
                        <button class="btn btn-sm btn-info editBtn" data-id="{{ $category->id }}">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endcan
                    @can('Delete Complaint Category')
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $category->id }}">
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
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="categoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="categoryId">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveCategoryBtn">Save</button>
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
    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const addBtn = document.getElementById('addCategoryBtn');
    const form = document.getElementById('categoryForm');
    const nameInput = document.getElementById('categoryName');
    const categoryIdInput = document.getElementById('categoryId');
    const nameError = document.getElementById('nameError');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Add Category
    addBtn.addEventListener('click', () => {
        form.reset();
        categoryIdInput.value = '';
        document.getElementById('categoryModalLabel').textContent = 'Add Category';
        nameError.textContent = '';
        nameInput.classList.remove('is-invalid');
        modal.show();
    });

    // Edit Category
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`/admin/complaint-categories/${id}/edit`)
                .then(res => res.json())
                .then(data => {
                    categoryIdInput.value = data.id;
                    nameInput.value = data.name;
                    document.getElementById('categoryModalLabel').textContent = 'Edit Category';
                    nameError.textContent = '';
                    nameInput.classList.remove('is-invalid');
                    modal.show();
                });
        });
    });

    // Save (Add/Update)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        nameError.textContent = '';
        nameInput.classList.remove('is-invalid');

        const id = categoryIdInput.value;
        const url = id ? `/admin/complaint-categories/${id}` : '/admin/complaint-categories';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ name: nameInput.value })
        })
        .then(async res => {
            if(res.status === 422){
                const data = await res.json();
                nameError.textContent = data.errors.name ? data.errors.name[0] : '';
                nameInput.classList.add('is-invalid');
            } else {
                return res.json();
            }
        })
        .then(data => {
            if(data){
                const rowId = `category-${data.category.id}`;
                const rowHtml = `
<tr id="${rowId}">
    <td>${data.category.id}</td>
    <td class="category-name">${data.category.name}</td>
    <td>${data.category.created_at.split('T')[0]}</td>
    <td>
        <button class="btn btn-sm btn-info editBtn" data-id="${data.category.id}"><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.category.id}"><i class="fas fa-trash"></i></button>
    </td>
</tr>`;
                if(id){
                    document.getElementById(rowId).outerHTML = rowHtml;
                } else {
                    document.querySelector('#categoriesTable tbody').insertAdjacentHTML('beforeend', rowHtml);
                }
                modal.hide();
                Swal.fire('Success', data.message, 'success').then(()=> location.reload());
            }
        });
    });

    // Delete Category
    document.addEventListener('click', function(e){
        if(e.target.closest('.deleteBtn')){
            const id = e.target.closest('.deleteBtn').dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the category!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result)=>{
                if(result.isConfirmed){
                    fetch(`/admin/complaint-categories/${id}`, {
                        method: 'DELETE',
                        headers: {'X-CSRF-TOKEN': token}
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire('Deleted!', data.message, 'success').then(()=>{
                            document.getElementById(`category-${id}`).remove();
                        });
                    });
                }
            });
        }
    });
});
</script>
@endpush
