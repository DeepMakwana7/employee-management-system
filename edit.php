<?php

$host = 'localhost';           // MySQL host
$db_name = 'employee_system';  // Database name
$db_user = 'root';             // MySQL username
$db_pass = '';                 // MySQL password


try {
    $conn = new PDO("mysql:host=$host;port=3307;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';
$employee = null;

// Get employee ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Fetch employee details
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $country_code = trim($_POST['country_code'] ?? '+91');
    $phone_number = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $custom_department = trim($_POST['custom_department'] ?? '');
    $salary = trim($_POST['salary']);

    // Combine country code and phone number
    $phone = !empty($phone_number) ? $country_code . $phone_number : '';

    // Handle custom department
    if ($department === 'OTHERS' && !empty($custom_department)) {
        $department = $custom_department;
    }

    // Validation
    $countryNames = [
        '+91' => 'india', '+1' => 'united states', '+44' => 'united kingdom', '+86' => 'china',
        '+81' => 'japan', '+49' => 'germany', '+33' => 'france', '+39' => 'italy',
        '+61' => 'australia', '+82' => 'south korea', '+65' => 'singapore', '+971' => 'uae',
        '+966' => 'saudi arabia'
    ];
    $countryName = $countryNames[$country_code] ?? 'country';

    $countryPhoneLengths = [
        '+91' => 10,
        '+1' => 10,
        '+44' => 10,
        '+86' => 11,
        '+81' => 10,
        '+49' => 11,
        '+33' => 9,
        '+39' => 10,
        '+61' => 9,
        '+82' => 9,
        '+65' => 8,
        '+971' => 9,
        '+966' => 9
    ];

    if (empty($name) || empty($email) || empty($department) || empty($salary)) {
        $error = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif (!empty($phone_number) && !preg_match('/^\d+$/', $phone_number)) {
        $error = "invalid phone number for {$countryName}";
    } elseif (!empty($phone_number) && isset($countryPhoneLengths[$country_code]) && strlen($phone_number) !== $countryPhoneLengths[$country_code]) {
        $error = "invalid phone number for {$countryName}";
    } elseif (!empty($phone) && !preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
        $error = "invalid phone number for {$countryName}";
    } elseif (!is_numeric($salary) || $salary < 0) {
        $error = 'Salary must be a valid positive number!';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, department = ?, salary = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $department, $salary, $id]);
            header("Location: index.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = 'Error updating employee: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Edit Employee</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #1f2937;
            --light: #f9fafb;
            --border: #e5e7eb;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .form-card {
            background: #1e293b;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            transition: gap 0.2s ease;
        }

        .back-link:hover {
            gap: 10px;
        }

        h1 {
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitle {
            color: #d1d5db;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        input::placeholder, select {
            color: #9ca3af;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }

        select option {
            background: #1e293b;
            color: #ffffff;
            padding: 8px;
        }

        select option:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Phone input group styling */
        .phone-input-group {
            display: flex;
            gap: 8px;
        }

        .phone-input-group select {
            flex: 0 0 200px;
            min-width: 200px;
            color: #ffffff;
        }

        .phone-input-group select option {
            color: #ffffff;
            background: #1e293b;
        }

        .phone-input-group select:focus {
            color: #ffffff;
        }

        .phone-input-group input[type="tel"] {
            flex: 1;
        }

        /* Hide spinner arrows on number inputs */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        .error-message {
            background: #451a1a;
            color: #f87171;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f43f5e;
            font-size: 14px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit {
            background: #6366f1;
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-submit:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }

        .btn-cancel {
            background: var(--border);
            color: var(--dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cancel:hover {
            background: #d1d5db;
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 24px;
            }

            h1 {
                font-size: 24px;
            }

            .button-group {
                flex-direction: column;
            }

            .phone-input-group {
                flex-direction: column;
                gap: 12px;
            }

            .phone-input-group select {
                flex: none;
                width: 100%;
            }

            .phone-input-group input[type="tel"] {
                flex: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <a href="index.php" class="back-link">← Back to Employees</a>

            <h1>✏️ Edit Employee</h1>
            <p class="subtitle">Update employee details</p>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="edit.php?id=<?php echo $id; ?>">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="phone-input-group">
                        <select id="country_code" name="country_code" onchange="updatePhonePrefix()">
                            <?php
                            $currentPhone = $employee['phone'] ?? '';
                            $selectedCountry = '+91'; // Default to India
                            
                            // Determine which country code is currently selected
                            $countryCodes = ['+91', '+1', '+44', '+86', '+81', '+49', '+33', '+39', '+34', '+31', '+32', '+41', '+46', '+47', '+45', '+358', '+48', '+420', '+421', '+36', '+40', '+359', '+30', '+351', '+353', '+43', '+386', '+385', '+387', '+389', '+381', '+382', '+383', '+355', '+7', '+380', '+375', '+994', '+374', '+995', '+996', '+992', '+993', '+998', '+66', '+84', '+855', '+856', '+95', '+60', '+65', '+62', '+63', '+673', '+670', '+92', '+880', '+94', '+977', '+975', '+93', '+968', '+971', '+973', '+974', '+966', '+965', '+962', '+961', '+963', '+964', '+98', '+90', '+972', '+970', '+20', '+212', '+213', '+216', '+218', '+27', '+52', '+54', '+55', '+56', '+57', '+58'];
                            foreach ($countryCodes as $code) {
                                if (strpos($currentPhone, $code) === 0) {
                                    $selectedCountry = $code;
                                    break;
                                }
                            }
                            ?>
                            <option value="+91" data-flag="in" <?php echo ($selectedCountry === '+91' ? 'selected' : ''); ?>>🇮🇳 India (+91)</option>
                            <option value="+1" data-flag="us" <?php echo ($selectedCountry === '+1' ? 'selected' : ''); ?>>🇺🇸 United States (+1)</option>
                            <option value="+1" data-flag="ca" <?php echo ($selectedCountry === '+1' && strpos($currentPhone, '+1') === 0 ? 'selected' : ''); ?>>🇨🇦 Canada (+1)</option>
                            <option value="+44" data-flag="gb" <?php echo ($selectedCountry === '+44' ? 'selected' : ''); ?>>🇬🇧 United Kingdom (+44)</option>
                            <option value="+61" data-flag="au" <?php echo ($selectedCountry === '+61' ? 'selected' : ''); ?>>🇦🇺 Australia (+61)</option>
                            <option value="+49" data-flag="de" <?php echo ($selectedCountry === '+49' ? 'selected' : ''); ?>>🇩🇪 Germany (+49)</option>
                            <option value="+33" data-flag="fr" <?php echo ($selectedCountry === '+33' ? 'selected' : ''); ?>>🇫🇷 France (+33)</option>
                            <option value="+39" data-flag="it" <?php echo ($selectedCountry === '+39' ? 'selected' : ''); ?>>🇮🇹 Italy (+39)</option>
                            <option value="+34" data-flag="es" <?php echo ($selectedCountry === '+34' ? 'selected' : ''); ?>>🇪🇸 Spain (+34)</option>
                            <option value="+31" data-flag="nl" <?php echo ($selectedCountry === '+31' ? 'selected' : ''); ?>>🇳🇱 Netherlands (+31)</option>
                            <option value="+32" data-flag="be" <?php echo ($selectedCountry === '+32' ? 'selected' : ''); ?>>🇧🇪 Belgium (+32)</option>
                            <option value="+41" data-flag="ch" <?php echo ($selectedCountry === '+41' ? 'selected' : ''); ?>>🇨🇭 Switzerland (+41)</option>
                            <option value="+46" data-flag="se" <?php echo ($selectedCountry === '+46' ? 'selected' : ''); ?>>🇸🇪 Sweden (+46)</option>
                            <option value="+47" data-flag="no" <?php echo ($selectedCountry === '+47' ? 'selected' : ''); ?>>🇳🇴 Norway (+47)</option>
                            <option value="+45" data-flag="dk" <?php echo ($selectedCountry === '+45' ? 'selected' : ''); ?>>🇩🇰 Denmark (+45)</option>
                            <option value="+358" data-flag="fi" <?php echo ($selectedCountry === '+358' ? 'selected' : ''); ?>>🇫🇮 Finland (+358)</option>
                            <option value="+48" data-flag="pl" <?php echo ($selectedCountry === '+48' ? 'selected' : ''); ?>>🇵🇱 Poland (+48)</option>
                            <option value="+420" data-flag="cz" <?php echo ($selectedCountry === '+420' ? 'selected' : ''); ?>>🇨🇿 Czech Republic (+420)</option>
                            <option value="+421" data-flag="sk" <?php echo ($selectedCountry === '+421' ? 'selected' : ''); ?>>🇸🇰 Slovakia (+421)</option>
                            <option value="+36" data-flag="hu" <?php echo ($selectedCountry === '+36' ? 'selected' : ''); ?>>🇭🇺 Hungary (+36)</option>
                            <option value="+40" data-flag="ro" <?php echo ($selectedCountry === '+40' ? 'selected' : ''); ?>>🇷🇴 Romania (+40)</option>
                            <option value="+359" data-flag="bg" <?php echo ($selectedCountry === '+359' ? 'selected' : ''); ?>>🇧🇬 Bulgaria (+359)</option>
                            <option value="+30" data-flag="gr" <?php echo ($selectedCountry === '+30' ? 'selected' : ''); ?>>🇬🇷 Greece (+30)</option>
                            <option value="+351" data-flag="pt" <?php echo ($selectedCountry === '+351' ? 'selected' : ''); ?>>🇵🇹 Portugal (+351)</option>
                            <option value="+353" data-flag="ie" <?php echo ($selectedCountry === '+353' ? 'selected' : ''); ?>>🇮🇪 Ireland (+353)</option>
                            <option value="+43" data-flag="at" <?php echo ($selectedCountry === '+43' ? 'selected' : ''); ?>>🇦🇹 Austria (+43)</option>
                            <option value="+386" data-flag="si" <?php echo ($selectedCountry === '+386' ? 'selected' : ''); ?>>🇸🇮 Slovenia (+386)</option>
                            <option value="+385" data-flag="hr" <?php echo ($selectedCountry === '+385' ? 'selected' : ''); ?>>🇭🇷 Croatia (+385)</option>
                            <option value="+387" data-flag="ba" <?php echo ($selectedCountry === '+387' ? 'selected' : ''); ?>>🇧🇦 Bosnia and Herzegovina (+387)</option>
                            <option value="+389" data-flag="mk" <?php echo ($selectedCountry === '+389' ? 'selected' : ''); ?>>🇲🇰 North Macedonia (+389)</option>
                            <option value="+381" data-flag="rs" <?php echo ($selectedCountry === '+381' ? 'selected' : ''); ?>>🇷🇸 Serbia (+381)</option>
                            <option value="+382" data-flag="me" <?php echo ($selectedCountry === '+382' ? 'selected' : ''); ?>>🇲🇪 Montenegro (+382)</option>
                            <option value="+383" data-flag="xk" <?php echo ($selectedCountry === '+383' ? 'selected' : ''); ?>>🇽🇰 Kosovo (+383)</option>
                            <option value="+355" data-flag="al" <?php echo ($selectedCountry === '+355' ? 'selected' : ''); ?>>🇦🇱 Albania (+355)</option>
                            <option value="+7" data-flag="ru" <?php echo ($selectedCountry === '+7' ? 'selected' : ''); ?>>🇷🇺 Russia (+7)</option>
                            <option value="+380" data-flag="ua" <?php echo ($selectedCountry === '+380' ? 'selected' : ''); ?>>🇺🇦 Ukraine (+380)</option>
                            <option value="+375" data-flag="by" <?php echo ($selectedCountry === '+375' ? 'selected' : ''); ?>>🇧🇾 Belarus (+375)</option>
                            <option value="+994" data-flag="az" <?php echo ($selectedCountry === '+994' ? 'selected' : ''); ?>>🇦🇿 Azerbaijan (+994)</option>
                            <option value="+374" data-flag="am" <?php echo ($selectedCountry === '+374' ? 'selected' : ''); ?>>🇦🇲 Armenia (+374)</option>
                            <option value="+995" data-flag="ge" <?php echo ($selectedCountry === '+995' ? 'selected' : ''); ?>>🇬🇪 Georgia (+995)</option>
                            <option value="+7" data-flag="kz" <?php echo ($selectedCountry === '+7' ? 'selected' : ''); ?>>🇰🇿 Kazakhstan (+7)</option>
                            <option value="+996" data-flag="kg" <?php echo ($selectedCountry === '+996' ? 'selected' : ''); ?>>🇰🇬 Kyrgyzstan (+996)</option>
                            <option value="+992" data-flag="tj" <?php echo ($selectedCountry === '+992' ? 'selected' : ''); ?>>🇹🇯 Tajikistan (+992)</option>
                            <option value="+993" data-flag="tm" <?php echo ($selectedCountry === '+993' ? 'selected' : ''); ?>>🇹🇲 Turkmenistan (+993)</option>
                            <option value="+998" data-flag="uz" <?php echo ($selectedCountry === '+998' ? 'selected' : ''); ?>>🇺🇿 Uzbekistan (+998)</option>
                            <option value="+86" data-flag="cn" <?php echo ($selectedCountry === '+86' ? 'selected' : ''); ?>>🇨🇳 China (+86)</option>
                            <option value="+81" data-flag="jp" <?php echo ($selectedCountry === '+81' ? 'selected' : ''); ?>>🇯🇵 Japan (+81)</option>
                            <option value="+82" data-flag="kr" <?php echo ($selectedCountry === '+82' ? 'selected' : ''); ?>>🇰🇷 South Korea (+82)</option>
                            <option value="+66" data-flag="th" <?php echo ($selectedCountry === '+66' ? 'selected' : ''); ?>>🇹🇭 Thailand (+66)</option>
                            <option value="+84" data-flag="vn" <?php echo ($selectedCountry === '+84' ? 'selected' : ''); ?>>🇻🇳 Vietnam (+84)</option>
                            <option value="+855" data-flag="kh" <?php echo ($selectedCountry === '+855' ? 'selected' : ''); ?>>🇰🇭 Cambodia (+855)</option>
                            <option value="+856" data-flag="la" <?php echo ($selectedCountry === '+856' ? 'selected' : ''); ?>>🇱🇦 Laos (+856)</option>
                            <option value="+95" data-flag="mm" <?php echo ($selectedCountry === '+95' ? 'selected' : ''); ?>>🇲🇲 Myanmar (+95)</option>
                            <option value="+60" data-flag="my" <?php echo ($selectedCountry === '+60' ? 'selected' : ''); ?>>🇲🇾 Malaysia (+60)</option>
                            <option value="+65" data-flag="sg" <?php echo ($selectedCountry === '+65' ? 'selected' : ''); ?>>🇸🇬 Singapore (+65)</option>
                            <option value="+62" data-flag="id" <?php echo ($selectedCountry === '+62' ? 'selected' : ''); ?>>🇮🇩 Indonesia (+62)</option>
                            <option value="+63" data-flag="ph" <?php echo ($selectedCountry === '+63' ? 'selected' : ''); ?>>🇵🇭 Philippines (+63)</option>
                            <option value="+673" data-flag="bn" <?php echo ($selectedCountry === '+673' ? 'selected' : ''); ?>>🇧🇳 Brunei (+673)</option>
                            <option value="+670" data-flag="tl" <?php echo ($selectedCountry === '+670' ? 'selected' : ''); ?>>🇹🇱 Timor-Leste (+670)</option>
                            <option value="+92" data-flag="pk" <?php echo ($selectedCountry === '+92' ? 'selected' : ''); ?>>🇵🇰 Pakistan (+92)</option>
                            <option value="+880" data-flag="bd" <?php echo ($selectedCountry === '+880' ? 'selected' : ''); ?>>🇧🇩 Bangladesh (+880)</option>
                            <option value="+94" data-flag="lk" <?php echo ($selectedCountry === '+94' ? 'selected' : ''); ?>>🇱🇰 Sri Lanka (+94)</option>
                            <option value="+977" data-flag="np" <?php echo ($selectedCountry === '+977' ? 'selected' : ''); ?>>🇳🇵 Nepal (+977)</option>
                            <option value="+975" data-flag="bt" <?php echo ($selectedCountry === '+975' ? 'selected' : ''); ?>>🇧🇹 Bhutan (+975)</option>
                            <option value="+93" data-flag="af" <?php echo ($selectedCountry === '+93' ? 'selected' : ''); ?>>🇦🇫 Afghanistan (+93)</option>
                            <option value="+968" data-flag="om" <?php echo ($selectedCountry === '+968' ? 'selected' : ''); ?>>🇴🇲 Oman (+968)</option>
                            <option value="+971" data-flag="ae" <?php echo ($selectedCountry === '+971' ? 'selected' : ''); ?>>🇦🇪 UAE (+971)</option>
                            <option value="+973" data-flag="bh" <?php echo ($selectedCountry === '+973' ? 'selected' : ''); ?>>🇧🇭 Bahrain (+973)</option>
                            <option value="+974" data-flag="qa" <?php echo ($selectedCountry === '+974' ? 'selected' : ''); ?>>🇶🇦 Qatar (+974)</option>
                            <option value="+966" data-flag="sa" <?php echo ($selectedCountry === '+966' ? 'selected' : ''); ?>>🇸🇦 Saudi Arabia (+966)</option>
                            <option value="+965" data-flag="kw" <?php echo ($selectedCountry === '+965' ? 'selected' : ''); ?>>🇰🇼 Kuwait (+965)</option>
                            <option value="+962" data-flag="jo" <?php echo ($selectedCountry === '+962' ? 'selected' : ''); ?>>🇯🇴 Jordan (+962)</option>
                            <option value="+961" data-flag="lb" <?php echo ($selectedCountry === '+961' ? 'selected' : ''); ?>>🇱🇧 Lebanon (+961)</option>
                            <option value="+963" data-flag="sy" <?php echo ($selectedCountry === '+963' ? 'selected' : ''); ?>>🇸🇾 Syria (+963)</option>
                            <option value="+964" data-flag="iq" <?php echo ($selectedCountry === '+964' ? 'selected' : ''); ?>>🇮🇶 Iraq (+964)</option>
                            <option value="+98" data-flag="ir" <?php echo ($selectedCountry === '+98' ? 'selected' : ''); ?>>🇮🇷 Iran (+98)</option>
                            <option value="+90" data-flag="tr" <?php echo ($selectedCountry === '+90' ? 'selected' : ''); ?>>🇹🇷 Turkey (+90)</option>
                            <option value="+972" data-flag="il" <?php echo ($selectedCountry === '+972' ? 'selected' : ''); ?>>🇮🇱 Israel (+972)</option>
                            <option value="+970" data-flag="ps" <?php echo ($selectedCountry === '+970' ? 'selected' : ''); ?>>🇵🇸 Palestine (+970)</option>
                            <option value="+20" data-flag="eg" <?php echo ($selectedCountry === '+20' ? 'selected' : ''); ?>>🇪🇬 Egypt (+20)</option>
                            <option value="+212" data-flag="ma" <?php echo ($selectedCountry === '+212' ? 'selected' : ''); ?>>🇲🇦 Morocco (+212)</option>
                            <option value="+213" data-flag="dz" <?php echo ($selectedCountry === '+213' ? 'selected' : ''); ?>>🇩🇿 Algeria (+213)</option>
                            <option value="+216" data-flag="tn" <?php echo ($selectedCountry === '+216' ? 'selected' : ''); ?>>🇹🇳 Tunisia (+216)</option>
                            <option value="+218" data-flag="ly" <?php echo ($selectedCountry === '+218' ? 'selected' : ''); ?>>🇱🇾 Libya (+218)</option>
                            <option value="+27" data-flag="za" <?php echo ($selectedCountry === '+27' ? 'selected' : ''); ?>>🇿🇦 South Africa (+27)</option>
                            <option value="+52" data-flag="mx" <?php echo ($selectedCountry === '+52' ? 'selected' : ''); ?>>🇲🇽 Mexico (+52)</option>
                            <option value="+54" data-flag="ar" <?php echo ($selectedCountry === '+54' ? 'selected' : ''); ?>>🇦🇷 Argentina (+54)</option>
                            <option value="+55" data-flag="br" <?php echo ($selectedCountry === '+55' ? 'selected' : ''); ?>>🇧🇷 Brazil (+55)</option>
                            <option value="+56" data-flag="cl" <?php echo ($selectedCountry === '+56' ? 'selected' : ''); ?>>🇨🇱 Chile (+56)</option>
                            <option value="+57" data-flag="co" <?php echo ($selectedCountry === '+57' ? 'selected' : ''); ?>>🇨🇴 Colombia (+57)</option>
                            <option value="+58" data-flag="ve" <?php echo ($selectedCountry === '+58' ? 'selected' : ''); ?>>🇻🇪 Venezuela (+58)</option>
                        </select>
                        <input type="tel" id="phone" name="phone" pattern="^\d{6,14}$" placeholder="9876543210" value="<?php 
                            $phone = $employee['phone'] ?? '';
                            if (!empty($phone)) {
                                // Extract phone number without country code
                                echo htmlspecialchars(preg_replace('/^\+\d+/', '', $phone));
                            }
                        ?>" title="Enter phone number without country code">
                    </div>
                </div>

                <div class="form-group">
                    <label for="department">Department *</label>
                    <select id="department" name="department" required onchange="toggleCustomDepartment()">
                        <option value="">Select Department</option>
                        <option value="Full Stack Developer" <?php echo $employee['department'] == 'Full Stack Developer' ? 'selected' : ''; ?>>Full Stack Developer</option>
                        <option value="Data Analyst" <?php echo $employee['department'] == 'Data Analyst' ? 'selected' : ''; ?>>Data Analyst</option>
                        <option value="Game Developer" <?php echo $employee['department'] == 'Game Developer' ? 'selected' : ''; ?>>Game Developer</option>
                        <option value="Blockchain Developer" <?php echo $employee['department'] == 'Blockchain Developer' ? 'selected' : ''; ?>>Blockchain Developer</option>
                        <option value="AI/ML Engineer" <?php echo $employee['department'] == 'AI/ML Engineer' ? 'selected' : ''; ?>>AI/ML Engineer</option>
                        <option value="DevOps Engineer" <?php echo $employee['department'] == 'DevOps Engineer' ? 'selected' : ''; ?>>DevOps Engineer</option>
                        <option value="OTHERS" <?php echo (in_array($employee['department'], ['Full Stack Developer', 'Data Analyst', 'Game Developer', 'Blockchain Developer', 'AI/ML Engineer', 'DevOps Engineer']) ? '' : 'selected'); ?>>OTHERS</option>
                    </select>
                </div>

                <div class="form-group" id="custom-department-group" style="display: <?php echo (in_array($employee['department'], ['Full Stack Developer', 'Data Analyst', 'Game Developer', 'Blockchain Developer', 'AI/ML Engineer', 'DevOps Engineer']) ? 'none' : 'block'); ?>;">
                    <label for="custom_department">Specify Department *</label>
                    <input type="text" id="custom_department" name="custom_department" value="<?php echo (in_array($employee['department'], ['Full Stack Developer', 'Data Analyst', 'Game Developer', 'Blockchain Developer', 'AI/ML Engineer', 'DevOps Engineer']) ? '' : htmlspecialchars($employee['department'])); ?>" placeholder="Enter department name" <?php echo (in_array($employee['department'], ['Full Stack Developer', 'Data Analyst', 'Game Developer', 'Blockchain Developer', 'AI/ML Engineer', 'DevOps Engineer']) ? '' : 'required'); ?>>
                </div>

                <div class="form-group">
                    <label for="salary">Annual Salary (₹) *</label>
                    <input type="number" id="salary" name="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>" step="0.01" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-submit">Update Employee</button>
                    <a href="index.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCustomDepartment() {
            const departmentSelect = document.getElementById('department');
            const customDepartmentGroup = document.getElementById('custom-department-group');
            const customDepartmentInput = document.getElementById('custom_department');

            if (departmentSelect.value === 'OTHERS') {
                customDepartmentGroup.style.display = 'block';
                customDepartmentInput.required = true;
            } else {
                customDepartmentGroup.style.display = 'none';
                customDepartmentInput.required = false;
                customDepartmentInput.value = '';
            }
        }

        function removeCountryFlags() {
            const countrySelect = document.getElementById('country_code');
            for (const option of countrySelect.options) {
                option.text = option.text.replace(/^[^A-Za-z0-9+]+/, '');
            }
        }

        function updatePhonePrefix() {
            const countrySelect = document.getElementById('country_code');
            const phoneInput = document.getElementById('phone');
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            const selectedCountryCode = selectedOption.value;
            
            // Update placeholder based on selected country
            const placeholders = {
                '+91': '9876543210',
                '+1': '2345678901',
                '+44': '7911123456',
                '+86': '13800138000',
                '+81': '9012345678',
                '+49': '15123456789',
                '+33': '612345678',
                '+39': '3123456789',
                '+34': '612345678',
                '+31': '612345678',
                '+32': '471234567',
                '+41': '781234567',
                '+46': '701234567',
                '+47': '41234567',
                '+45': '20123456',
                '+358': '401234567',
                '+48': '512345678',
                '+420': '601234567',
                '+421': '901234567',
                '+36': '201234567',
                '+40': '712345678',
                '+359': '871234567',
                '+30': '6912345678',
                '+351': '912345678',
                '+353': '851234567',
                '+43': '6641234567',
                '+386': '31234567',
                '+385': '912345678',
                '+387': '61123456',
                '+389': '71234567',
                '+381': '601234567',
                '+382': '67123456',
                '+383': '44123456',
                '+355': '691234567',
                '+7': '9123456789',
                '+380': '501234567',
                '+375': '291234567',
                '+994': '501234567',
                '+374': '77123456',
                '+995': '555123456',
                '+996': '555123456',
                '+992': '901234567',
                '+993': '66123456',
                '+998': '901234567',
                '+66': '812345678',
                '+84': '912345678',
                '+855': '12345678',
                '+856': '2023456789',
                '+95': '912345678',
                '+60': '123456789',
                '+62': '8123456789',
                '+63': '9123456789',
                '+673': '7123456',
                '+670': '77212345',
                '+92': '3012345678',
                '+880': '1812345678',
                '+94': '712345678',
                '+977': '9841234567',
                '+975': '17123456',
                '+93': '701234567',
                '+968': '90123456',
                '+973': '31234567',
                '+974': '33123456',
                '+965': '51234567',
                '+962': '791234567',
                '+961': '71123456',
                '+963': '944123456',
                '+964': '7501234567',
                '+98': '9123456789',
                '+90': '5012345678',
                '+972': '501234567',
                '+970': '591234567',
                '+20': '1012345678',
                '+212': '612345678',
                '+213': '551234567',
                '+216': '20123456',
                '+218': '911234567',
                '+27': '711234567',
                '+52': '5512345678',
                '+54': '91123456789',
                '+56': '912345678',
                '+57': '3012345678',
                '+58': '4121234567'
            };
            
            phoneInput.placeholder = placeholders[selectedCountryCode] || 'Enter phone number';
            
            // Update the select display to show selected country in white text
            countrySelect.style.color = '#ffffff';
            
            // Validate current input
            validatePhoneNumber();
        }

        function validatePhoneNumber() {
            const countrySelect = document.getElementById('country_code');
            const phoneInput = document.getElementById('phone');
            const countryCode = countrySelect.value;
            const phoneNumber = phoneInput.value;
            const localLengths = {
                '+91': 10, '+1': 10, '+44': 10, '+86': 11,
                '+81': 10, '+49': 11, '+33': 9, '+39': 10,
                '+61': 9, '+82': 9, '+65': 8, '+971': 9, '+966': 9
            };

            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            const countryName = selectedOption.text.replace(/\s*\(.*\)$/, '').trim().toLowerCase();
            
            const cleanPhone = phoneNumber.replace(/\D/g, '');

            if (phoneNumber && !/^\d+$/.test(cleanPhone)) {
                phoneInput.setCustomValidity(`invalid phone number for ${countryName}`);
                return;
            }

            if (phoneNumber && localLengths[countryCode] !== undefined) {
                if (cleanPhone.length !== localLengths[countryCode]) {
                    phoneInput.setCustomValidity(`invalid phone number for ${countryName}`);
                    return;
                }
            } else if (phoneNumber && (cleanPhone.length < 6 || cleanPhone.length > 14)) {
                phoneInput.setCustomValidity(`invalid phone number for ${countryName}`);
                return;
            }

            const fullNumber = `+${countryCode.replace('+', '')}${cleanPhone}`;
            const phonePattern = /^\+[1-9]\d{6,14}$/;

            if (phoneNumber && !phonePattern.test(fullNumber)) {
                phoneInput.setCustomValidity(`invalid phone number for ${countryName}`);
                return;
            }

            phoneInput.setCustomValidity('');
        }

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            const countrySelect = document.getElementById('country_code');

            // Remove emoji flags and keep only text in the dropdown options
            removeCountryFlags();

            phoneInput.addEventListener('input', validatePhoneNumber);
            phoneInput.addEventListener('blur', validatePhoneNumber);
            countrySelect.addEventListener('change', updatePhonePrefix);

            // Initialize with current country selection
            updatePhonePrefix();
        });
    </script>
</body>
</html>