<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

// Include PHPMailer

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $payment = $_POST['payment'] ?? '';
        // Accept either hidden m/d/Y fields or raw yyyy-mm-dd inputs
        $checkinRaw = $_POST['checkin'] ?? ($_POST['checkIn'] ?? '');
        $checkoutRaw = $_POST['checkout'] ?? ($_POST['checkOut'] ?? '');
        $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 1;
        $room = $_POST['room'] ?? ($_POST['roomType'] ?? '');
        $nofroom = isset($_POST['nofroom']) ? intval($_POST['nofroom']) : 1;
        $message = $_POST['message'] ?? ($_POST['requests'] ?? '');
        $pricePerNight = isset($_POST['pricePerNight']) ? intval($_POST['pricePerNight']) : 0;

        // Parse dates robustly: try m/d/Y first, then fallback to Y-m-d
        $checkinDateTime = DateTime::createFromFormat('m/d/Y', $checkinRaw);
        if (!$checkinDateTime) {
            $checkinDateTime = DateTime::createFromFormat('Y-m-d', $checkinRaw);
        }
        $checkoutDateTime = DateTime::createFromFormat('m/d/Y', $checkoutRaw);
        if (!$checkoutDateTime) {
            $checkoutDateTime = DateTime::createFromFormat('Y-m-d', $checkoutRaw);
        }

        if (!$checkinDateTime || !$checkoutDateTime) {
            throw new Exception("Invalid date format");
        }

        $checkinFormatted = $checkinDateTime->format('Y-m-d');
        $checkoutFormatted = $checkoutDateTime->format('Y-m-d');

        $interval = $checkinDateTime->diff($checkoutDateTime);
        $days = $interval->days;
        $totalPrice = $days * $pricePerNight;


        // $stmt = $con->prepare("INSERT INTO bookings (name, email, phone, payment, checkin, checkout, guests, room, message, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // $stmt->execute([$name, $email, $phone, $payment, $checkinFormatted, $checkoutFormatted, $guests, $room, $message, $totalPrice]);


        // Email to Client
        $mailClient = new PHPMailer(true);
        try {
            $mailClient->isSMTP();
            $mailClient->Host = 'mail.vegasastoriahotel.com';
            $mailClient->SMTPAuth = true;
            $mailClient->Username = 'bookings@vegasastoriahotel.com';
            $mailClient->Password = 'l?9oet?ad@Q?OXF*';
            $mailClient->SMTPSecure = 'ssl';
            $mailClient->Port = 465;

            // $mailClient->Host = 'smtp.mailtrap.io';
            // $mailClient->SMTPAuth = true;
            // $mailClient->Username = 'c37ef4508c01e6';
            // $mailClient->Password = '25db67cf9f349e';
            // $mailClient->SMTPSecure = 'tls';
            // $mailClient->Port = 2525;


            // $mailClient->setFrom('info@vegashotelsng.com', 'Vegas Astoria Hotel & Suites LTD');
            $mailClient->setFrom('lawrencechrisojor@gmail.com', 'Vegas Astoria Hotel & Suites LTD');
            $mailClient->addAddress($email, $name);
            $mailClient->Subject = 'Booking Confirmation';
            $mailClient->isHTML(true);

            $clientEmailContent = "
    <html>
      <body style='margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background:#f5f7fa;'>
          <tr>
            <td align='center' style='padding:24px;'>
              <table role='presentation' width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
                <tr>
                  <td style='background:#111827;color:#fc0303;padding:20px 24px;'>
                    <div style='font-size:20px;font-weight:600;'>Vegas Astoria Hotel &amp; Suites LTD</div>
                    <div style='font-size:13px;opacity:.85; color:#ffffff;'>Booking Confirmation</div>
                  </td>
                </tr>
                <tr>
                  <td style='padding:24px;color:#111827;'>
                    <p style='margin:0 0 12px;'>Dear $name,</p>
                    <p style='margin:0 0 20px;color:#374151;'>Thank you for booking with us. Here are your details:</p>
                    <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Room</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$room</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Check_in</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$checkinFormatted</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Check_out</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$checkoutFormatted</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Guests</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$guests</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Number of Nights</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$days</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Payment Method</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$payment</td></tr>
                    </table>
                    <div style='margin:20px 0;padding:16px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;'>
                      <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;'>
                        <span style='color:#6b7280;'>Rate / Night</span>
                        <span style='font-weight:600;color:#111827;'>&#x20A6;$pricePerNight</span>
                      </div>
                      <div style='display:flex;justify-content:space-between;align-items:center;'>
                        <span style='color:#6b7280;'>Total</span>
                        <span style='font-size:18px;font-weight:700;color:#111827;'>&#x20A6;$totalPrice</span>
                      </div>
                    </div>
                    <p style='margin:0 0 12px;color:#374151;'>We look forward to hosting you!</p>
                    <p style='margin:0;color:#6b7280;font-size:13px;'>If you have any questions or need to modify your booking, please reply to this email or call us.</p>
                  </td>
                </tr>
                <tr>
                  <td style='background:#f9fafb;padding:16px;text-align:center;color:#6b7280;font-size:12px;'>Regards, <strong>Vegas Astoria Hotel &amp; Suites LTD</strong>  Call: 08060425569</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
    </html>";

            $mailClient->Body = $clientEmailContent;
            $mailClient->send();
        } catch (Exception $e) {
            echo 'Error sending client email: ', $mailClient->ErrorInfo;
        }

        // Email to Admin
        $mailAdmin = new PHPMailer(true);
        try {
            $mailAdmin->isSMTP();
            $mailClient->isSMTP();
            $mailClient->Host = 'mail.vegasastoriahotel.com';
            $mailClient->SMTPAuth = true;
            $mailClient->Username = 'bookings@vegasastoriahotel.com';
            $mailClient->Password = 'l?9oet?ad@Q?OXF*';
            $mailClient->SMTPSecure = 'ssl';
            $mailClient->Port = 465;

            // $mailAdmin->Host = 'smtp.mailtrap.io';
            // $mailAdmin->SMTPAuth = true;
            // $mailAdmin->Username = 'c37ef4508c01e6';
            // $mailAdmin->Password = '25db67cf9f349e';
            // $mailAdmin->SMTPSecure = 'tls';
            // $mailAdmin->Port = 2525;


            // $mailAdmin->setFrom('info@vegashotelsng.com', 'Vegas Astoria Hotel & Suites LTD');
            // $mailAdmin->addAddress('info@vegashotelsng.com', 'Vegas Astoria Hotel & Suites LTD'); // Admin email
            $mailAdmin->setFrom('bookings@vegasastoriahotel.com', 'Vegas Astoria Hotel & Suites LTD');
            $mailAdmin->addAddress('info@vegasastoriahotel.com', 'Vegas Astoria Hotel & Suites LTD'); // Admin email
            $mailAdmin->Subject = 'New Booking Received';
            $mailAdmin->isHTML(true);

            $adminEmailContent = "
    <html>
      <body style='margin:0;padding:0;background:#f5f7fa;font-family:-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;'>
        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background:#f5f7fa;'>
          <tr>
            <td align='center' style='padding:24px;'>
              <table role='presentation' width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
                <tr>
                  <td style='background:#111827;color:#ffffff;padding:20px 24px;'>
                    <div style='font-size:20px;font-weight:600;'>Vegas Astoria Hotel &amp; Suites LTD</div>
                    <div style='font-size:13px;opacity:.85;'>New Booking Received</div>
                  </td>
                </tr>
                <tr>
                  <td style='padding:24px;color:#111827;'>
                    <p style='margin:0 0 16px;color:#374151;'>A new booking has been made:</p>
                    <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Name</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$name</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Email</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$email</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Phone</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$phone</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Room</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$room</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Check_in</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$checkinFormatted</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Check_out</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$checkoutFormatted</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Guests</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$guests</td></tr>
                
                      <tr><td style='padding:8px 0;color:#6b7280;'>Number of Nights</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$days</td></tr>
                      <tr><td style='padding:8px 0;color:#6b7280;'>Payment Method</td><td style='padding:8px 0;text-align:right;font-weight:600;color:#111827;'>$payment</td></tr>
                      </table>
                      <p style='margin:0;color:#6b7280;font-size:13px;'>Special requests: $message</p>
                    <div style='margin:20px 0;padding:16px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;'>
                      <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;'>
                        <span style='color:#6b7280;'>Rate / Night</span>
                        <span style='font-weight:600;color:#111827;'>&#x20A6;$pricePerNight</span>
                      </div>
                      <div style='display:flex;justify-content:space-between;align-items:center;'>
                        <span style='color:#6b7280;'>Total</span>
                        <span style='font-size:18px;font-weight:700;color:#111827;'>&#x20A6;$totalPrice</span>
                      </div>
                    </div>
                    
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
    </html>";

            $mailAdmin->Body = $adminEmailContent;
            $mailAdmin->send();


            header("Location: booking.html?message=Booking successful.");
            exit;
        } catch (Exception $e) {
            echo 'Error sending admin email: ', $mailAdmin->ErrorInfo;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
