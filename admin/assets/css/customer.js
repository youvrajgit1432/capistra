function toggleMinorGuardian() {
    const applicantType = document.querySelector('input[name="applicant_type"]:checked').value;
    const guardianSection = document.getElementById("guardianDetails");
    if (applicantType === "Minor") {
        guardianSection.style.display = "block";
    } else {
        guardianSection.style.display = "none";
    }
}

function toggleMarriedChildren() {
    const maritalStatus = document.querySelector('select[name="marital_status"]').value;
    const childrenSection = document.getElementById("childrenDetails");
    if (maritalStatus === "Married") {
        childrenSection.style.display = "block";
    } else {
        childrenSection.style.display = "none";
    }
}
jQuery(document).ready(function () {
        $('.date-picker').nepaliDatePicker();
    })
    document.querySelector("form").addEventListener("submit", function(event) {
        // Get the values of the required fields
        var dateOfBirthBS = document.querySelector("input[name='date1']").value;
        var issuedPlace = document.querySelector("input[name='issued_place']").value;

        // Check if either field is empty
        if (dateOfBirthBS === "" || issuedPlace === "") {
            event.preventDefault(); // Prevent form submission
            alert("Please fill in both Date of Birth (BS) and Issued Place.");
        }
    });