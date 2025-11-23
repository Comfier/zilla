# Image Upload Guide

## Overview
The furniture ordering system now supports image uploads for furniture items. Admins can upload images directly from their computer or use image URLs.

## Features

### 1. Image Upload
- **Supported Formats**: JPEG, JPG, PNG, GIF, WebP
- **Maximum Size**: 5MB per image
- **Storage Location**: `images/furniture/` directory
- **Automatic Naming**: Images are automatically renamed with unique identifiers

### 2. Image URL Option
- Admins can also enter image URLs if images are hosted elsewhere
- Useful for external image hosting services

### 3. Image Preview
- Real-time preview of selected images before upload
- Shows existing images when editing furniture items

## How to Use

### For Admins:

1. **Navigate to Admin Furniture Management**
   - Go to Admin Panel → Manage Furniture

2. **Add/Edit Furniture**
   - Click "Add New Furniture" or "Edit" on an existing item

3. **Upload Image**
   - Click "📷 Choose Image" button
   - Select an image file from your computer
   - Preview will appear automatically
   - Or enter an image URL in the "Image URL" field

4. **Save Furniture**
   - Click "Save Furniture"
   - Image will be uploaded automatically (if file selected)
   - Image URL will be saved to database

5. **Remove Image**
   - Click "Remove Image" button to clear selected image
   - Or leave URL field empty to remove image

## Technical Details

### File Structure
```
project/
├── images/
│   └── furniture/
│       └── (uploaded images)
├── php/
│   └── upload-image.php (upload handler)
└── js/
    └── admin-furniture.js (upload logic)
```

### Security Features
- ✅ Admin authentication required
- ✅ File type validation (only images)
- ✅ File size limit (5MB)
- ✅ Unique filename generation
- ✅ Secure file storage

### Image Display
- Images are displayed in:
  - Homepage catalog
  - Client catalog
  - Admin furniture management
- Fallback to placeholder icon if image fails to load
- Lazy loading for better performance

## Troubleshooting

### Image Not Uploading
1. Check file size (must be under 5MB)
2. Verify file format (JPEG, PNG, GIF, WebP only)
3. Check `images/furniture/` directory permissions (should be writable)
4. Check PHP upload settings in `php.ini`

### Image Not Displaying
1. Verify image URL is correct
2. Check file path (should be relative: `images/furniture/filename.jpg`)
3. Check browser console for errors
4. Verify image file exists in `images/furniture/` directory

### Permission Issues
- Ensure `images/furniture/` directory has write permissions (755 or 777)
- On Windows/XAMPP, this is usually automatic
- On Linux, you may need: `chmod 755 images/furniture/`

## Best Practices

1. **Image Optimization**
   - Compress images before uploading for faster loading
   - Recommended size: 800x600px to 1200x900px
   - Use JPEG for photos, PNG for graphics

2. **File Naming**
   - System auto-generates unique names
   - Original filename is not preserved for security

3. **Storage**
   - Images are stored locally in `images/furniture/`
   - Consider backing up this directory regularly
   - For production, consider cloud storage (AWS S3, etc.)

4. **Performance**
   - Images use lazy loading
   - Consider CDN for high-traffic sites

## Future Enhancements

- [ ] Image cropping/resizing on upload
- [ ] Multiple images per furniture item
- [ ] Image gallery view
- [ ] Cloud storage integration
- [ ] Image compression on upload
- [ ] Drag-and-drop upload

