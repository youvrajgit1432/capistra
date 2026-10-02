<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepali Date Picker</title>

    <!-- Include Nepali Date Picker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nepali-datepicker@2.2.0/css/nepali.datepicker.min.css">

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Include Nepali Date Picker JS -->
    <script src="https://cdn.jsdelivr.net/npm/nepali-datepicker@2.2.0/js/nepali.datepicker.min.js"></script>
</head>
<body>
    <form action="process.php" method="POST">
        <div class="row">
            <div class="col">
                <label>Date of Birth (BS)</label>
                <input type="text" id="date1" name="date1" class="form-control" placeholder="yyyy-mm-dd">
            </div>
            <div class="col">
                <label>Date of Birth (AD)</label>
                <input type="date" name="dob_ad" class="form-control">
            </div>
        </div>
        <button type="submit">Submit</button>
    </form>

    <!-- Initialize the Nepali Date Picker -->
    <script>
        $(document).ready(function () {
            $('#date1').nepaliDatePicker({
                dateFormat: "YYYY-MM-DD", // Set date format
                closeOnDateSelect: true   // Close picker on selection
            });
        });
    </script>
</body>
</html>
