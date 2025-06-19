<?php
session_start();
require 'db.php';

// Fetch student details
$registration_number = $_SESSION['registration_number'] ?? '';
$student = [
    'name' => '',
    'registration_number' => '',
    'email' => '',
    'department' => '',
    'year_of_study' => ''
];
if ($registration_number) {
    $sql = "SELECT * FROM users WHERE registration_number = :registration_number";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['registration_number' => $registration_number]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $student = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PTU - Career Guidance Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #ffffff;
    }
    .container {
      width: 85%;
      margin: 0 auto;
      background-color: #ffffff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .header {
      background-color: #c40d0d;
      color: #ffffff;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .header .logo {
      width: 170px;
      height: auto;
      margin-right: 20px;
    }
    .header-text {
      text-align: center;
      font-size: 25px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      color: #660000;
      font-weight: bold;
    }
    input[type="text"],
    input[type="email"],
    select,
    textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #660000;
      border-radius: 4px;
      box-sizing: border-box;
    }
    textarea {
      height: 60px;
      resize: vertical;
    }
    .section {
      border: 1px solid #c40d0d;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
    }
    .section-title {
      background-color: #c40d0d;
      color: #ffffff;
      padding: 8px;
      margin: -15px -15px 15px -15px;
      border-radius: 5px 5px 0 0;
    }
    .checkbox-group, .radio-group {
      margin-bottom: 10px;
    }
    .checkbox-group label, .radio-group label {
      font-weight: normal;
      color: #000;
      margin-right: 20px;
    }
    button {
      background-color: #c40d0d;
      color: #ffffff;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
      margin-top: 20px;
    }
    button:hover {
      background-color: #660000;
    }
    .hidden-section {
      display: none;
      margin-left: 20px;
      padding: 10px;
      background-color: #f9f9f9;
      border-radius: 4px;
      border-left: 3px solid #c40d0d;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="assets/ptu-logo.png" alt="PTU Logo" class="logo" />
      <div class="header-text">
        <h2>Puducherry Technological University</h2>
        <h3>Career Guidance Form</h3>
      </div>
    </div>
    <form action="submit_career_guidance.php" method="POST">
      <div class="section">
        <div class="section-title">Personal Information</div>
        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required />
        </div>
        <div class="form-group">
          <label>Reg. No</label>
          <input type="text" name="reg_no" value="<?php echo htmlspecialchars($student['registration_number']); ?>" required />
        </div>
        <div class="form-group">
          <label>Email id</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required />
        </div>
        <div class="form-group">
          <label>Dept</label>
          <input type="text" name="dept" value="<?php echo htmlspecialchars($student['department']); ?>" required />
        </div>
        <div class="form-group">
       <label for="year_of_study">Year of Study</label>
       <select id="year_of_study" name="year_of_study" required>
        <option value="">Select Year</option>
        <option value="1st Year">1st Year</option>
        <option value="2nd Year">2nd Year</option>
        <option value="3rd Year">3rd Year</option>
        <option value="4th Year">4th Year</option>
      </select>
      </div>
        <div class="form-group">
          <label>Sem</label>
          <input type="text" name="sem" required />
        </div>
      </div>
      <div class="section">
        <div class="section-title">I am interested in: <span style="font-style:italic;">(You may select multiple options)</span></div>
        <div class="checkbox-group">
          <label><input type="checkbox" name="interest[]" value="Higher Education" onclick="toggleSection('higher_education_section', this)"> Higher Education</label>
          <div id="higher_education_section" class="hidden-section">
            <label>Preferred Country/University:</label>
            <input type="text" name="preferred_university" />
            <label>Preferred Course:</label>
            <input type="text" name="preferred_course" />
          </div>
          <label><input type="checkbox" name="interest[]" value="Job" onclick="toggleSection('job_section', this)"> Job</label>
          <div id="job_section" class="hidden-section">
            <label>Area of Interest / Role:</label>
            <input type="text" name="job_role" />
            <label>Companies you're targeting:</label>
            <input type="text" name="job_companies" />
          </div>
          <label><input type="checkbox" name="interest[]" value="Entrepreneurship" onclick="toggleSection('entrepreneurship_section', this)"> Entrepreneurship</label>
          <div id="entrepreneurship_section" class="hidden-section">
            <label>Idea Stage:</label>
            <div class="radio-group">
              <label><input type="radio" name="idea_stage" value="Not yet decided"> Not yet decided</label>
              <label><input type="radio" name="idea_stage" value="Have an idea"> Have an idea</label>
              <label><input type="radio" name="idea_stage" value="Working on it"> Working on it</label>
            </div>
            <label>Sector/Field:</label>
            <input type="text" name="entrepreneurship_sector" />
          </div>
          <label><input type="checkbox" name="interest[]" value="Start-up" onclick="toggleSection('startup_section', this)"> Start-up</label>
          <div id="startup_section" class="hidden-section">
            <label>Co-founders?</label>
            <div class="radio-group">
              <label><input type="radio" name="cofounders" value="Yes"> Yes</label>
              <label><input type="radio" name="cofounders" value="No"> No</label>
            </div>
            <label>Details:</label>
            <input type="text" name="startup_details" />
          </div>
        </div>
      </div>
      <div class="section">
        <div class="section-title">Additional Support Needed <span style="font-style:italic;">(Tick all that apply)</span></div>
        <div class="checkbox-group">
          <label><input type="checkbox" name="support[]" value="Resume Building"> Resume Building</label>
          <label><input type="checkbox" name="support[]" value="Interview Preparation"> Interview Preparation</label>
          <label><input type="checkbox" name="support[]" value="Higher Education Counselling"> Higher Education Counselling</label>
          <label><input type="checkbox" name="support[]" value="Technical Skill Training"> Technical Skill Training</label>
          <label><input type="checkbox" name="support[]" value="Start-up Mentoring"> Start-up Mentoring</label>
          <label><input type="checkbox" name="support[]" value="Networking Opportunities"> Networking Opportunities</label>
          <label><input type="checkbox" name="support[]" value="Others"> Others (please specify):</label>
          <input type="text" name="support_others" />
        </div>
      </div>
      <button type="submit">Submit</button>
    </form>
  </div>
  <script>
    function toggleSection(sectionId, checkbox) {
      document.getElementById(sectionId).style.display = checkbox.checked ? 'block' : 'none';
    }
  </script>
</body>
</html>