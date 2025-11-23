import { authAPI, furnitureAPI } from './api.js';

let currentUser = null;
let currentEditId = null;
let pendingDeleteId = null;

// Delete confirmation modal setup
const deleteModal = document.getElementById('deleteConfirmModal');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');

if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', async () => {
        if (pendingDeleteId !== null) {
            try {
                await furnitureAPI.delete(pendingDeleteId);
                deleteModal.classList.remove('show');
                document.body.style.overflow = '';
                await loadFurniture();
                pendingDeleteId = null;
            } catch (error) {
                console.error('Error deleting furniture:', error);
                alert('Error deleting furniture. Please try again.');
            }
        }
    });
}

if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.classList.remove('show');
        document.body.style.overflow = '';
        pendingDeleteId = null;
    });
}

if (deleteModal) {
    window.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            deleteModal.classList.remove('show');
            document.body.style.overflow = '';
            pendingDeleteId = null;
        }
    });
}

// Check authentication and initialize
async function initAdminFurniture() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated || authResult.user.role !== 'admin') {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = authResult.user;
        displayUserInfo(currentUser);
        await loadFurniture();
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'index.html';
    }
}

// Display user info
function displayUserInfo(user) {
    const userInfo = document.getElementById('adminUserInfo');
    if (userInfo) {
        userInfo.textContent = `Admin: ${user.email}`;
    }
}

// Logout functionality
const logoutBtn = document.getElementById('adminLogoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            await authAPI.logout();
            window.location.href = 'index.html';
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = 'index.html';
        }
    });
}

// Load furniture
async function loadFurniture(filters = {}) {
    try {
        const furniture = await furnitureAPI.getAll(filters);
        displayFurniture(furniture);
    } catch (error) {
        console.error('Error loading furniture:', error);
        document.getElementById('adminFurnitureList').innerHTML = 
            '<p class="loading">Error loading furniture. Please try again.</p>';
    }
}

