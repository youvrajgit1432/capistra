document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default
    document.getElementById('investment_date').valueAsDate = new Date();
    
    // Initialize investment model fields
    toggleInvestmentFields();
    
    // Add event listeners
    document.getElementById('business_investment_model').addEventListener('change', toggleInvestmentFields);
    
    // Form validation
    document.getElementById('businessForm').addEventListener('submit', function(e) {
        if(!validateBusinessForm()) {
            e.preventDefault();
        }
    });
});

// Toggle fields based on investment model
function toggleInvestmentFields() {
    const model = document.getElementById('business_investment_model').value;
    
    // Hide all fields first
    document.getElementById('equity_fields').style.display = 'none';
    document.getElementById('debt_fields').style.display = 'none';
    document.getElementById('profit_sharing_fields').style.display = 'none';
    
    // Show relevant fields
    if(model === 'equity') {
        document.getElementById('equity_fields').style.display = 'block';
    } else if(model === 'debt') {
        document.getElementById('debt_fields').style.display = 'block';
    } else if(model === 'profit_sharing') {
        document.getElementById('profit_sharing_fields').style.display = 'block';
    }
}

// Update shareholder rights checkboxes
function updateShareholderRights() {
    const checkboxes = document.querySelectorAll('.rights-checkboxes input[type="checkbox"]');
    const selectedRights = [];
    
    checkboxes.forEach(checkbox => {
        if(checkbox.checked) {
            selectedRights.push(checkbox.value);
        }
    });
    
    document.getElementById('shareholder_rights_text').value = selectedRights.join(', ');
}

// Validate business form before submission
function validateBusinessForm() {
    let isValid = true;
    const model = document.getElementById('business_investment_model').value;
    
    // Common required fields
    const commonFields = ['business_name', 'business_type', 'invested_amount'];
    commonFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if(!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Model-specific validation
    if(model === 'equity') {
        const equityPct = parseFloat(document.querySelector('[name="equity_percentage"]').value);
        if(isNaN(equityPct) || equityPct <= 0 || equityPct > 100) {
            document.querySelector('[name="equity_percentage"]').classList.add('is-invalid');
            isValid = false;
        }
    } 
    else if(model === 'debt') {
        const loanAmount = parseFloat(document.querySelector('[name="loan_amount"]').value);
        const interestRate = parseFloat(document.querySelector('[name="interest_rate"]').value);
        const repaymentPeriod = parseFloat(document.querySelector('[name="repayment_period"]').value);
        
        if(isNaN(loanAmount) || loanAmount <= 0) {
            document.querySelector('[name="loan_amount"]').classList.add('is-invalid');
            isValid = false;
        }
        if(isNaN(interestRate) || interestRate <= 0) {
            document.querySelector('[name="interest_rate"]').classList.add('is-invalid');
            isValid = false;
        }
        if(isNaN(repaymentPeriod) || repaymentPeriod <= 0) {
            document.querySelector('[name="repayment_period"]').classList.add('is-invalid');
            isValid = false;
        }
    }
    else if(model === 'profit_sharing') {
        const profitShare = parseFloat(document.querySelector('[name="profit_share_percentage"]').value);
        if(isNaN(profitShare) || profitShare <= 0 || profitShare > 100) {
            document.querySelector('[name="profit_share_percentage"]').classList.add('is-invalid');
            isValid = false;
        }
    }
    
    // Validate investment amount
    const amount = parseFloat(document.getElementById('invested_amount').value);
    if(isNaN(amount) || amount <= 0) {
        document.getElementById('invested_amount').classList.add('is-invalid');
        isValid = false;
    }
    
    if(!isValid) {
        alert('Please fill all required fields with valid values');
    }
    
    return isValid;
}