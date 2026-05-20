@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">

                    <div class="card-header d-flex justify-content-between">
                        <h4>Edit Product</h4>
                    </div>

                    <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data"
                        id="productForm">

                        @csrf
                        @method('PUT')

                        <div class="card-body">

                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" id="categorySelect" class="form-control">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="attributeManager" style="display:none;" class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <small class="text-muted fw-semibold">Category Attributes:</small>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="openAddAttributeModal()">
                                        + Add New Attribute
                                    </button>
                                </div>

                                <div id="existingAttributesList" class="d-flex flex-wrap" style="gap:8px;"></div>
                            </div>

                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Product Title"
                                    value="{{ old('title', $product->title) }}">
                            </div>

                            <div class="form-group">
                                <label>Brand</label>
                                <input type="text" name="brand" class="form-control" placeholder="Brand Name"
                                    value="{{ old('brand', $product->brand) }}">
                            </div>

                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" name="price" class="form-control" step="0.01"
                                    placeholder="Product Price" value="{{ old('price', $product->price) }}">
                            </div>

                            <div class="form-group">
                                <label>Discount Price</label>
                                <input type="number" name="discount_price" class="form-control" step="0.01"
                                    placeholder="Discount Price"
                                    value="{{ old('discount_price', $product->discount_price) }}">
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Thumbnail <small class="text-muted">(stored in products table)</small></label>
                                <input type="file" name="thumbnail" id="thumbnailInput" class="form-control"
                                    accept="image/*">
                                <div id="thumbnailPreview" class="mt-2"></div>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Specifications</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="addSpecificationRow()">
                                    + Add Specification
                                </button>
                            </div>

                            <div id="specifications-area"></div>

                            <hr>
                            <h5>Variants</h5>

                            <div id="variant-area"></div>

                            <button type="button" class="btn btn-primary mt-3" onclick="addVariant()">
                                + Add Variant
                            </button>

                            <div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:1080;"></div>

                        </div>

                        <button class="btn btn-success m-3">Update Product</button>

                    </form>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="addAttributeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add New Attribute</h5>
                    <button type="button" class="close" onclick="closeModal('addAttributeModal')" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group mb-3">
                        <label class="fw-semibold">Attribute Name <span class="text-danger">*</span></label>
                        <input type="text" id="newAttrName" class="form-control"
                            placeholder="e.g. RAM, Color, Size, Storage">
                    </div>

                    <div class="form-group mb-3">
                        <label class="fw-semibold">Display Type</label>
                        <select id="newAttrDisplayType" class="form-control" onchange="syncAttributeOptionHexFields()">
                            <option value="chip">Chip</option>
                            <option value="swatch">Swatch</option>
                        </select>
                    </div>

                    <label class="fw-semibold">Options</label>
                    <small class="text-muted d-block mb-2">
                        Add values now, or create the attribute first and add options later.
                    </small>

                    <div id="optionsList"></div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="addOptionRow()">
                        + Add Option
                    </button>

                    <div id="attrModalError" class="alert alert-danger mt-3" style="display:none;"></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('addAttributeModal')">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveAttrBtn" onclick="saveAttribute()">
                        Save Attribute
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="addOptionModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Add Option - <span id="addOptionAttrLabel" class="text-primary"></span>
                    </h5>
                    <button type="button" class="close" onclick="closeModal('addOptionModal')" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <label class="fw-semibold">Option Value <span class="text-danger">*</span></label>
                    <input type="text" id="newOptionValue" class="form-control mt-1" placeholder="e.g. 8GB, Red, XL">

                    <div class="mt-3" id="hexCodeWrapper" style="display:none;">
                        <label class="fw-semibold">Hex Code</label>
                        <input type="text" id="newOptionHexCode" class="form-control mt-1 text-uppercase"
                            placeholder="#000000">
                    </div>

                    <div id="optionModalError" class="alert alert-danger mt-2" style="display:none;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('addOptionModal')">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveOptionBtn" onclick="saveOption()">
                        Save Option
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        // ============================================
        // UTILITY FUNCTIONS
        // ============================================

        function getModalInstance(el) {
            if (!el) return null;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            }
            if (window.jQuery) {
                return $(el).data('bs.modal') || $(el).data('modal') || null;
            }
            return null;
        }

        function openModal(id) {
            const el = document.getElementById(id);
            if (!el) return;

            // Native JavaScript modal opening
            el.style.display = 'block';
            el.classList.add('show');
            document.body.classList.add('modal-open');

            // Create backdrop if not exists
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (!el) return;

            // Native JavaScript modal closing
            el.style.display = 'none';
            el.classList.remove('show');
            document.body.classList.remove('modal-open');

            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        function getModalInstance(el) {
            return el;
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `alert alert-${type} shadow-sm rounded my-2`;
            toast.style.minWidth = '220px';
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.2s ease-in-out';
            toast.innerHTML = message;

            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.addEventListener('transitionend', () => toast.remove(), {
                    once: true
                });
            }, 2800);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // ============================================
        // GLOBAL VARIABLES
        // ============================================

        let categories = @json($categories);
        let variantIndex = 0;
        let selectedCategoryId = null;
        let addingOptionForAttrId = null;
        let optionRowIndex = 0;
        let specificationIndex = 0;
        let editingProduct = @json($product);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        // ============================================
        // CATEGORY & ATTRIBUTE FUNCTIONS
        // ============================================

        function currentCategory() {
            return categories.find(c => c.id == selectedCategoryId);
        }

        function updateLocalCategory(updatedCategory) {
            const idx = categories.findIndex(c => c.id == updatedCategory.id);
            if (idx !== -1) {
                categories[idx] = updatedCategory;
            }
        }

        async function removeCategoryAttribute(attributeId, attributeName) {
            if (!selectedCategoryId) return;
            if (!confirm(`Remove attribute "${attributeName}" from this category?`)) return;

            const res = await fetch(`/categories/${selectedCategoryId}/attributes/${attributeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await res.json();
            if (!res.ok) {
                alert(data.message || 'Unable to remove attribute.');
                return;
            }

            updateLocalCategory(data.category);
            renderAttributeList();
            refreshProductImageTagSelectors();
        }

        function getTaggableOptions() {
            const category = currentCategory();
            if (!category?.attributes?.length) return [];

            return category.attributes.flatMap((attr) =>
                (attr.options || []).map((option) => ({
                    id: option.id,
                    label: `${attr.name}: ${option.value}`,
                    displayType: attr.display_type || 'chip',
                    hexCode: option.hex_code || '',
                }))
            );
        }

        function buildImageTagOptions(selectedValue = '') {
            const options = getTaggableOptions();
            const items = ['<option value="">No tag</option>'];

            options.forEach((option) => {
                const selected = String(selectedValue) === String(option.id) ? 'selected' : '';
                items.push(`<option value="${option.id}" ${selected}>${option.label}</option>`);
            });

            return items.join('');
        }

        function refreshProductImageTagSelectors() {
            document.querySelectorAll('.product-image-tag-select').forEach((select) => {
                const currentValue = select.value;
                select.innerHTML = buildImageTagOptions(currentValue);
            });
        }

        function renderAttributeList() {
            const cat = currentCategory();
            const listDiv = document.getElementById('existingAttributesList');
            listDiv.innerHTML = '';

            if (!cat?.attributes?.length) {
                listDiv.innerHTML =
                    '<small class="text-muted">No attributes yet. Add one to start building variants.</small>';
                return;
            }

            cat.attributes.forEach((attr) => {
                const badges = (attr.options || []).map((o) => {
                    const hex = o.hex_code ?
                        `<span class="badge border text-dark" style="background-color:${o.hex_code};">${o.hex_code}</span>` :
                        '';
                    return `<span class="badge bg-white text-dark border">${o.value}</span>${hex}`;
                }).join(' ');

                listDiv.insertAdjacentHTML('beforeend', `
                    <div class="border rounded px-3 py-2 bg-light" style="min-width:180px;">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <strong style="font-size:13px;">${attr.name}</strong>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm py-0 px-1"
                                        style="font-size:11px;line-height:1;"
                                        onclick="removeCategoryAttribute(${attr.id}, '${attr.name.replace(/'/g, "\\'")}')">
                                    &times;
                                </button>
                                <span class="badge bg-info text-dark">${attr.display_type || 'chip'}</span>
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between align-items-center gap-2">
                            <div class="d-flex flex-wrap" style="gap:3px;">
                                ${badges || '<small class="text-muted">No options yet</small>'}
                            </div>
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm py-0 px-1"
                                    style="font-size:11px;white-space:nowrap;"
                                    onclick="openAddOptionModal(${attr.id}, '${attr.name.replace(/'/g, "\\'")}')">
                                + Option
                            </button>
                        </div>
                    </div>
                `);
            });
        }

        // ============================================
        // ATTRIBUTE MODAL FUNCTIONS
        // ============================================

        function openAddAttributeModal() {
            document.getElementById('newAttrName').value = '';
            document.getElementById('newAttrDisplayType').value = 'chip';
            document.getElementById('optionsList').innerHTML = '';
            document.getElementById('attrModalError').style.display = 'none';
            optionRowIndex = 0;

            addOptionRow();
            openModal('addAttributeModal');
        }

        function addOptionRow() {
            const idx = optionRowIndex++;
            document.getElementById('optionsList').insertAdjacentHTML('beforeend', `
                <div class="row mb-2 align-items-center" id="optRow_${idx}">
                    <div class="col-7">
                        <input type="text" class="form-control attr-option-value"
                               placeholder="Option value (e.g. 8GB, Red, XL)">
                    </div>
                    <div class="col-4 attr-option-hex-column" style="display:none;">
                        <input type="text" class="form-control attr-option-hex text-uppercase"
                               placeholder="#000000">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-outline-danger"
                                onclick="document.getElementById('optRow_${idx}').remove()">x</button>
                    </div>
                </div>
            `);
            syncAttributeOptionHexFields();
        }

        function syncAttributeOptionHexFields() {
            const isSwatch = document.getElementById('newAttrDisplayType').value === 'swatch';
            document.querySelectorAll('.attr-option-hex-column').forEach((column) => {
                column.style.display = isSwatch ? '' : 'none';
            });
        }

        async function saveAttribute() {
            const errorDiv = document.getElementById('attrModalError');
            const btn = document.getElementById('saveAttrBtn');
            errorDiv.style.display = 'none';

            const name = document.getElementById('newAttrName').value.trim();
            const displayType = document.getElementById('newAttrDisplayType').value;

            if (!name) {
                errorDiv.textContent = 'Attribute name is required.';
                errorDiv.style.display = 'block';
                return;
            }

            const optionRows = Array.from(document.querySelectorAll('#optionsList .row'));
            const options = optionRows.map((row) => ({
                value: row.querySelector('.attr-option-value')?.value.trim() || '',
                hex_code: row.querySelector('.attr-option-hex')?.value.trim() || '',
            })).filter((option) => option.value);

            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const res = await fetch(`/categories/${selectedCategoryId}/attributes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        display_type: displayType,
                        options
                    }),
                });

                const data = await res.json();

                if (!res.ok) {
                    errorDiv.textContent = data.message || 'Something went wrong.';
                    errorDiv.style.display = 'block';
                    return;
                }

                updateLocalCategory(data.category);
                renderAttributeList();
                refreshProductImageTagSelectors();
                closeModal('addAttributeModal');
                showToast('Attribute added successfully');
            } catch (e) {
                errorDiv.textContent = 'Server error: ' + e.message;
                errorDiv.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save Attribute';
            }
        }

        function openAddOptionModal(attrId, attrName) {
            addingOptionForAttrId = attrId;
            document.getElementById('addOptionAttrLabel').textContent = attrName;
            document.getElementById('newOptionValue').value = '';
            document.getElementById('newOptionHexCode').value = '';
            document.getElementById('optionModalError').style.display = 'none';

            const attribute = currentCategory()?.attributes?.find((attr) => attr.id == attrId);
            document.getElementById('hexCodeWrapper').style.display = attribute?.display_type === 'swatch' ? '' : 'none';

            openModal('addOptionModal');
        }

        async function saveOption() {
            const val = document.getElementById('newOptionValue').value.trim();
            const hexCode = document.getElementById('newOptionHexCode').value.trim();
            const errorDiv = document.getElementById('optionModalError');
            const btn = document.getElementById('saveOptionBtn');
            errorDiv.style.display = 'none';

            if (!val) {
                errorDiv.textContent = 'Option value is required.';
                errorDiv.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const res = await fetch(
                    `/categories/${selectedCategoryId}/attributes/${addingOptionForAttrId}/options`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            value: val,
                            hex_code: hexCode
                        }),
                    });

                const data = await res.json();

                if (!res.ok) {
                    errorDiv.textContent = data.message || 'Something went wrong.';
                    errorDiv.style.display = 'block';
                    return;
                }

                updateLocalCategory(data.category);
                renderAttributeList();
                refreshProductImageTagSelectors();
                addingOptionForAttrId = null;
                closeModal('addOptionModal');
                showToast('Option added successfully');
            } catch (e) {
                errorDiv.textContent = 'Server error: ' + e.message;
                errorDiv.style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save Option';
            }
        }

        // ============================================
        // VARIANT FUNCTIONS
        // ============================================

        function addVariant(variant = null) {
            if (!selectedCategoryId) {
                alert('Please select a category first.');
                return;
            }

            const category = currentCategory();
            const idx = variantIndex;

            // Create a map of attribute_option_id to attribute_id for easier matching
            const selectedOptionsMap = new Map();
            if (variant && variant.attribute_options) {
                variant.attribute_options.forEach(opt => {
                    selectedOptionsMap.set(opt.attribute_id, opt.id);
                });
            }

            let attributesHtml = '';

            category.attributes.forEach((attr) => {
                const selectedOptionId = selectedOptionsMap.get(attr.id) || '';

                let opts = '<option value="">Select</option>';

                (attr.options || []).forEach((opt) => {
                    const selected = String(selectedOptionId) === String(opt.id) ? 'selected' : '';
                    opts += `<option value="${opt.id}" ${selected}>${opt.value}</option>`;
                });

                attributesHtml += `
                    <div class="col-md-4 mb-2">
                        <label>${attr.name}</label>
                        <select name="variants[${idx}][attribute_options][${attr.id}]" class="form-control variant-attribute-select" data-attr-id="${attr.id}">
                            ${opts}
                        </select>
                    </div>
                `;
            });

            const defaultChecked = variant?.is_default ? 'checked' : '';
            const skuValue = variant?.sku ? escapeHtml(variant.sku) : '';
            const priceValue = variant?.price ? variant.price : '';
            const discountValue = variant?.discount_price ? variant.discount_price : '';
            const stockValue = variant?.stock ? variant.stock : '';
            const variantId = variant?.id ? variant.id : '';

            let existingImagesHtml = '';
            let deletedImagesInput = '';

            if (variant && variant.images && variant.images.length) {
                existingImagesHtml = variant.images.map((img, imgIdx) => `
            <div class="position-relative existing-image-item" style="display:inline-block;margin:5px;" data-image-id="${img.id}">
                <img src="${img.image}"
                     style="width:70px;height:70px;object-fit:cover;
                            border-radius:6px;border:1px solid #ddd">
                <input type="hidden" name="variants[${idx}][existing_images][]" value="${img.id}">
                <button type="button" class="btn btn-sm btn-danger position-absolute"
                        style="top:-5px;right:-5px;padding:0 5px;font-size:12px;"
                        onclick="markImageForDeletion(this, ${idx}, ${img.id})">×</button>
            </div>
        `).join('');

                // Hidden input to track deleted images
                deletedImagesInput =
                    `<input type="hidden" name="variants[${idx}][deleted_images][]" id="deleted_images_${idx}" value="">`;
            }

            document.getElementById('variant-area').insertAdjacentHTML('beforeend', `
        <div class="card mt-3 p-3 border" id="variant-block-${idx}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Variant #${idx + 1}</strong>
                <button type="button" class="btn btn-sm btn-danger"
                        onclick="removeVariant(${idx})">Remove</button>
            </div>

            <input type="hidden" name="variants[${idx}][id]" value="${variantId}">
            ${deletedImagesInput}

            <div class="row">
                <div class="col-md-3">
                    <label>SKU</label>
                    <input type="text" name="variants[${idx}][sku]"
                           class="form-control" placeholder="SKU" value="${skuValue}">
                </div>
                <div class="col-md-2">
                    <label>Price</label>
                    <input type="number" step="0.01" name="variants[${idx}][price]"
                           class="form-control" placeholder="0" value="${priceValue}">
                </div>
                <div class="col-md-2">
                    <label>Discount Price</label>
                    <input type="number" step="0.01" name="variants[${idx}][discount_price]"
                           class="form-control" placeholder="0" value="${discountValue}">
                </div>
                <div class="col-md-2">
                    <label>Stock</label>
                    <input type="number" name="variants[${idx}][stock]"
                           class="form-control" placeholder="0" value="${stockValue}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mt-4">
                        <input class="form-check-input default-variant-checkbox"
                               type="checkbox"
                               name="variants[${idx}][is_default]"
                               value="1"
                               id="variant-default-${idx}"
                               onchange="toggleDefaultVariant(${idx})" ${defaultChecked}>
                        <label class="form-check-label" for="variant-default-${idx}">
                            Set as default
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mt-3">${attributesHtml}</div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <label>Variant Images <small class="text-muted">(multiple)</small></label>
                    <input type="file"
                           name="variants[${idx}][new_images][]"
                           class="form-control" multiple accept="image/*"
                           onchange="previewVariantImages(this, ${idx})">
                    <div id="variantPreview_${idx}"
                         class="d-flex flex-wrap mt-1" style="gap:6px;">${existingImagesHtml}</div>
                </div>
            </div>
        </div>
    `);

            variantIndex++;
        }

        // Function to mark image for deletion
        function markImageForDeletion(button, variantIdx, imageId) {
            const imageDiv = button.closest('.existing-image-item');
            const deletedImagesInput = document.getElementById(`deleted_images_${variantIdx}`);

            if (imageDiv) {
                // Hide the image from UI
                imageDiv.style.display = 'none';

                // Add image ID to deleted images list
                let deletedIds = deletedImagesInput.value ? deletedImagesInput.value.split(',') : [];
                if (!deletedIds.includes(String(imageId))) {
                    deletedIds.push(imageId);
                    deletedImagesInput.value = deletedIds.join(',');
                }

                // Also remove from existing_images hidden input
                const existingImagesInput = imageDiv.querySelector('input[name*="existing_images"]');
                if (existingImagesInput) {
                    existingImagesInput.remove();
                }

                // Show toast notification
                showToast('Image marked for deletion', 'info');
            }
        }

        function toggleDefaultVariant(activeIndex) {
            document.querySelectorAll('.default-variant-checkbox').forEach((checkbox) => {
                if (checkbox.id !== `variant-default-${activeIndex}`) {
                    checkbox.checked = false;
                }
            });
        }

        function previewVariantImages(input, idx) {
            const preview = document.getElementById(`variantPreview_${idx}`);

            // Keep existing images, just add new previews
            Array.from(input.files).forEach((file) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.insertAdjacentHTML('beforeend', `
                        <img src="${e.target.result}"
                             style="width:70px;height:70px;object-fit:cover;
                                    border-radius:6px;border:1px solid #ddd;margin:5px;"
                             title="${file.name}">
                    `);
                };
                reader.readAsDataURL(file);
            });
        }

        function removeVariant(idx) {
            // Find all deleted images inputs in this variant
            const variantBlock = document.getElementById(`variant-block-${idx}`);
            if (variantBlock) {
                const deletedImagesInput = variantBlock.querySelector(`input[name="variants[${idx}][deleted_images][]"]`);
                if (deletedImagesInput && deletedImagesInput.value) {
                    // Keep the deleted images info - it will be sent to server
                    console.log('Variant removed with deleted images:', deletedImagesInput.value);
                }
            }
            variantBlock?.remove();
        }

        // ============================================
        // SPECIFICATION FUNCTIONS
        // ============================================

        function addSpecificationRow(label = '', value = '') {
            const idx = specificationIndex++;

            document.getElementById('specifications-area').insertAdjacentHTML('beforeend', `
                <div class="row mb-2" id="specification-row-${idx}">
                    <div class="col-md-4">
                        <input type="text"
                               name="specifications[${idx}][label]"
                               class="form-control"
                               placeholder="Label e.g. Material, Display, Camera"
                               value="${escapeHtml(label)}">
                    </div>
                    <div class="col-md-7">
                        <input type="text"
                               name="specifications[${idx}][value]"
                               class="form-control"
                               placeholder="Value e.g. 100% Cotton"
                               value="${escapeHtml(value)}">
                    </div>
                    <div class="col-md-1">
                        <button type="button"
                                class="btn btn-outline-danger"
                                onclick="document.getElementById('specification-row-${idx}').remove()">
                            x
                        </button>
                    </div>
                </div>
            `);
        }

        // ============================================
        // IMAGE PREVIEW FUNCTIONS
        // ============================================

        document.getElementById('thumbnailInput')?.addEventListener('change', function() {
            const preview = document.getElementById('thumbnailPreview');
            preview.innerHTML = '';
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `
                    <img src="${e.target.result}"
                         style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd">
                `;
            };
            reader.readAsDataURL(file);
        });

        const productImagesInput = document.getElementById('productImages');
        if (productImagesInput) {
            productImagesInput.addEventListener('change', function() {
                const preview = document.getElementById('productImagePreview');
                preview.innerHTML = '';

                Array.from(this.files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.insertAdjacentHTML('beforeend', `
                            <div class="card p-2 mb-2" style="max-width:180px;display:inline-block;margin-right:10px;">
                                <img src="${e.target.result}"
                                     style="width:100%;height:100px;object-fit:cover;border-radius:8px;border:1px solid #ddd">
                                <div class="mt-2">
                                    <label class="small mb-1">Image Tag</label>
                                    <select name="product_image_attribute_option_ids[${index}]"
                                            class="form-control form-control-sm product-image-tag-select">
                                        ${buildImageTagOptions()}
                                    </select>
                                </div>
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }

        // ============================================
        // CATEGORY CHANGE HANDLER
        // ============================================

        document.getElementById('categorySelect').addEventListener('change', function() {
            selectedCategoryId = this.value;
            variantIndex = 0;
            document.getElementById('variant-area').innerHTML = '';

            if (!selectedCategoryId) {
                document.getElementById('attributeManager').style.display = 'none';
                return;
            }

            document.getElementById('attributeManager').style.display = 'block';
            renderAttributeList();
            refreshProductImageTagSelectors();
        });

        // ============================================
        // INITIALIZE EDIT FORM
        // ============================================

        function initializeProductForm() {
            if (!editingProduct) {
                addSpecificationRow();
                return;
            }

            // Set category and trigger change
            selectedCategoryId = editingProduct.category_id;
            const categorySelect = document.getElementById('categorySelect');
            categorySelect.value = selectedCategoryId;
            categorySelect.dispatchEvent(new Event('change'));

            // Show existing thumbnail
            if (editingProduct.thumbnail) {
                document.getElementById('thumbnailPreview').innerHTML = `
                    <img src="${editingProduct.thumbnail}"
                         style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd">
                `;
            }

            // Load specifications after a short delay to ensure attributes are loaded
            setTimeout(() => {
                // Specifications
                document.getElementById('specifications-area').innerHTML = '';
                specificationIndex = 0;

                if (editingProduct.specifications && editingProduct.specifications.length) {
                    editingProduct.specifications.forEach((spec) => {
                        addSpecificationRow(spec.label || '', spec.value || '');
                    });
                } else {
                    addSpecificationRow();
                }

                // Variants
                if (editingProduct.variants && editingProduct.variants.length) {
                    editingProduct.variants.forEach((variant) => addVariant(variant));
                }
            }, 200);
        }

        // Start the initialization
        initializeProductForm();
    </script>
@endsection
