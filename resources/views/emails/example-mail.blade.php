<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome Email</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #ddd;
    }
    .header {
      text-align: center;
      background-color: #007bff;
      color: white;
      padding: 20px;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
    }
    .footer {
      text-align: center;
      font-size: 12px;
      color: #777;
      margin-top: 20px;
    }
    .company-logo {
      max-width: 150px;
      margin: 0 auto 20px;
    }
    .welcome-message {
      margin-top: 20px;
    }
    .button {
      margin: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Header Section -->
    <div class="header">
      <img src="https://via.placeholder.com/150" alt="Company Logo" class="company-logo">
      <h2>Welcome to [Company Name]</h2>
    </div>

    <!-- Body Section -->
    <div class="welcome-message">
      <p>Dear [Employee Name],</p>
      <p>
        We are thrilled to welcome you to the team at <strong>[Company Name]</strong>! As you start your journey with us,
        we want to extend our warmest greetings and ensure you feel comfortable in your new role.
      </p>

      <p>
        At <strong>[Company Name]</strong>, we pride ourselves on fostering a collaborative and innovative work environment.
        We are confident that your skills, enthusiasm, and unique perspective will be a great addition to our mission.
      </p>

      <p>
        Here are your initial details:
        <ul>
          <li><strong>Employee ID:</strong> [Employee ID]</li>
          <li><strong>Department:</strong> [Department]</li>
          <li><strong>Joining Date:</strong> [Joining Date]</li>
        </ul>
      </p>

      <p>We look forward to seeing you thrive and grow with us!</p>
    </div>

    <!-- Button Section -->
    <div class="button">
      <a href="[HRMS Portal Link]" class="btn btn-outline-primary">Access HR Portal</a>
    </div>

    <!-- Closing Section -->
    <div class="welcome-message">
      <p>
        If you have any questions, feel free to reach out to HR at <a href="mailto:hr@company.com">hr@company.com</a>.
      </p>
      <p>Best regards,</p>
      <p><strong>[Your Name]</strong><br>[Your Position]<br>[Company Name]</p>
    </div>

    <!-- Footer Section -->
    <div class="footer">
      <p>&copy; {{date('Y')}} Fixing Dots, All rights reserved.</p>
      <p><a href="[Company Website]">Visit our website</a> | <a href="mailto:hr@company.com">Contact Us</a></p>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
message.txt
4 KB