// Display furniture
function displayFurniture(furniture) {
    const furnitureList = document.getElementById('adminFurnitureList');

    if (!furniture || furniture.length === 0) {
        furnitureList.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🛋️</div>
                <h3>No furniture items</h3>
                <p>Add your first furniture item!</p>
            </div>
        `;
        return;
    }

    furnitureList.innerHTML = furniture.map(item => `
        <div class="furniture-card">
            <div class="furniture-image">
                ${item.image_url && item.image_url.trim() !== '' ? 
                    `<img src="${item.image_url}" alt="${item.name}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" onload="this.style.display='block';">
                     <div class="no-image" style="display: none;">🛋️</div>` : 
                    '<div class="no-image">🛋️</div>'}
            </div>
            <div class="furniture-content">
                <div class="furniture-category">${item.category} ${!item.is_active ? '(Inactive)' : ''}</div>
                <h3>${item.name}</h3>
                <p class="furniture-description">${item.description}</p>
                <div class="furniture-details">
                    <p><strong>Measurements:</strong> ${item.default_measurements}</p>
                    ${item.price_range ? `<p><strong>Price Range:</strong> ${item.price_range}</p>` : ''}
                    ${item.features ? `<p><strong>Features:</strong> ${item.features}</p>` : ''}
                </div>
                <div class="order-card-actions">
                    <button class="btn btn-edit" onclick="window.editFurniture(${item.id})">✏️ Edit</button>
                    <button class="btn btn-delete" onclick="window.deleteFurniture(${item.id})">🗑️ Delete</button>
                </div>
            </div>
        </div>
    `).join('');
}

// Image upload variables
let selectedImageFile = null;
let uploadedImageUrl = null;

// Image upload functionality
const imageInput = document.getElementById('furnitureImage');
const selectImageBtn = document.getElementById('selectImageBtn');
const imagePreview = document.getElementById('imagePreview');
const imagePreviewImg = document.getElementById('imagePreviewImg');
const imageFileName = document.getElementById('imageFileName');
const removeImageBtn = document.getElementById('removeImageBtn');
const furnitureImageUrl = document.getElementById('furnitureImageUrl');

if (selectImageBtn && imageInput) {
    selectImageBtn.addEventListener('click', () => {
        imageInput.click();
    });
}

if (imageInput) {
    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            selectedImageFile = file;
            imageFileName.textContent = file.name;
            
            // Show preview
            const reader = new FileReader();
            reader.onload = (event) => {
                imagePreviewImg.src = event.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
            
            // Clear URL input when file is selected
            if (furnitureImageUrl) {
                furnitureImageUrl.value = '';
            }
        }
    });
}

if (removeImageBtn) {
    removeImageBtn.addEventListener('click', () => {
        selectedImageFile = null;
        uploadedImageUrl = null;
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.style.display = 'none';
        if (imageFileName) imageFileName.textContent = '';
        if (imagePreviewImg) imagePreviewImg.src = '';
    });
}

// Upload image function
async function uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    
    try {
        const response = await fetch('php/upload-image.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Upload failed');
        }
        
        const result = await response.json();
        return result.image_url;
    } catch (error) {
        console.error('Image upload error:', error);
        throw error;
    }
}

// Modal management
const addFurnitureBtn = document.getElementById('addFurnitureBtn');
const furnitureModal = document.getElementById('furnitureModal');
const furnitureForm = document.getElementById('furnitureForm');
const closeModal = document.querySelector('#furnitureModal .close');
const cancelBtn = document.getElementById('cancelFurniture');

if (addFurnitureBtn) {
    addFurnitureBtn.addEventListener('click', (e) => {
        e.preventDefault();
        // Redirect to add furniture page
        window.location.href = 'add-furniture.html';
    });
} else {
    console.error('Add furniture button not found!');
}

if (closeModal) {
    closeModal.addEventListener('click', () => {
        furnitureModal.classList.remove('show');
    });
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
        furnitureModal.classList.remove('show');
    });
}

window.addEventListener('click', (e) => {
    if (e.target === furnitureModal) {
        furnitureModal.classList.remove('show');
    }
});

// Edit furniture - redirect to edit page
window.editFurniture = function(furnitureId) {
    furnitureId = parseInt(furnitureId);
    if (isNaN(furnitureId)) {
        alert('Invalid furniture ID');
        return;
    }
    window.location.href = `edit-furniture.html?id=${furnitureId}`;
};

// Delete furniture
window.deleteFurniture = function(furnitureId) {
    furnitureId = parseInt(furnitureId);
    pendingDeleteId = furnitureId;
    deleteConfirmMessage.textContent = 'Are you sure you want to delete this furniture item? This action cannot be undone.';
    deleteModal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Form submission
if (furnitureForm) {
    furnitureForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Form submitted');
        const errorDiv = document.getElementById('furnitureError');
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove('show');
        }

        // Validate required fields
        const name = document.getElementById('furnitureName')?.value.trim();
        const category = document.getElementById('furnitureCategory')?.value;
        const description = document.getElementById('furnitureDescription')?.value.trim();
        const measurements = document.getElementById('furnitureMeasurements')?.value.trim();

        if (!name || !category || !description || !measurements) {
            if (errorDiv) {
                errorDiv.textContent = 'Please fill in all required fields (Name, Category, Description, Measurements)';
                errorDiv.classList.add('show');
            }
            return;
        }

        // Disable submit button
        const submitBtn = furnitureForm.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : 'Save Furniture';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
        }

        try {
            let imageUrl = '';
            
            // Upload image if file is selected
            if (selectedImageFile) {
                try {
                    console.log('Uploading image...');
                    imageUrl = await uploadImage(selectedImageFile);
                    uploadedImageUrl = imageUrl;
                    console.log('Image uploaded:', imageUrl);
                } catch (uploadError) {
                    console.error('Image upload error:', uploadError);
                    if (errorDiv) {
                        errorDiv.textContent = uploadError.message || 'Failed to upload image. Please try again.';
                        errorDiv.classList.add('show');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                    return;
                }
            } else {
                // Use URL input if no file selected
                const urlInput = document.getElementById('furnitureImageUrl');
                imageUrl = urlInput ? urlInput.value.trim() : '';
            }

            const furnitureData = {
                name: name,
                category: category,
                description: description,
                default_measurements: measurements,
                price_range: document.getElementById('furniturePriceRange')?.value.trim() || '',
                features: document.getElementById('furnitureFeatures')?.value.trim() || '',
                image_url: imageUrl,
                is_active: document.getElementById('furnitureIsActive')?.checked || true
            };

            console.log('Saving furniture:', furnitureData);

            if (currentEditId) {
                furnitureData.id = currentEditId;
                await furnitureAPI.update(furnitureData);
                console.log('Furniture updated successfully');
            } else {
                await furnitureAPI.create(furnitureData);
                console.log('Furniture created successfully');
            }

            if (furnitureModal) {
                furnitureModal.classList.remove('show');
                furnitureModal.style.display = 'none';
            }
            if (furnitureForm) {
                furnitureForm.reset();
            }
            selectedImageFile = null;
            uploadedImageUrl = null;
            if (imagePreview) imagePreview.style.display = 'none';
            if (imageFileName) imageFileName.textContent = '';
            if (imageInput) imageInput.value = '';
            await loadFurniture();
        } catch (error) {
            console.error('Error saving furniture:', error);
            if (errorDiv) {
                errorDiv.textContent = error.message || 'Error saving furniture. Please try again.';
                errorDiv.classList.add('show');
            } else {
                alert(error.message || 'Error saving furniture. Please try again.');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    });
} else {
    console.error('Furniture form not found!');
}

// Filter functionality
const categoryFilter = document.getElementById('adminCategoryFilter');
const clearFiltersBtn = document.getElementById('clearAdminCatalogFilters');

if (categoryFilter) {
    categoryFilter.addEventListener('change', () => {
        applyFilters();
    });
}

if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
        categoryFilter.value = 'all';
        loadFurniture();
    });
}

function applyFilters() {
    const filters = {
        category: categoryFilter.value
    };
    loadFurniture(filters);
}

// Initialize when page loads
initAdminFurniture();




