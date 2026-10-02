document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default
    document.getElementById('investment_date').valueAsDate = new Date();
    
    // Form validation
    document.getElementById('loanForm').addEventListener('submit', function(e) {
        if(!validateLoanForm()) {
            e.preventDefault();
        }
    });
    
    // Add input validation
    document.getElementById('interest_rate').addEventListener('blur', validateInterestRate);
    document.getElementById('loan_duration').addEventListener('blur', validateLoanDuration);
});

// Validate interest rate
function validateInterestRate() {
    const rate = parseFloat(this.value);
    if(isNaN(rate) || rate < 0 || rate > 50) {
        this.classList.add('is-invalid');
        return false;
    } else {
        this.classList.remove('is-invalid');
        return true;
    }
}

// Validate loan duration
function validateLoanDuration() {
    const duration = parseInt(this.value);
    if(isNaN(duration) || duration < 1 || duration > 360) {
        this.classList.add('is-invalid');
        return false;
    } else {
        this.classList.remove('is-invalid');
        return true;
    }
}

// Validate loan form before submission
function validateLoanForm() {
    let isValid = true;
    
    // Required fields
    const requiredFields = [
        'borrower_name', 'loan_type', 'invested_amount',
        'interest_rate', 'loan_duration', 'repayment_schedule'
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
    
    // Validate financial values
    const amount = parseFloat(document.getElementById('invested_amount').value);
    if(isNaN(amount) || amount <= 0) {
        document.getElementById('invested_amount').classList.add('is-invalid');
        isValid = false;
    }
    
    // Validate interest rate and duration
    isValid = validateInterestRate.call(document.getElementById('interest_rate')) && isValid;
    isValid = validateLoanDuration.call(document.getElementById('loan_duration')) && isValid;
    
    if(!isValid) {
        alert('Please fill all required fields with valid values');
    }
    
    return isValid;
}