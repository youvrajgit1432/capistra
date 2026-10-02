document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default
    document.getElementById('investment_date').valueAsDate = new Date();
    
    // Form validation
    document.getElementById('realEstateForm').addEventListener('submit', function(e) {
        if(!validateRealEstateForm()) {
            e.preventDefault();
        }
    });
    
    // Image upload validation
    document.querySelector('[name="property_images[]"]').addEventListener('change', function() {
        validatePropertyImages(this);
    });
});

// Validate property images
function validatePropertyImages(input) {
    const files = input.files;
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/png'];
    let isValid = true;
    
    if(files.length > 5) {
        alert('You can upload maximum 5 images');
        input.value = '';
        isValid = false;
    }
    
    for(let i = 0; i < files.length; i++) {
        if(files[i].size > maxSize) {
            alert(`File ${files[i].name} is too large (max 2MB)`);
            isValid = false;
            break;
        }
        
        if(!allowedTypes.includes(files[i].type)) {
            alert(`File ${files[i].name} must be JPEG or PNG`);
            isValid = false;
            break;
        }
    }
    
    if(!isValid) {
        input.value = '';
    }
    
    return isValid;
}

// Validate real estate form before submission
function validateRealEstateForm() {
    let isValid = true;
    
    // Required fields
    const requiredFields = [
        'property_type', 'property_location', 'property_size',
        'ownership_type', 'invested_amount'
    ];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if(!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validate property size
    const size = parseFloat(document.getElementById('property_size').value);
    if(isNaN(size) || size <= 0) {
        document.getElementById('property_size').classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate investment amount
    const amount = parseFloat(document.getElementById('invested_amount').value);
    if(isNaN(amount) || amount <= 0) {
        document.getElementById('invested_amount').classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate purchase document if uploaded
    const purchaseDoc = document.querySelector('[name="purchase_document"]');
    if(purchaseDoc.files.length > 0) {
        const file = purchaseDoc.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        
        if(file.size > maxSize) {
            alert('Purchase document must be less than 5MB');
            isValid = false;
        }
        
        if(!allowedTypes.includes(file.type)) {
            alert('Purchase document must be PDF, JPEG or PNG');
            isValid = false;
        }
    }
    
    if(!isValid) {
        alert('Please fill all required fields with valid values');
    }
    
    return isValid;
}