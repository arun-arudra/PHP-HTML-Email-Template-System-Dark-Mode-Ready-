<?php
// ---------------------------------------------------------
// 1. CONFIGURATION & HEADERS
// ---------------------------------------------------------

// Allow your React app to talk to this script (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Disable error printing to screen (prevents "Unexpected token" errors in React)
// Errors will still be logged to your server's error_log file
ini_set('display_errors', 0);
error_reporting(E_ALL);

// --- CONFIGURATION ---
$company_arun   = "Arun Arudra";
$owner_email_to = "abcd@example.com"; 

// Dynamic Server Info
$protocol    = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$server_host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$server_url  = $protocol . "://" . $server_host;
$from_email  = "noreply@" . $server_host; 

// ---------------------------------------------------------
// 2. DATA COLLECTION
// ---------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    // HELPER: Sanitize string (Replaces deprecated FILTER_SANITIZE_STRING)
    function clean_input($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    if (!empty($input)) {
        // JSON Input
        $name    = clean_input($input['name'] ?? '');
        $phone   = clean_input($input['phone'] ?? '');
        $email   = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $company = clean_input($input['company'] ?? '');
        $message = clean_input($input['message'] ?? '');
    } else {
        // Form Data Input
        $name    = clean_input($_POST['name'] ?? '');
        $phone   = clean_input($_POST['phone'] ?? '');
        $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $company = clean_input($_POST['company'] ?? '');
        $message = clean_input($_POST['message'] ?? '');
    }

    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit();
    }

    // ---------------------------------------------------------
    // 3. LOAD TEMPLATES
    // ---------------------------------------------------------
    
    // Ensure folder name matches EXACTLY (Case Sensitive!)
    // Check if your folder is 'Email-templates' or 'email-templates'
    $owner_template_path = __DIR__ . '/Email-templates/owner.html';
    $user_template_path  = __DIR__ . '/Email-templates/user.html';

    if (!file_exists($owner_template_path) || !file_exists($user_template_path)) {
        // Fallback checks for lowercase if uppercase fails
        $owner_template_path = __DIR__ . '/email-templates/owner.html';
        $user_template_path  = __DIR__ . '/email-templates/user.html';
        
        if (!file_exists($owner_template_path)) {
            echo json_encode(["status" => "error", "message" => "Server Error: Template files not found."]);
            exit();
        }
    }

    $owner_body = file_get_contents($owner_template_path);
    $user_body  = file_get_contents($user_template_path);

    // ---------------------------------------------------------
    // 4. REPLACE PLACEHOLDERS
    // ---------------------------------------------------------
    
    $swap_var = array(
        "{{name}}"       => $name,
        "{{phone}}"      => $phone,
        "{{email}}"      => $email,
        "{{company}}"    => $company,
        "{{message}}"    => nl2br($message),
        "{{server_url}}" => $server_url,
        "{{site_name}}"  => $company_arun  // <--- The Comma Issue was likely here previously
    );

    foreach($swap_var as $key => $value){
        $owner_body = str_replace($key, $value, $owner_body);
        $user_body  = str_replace($key, $value, $user_body);
    }

    // ---------------------------------------------------------
    // 5. SEND EMAILS
    // ---------------------------------------------------------
    
    // Email 1: To Owner
    $owner_subject = "New Inquiry - " . $company_arun;
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $company_arun . " <" . $from_email . ">" . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n"; 

    $mail_owner = mail($owner_email_to, $owner_subject, $owner_body, $headers);

    // Email 2: To User
    if ($email) {
        $user_subject = "Thank you for contacting " . $company_arun;
        $headers_user  = "MIME-Version: 1.0" . "\r\n";
        $headers_user .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers_user .= "From: " . $company_arun . " <" . $from_email . ">" . "\r\n";
        $headers_user .= "Reply-To: " . $owner_email_to . "\r\n"; 

        mail($email, $user_subject, $user_body, $headers_user);
    }

    // ---------------------------------------------------------
    // 6. RESPONSE
    // ---------------------------------------------------------
    if($mail_owner){
        echo json_encode(["status" => "success", "message" => "Thank you! Your message has been sent."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Mail server failed to send email."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
?>