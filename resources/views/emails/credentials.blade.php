<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Credentials</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0"
                    style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="padding: 40px 20px; background-color: #0d6efd; color: #ffffff; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h1 style="margin: 0; font-size: 24px;">Welcome to {{ config('app.name') }}!</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: #555555; line-height: 1.6;">
                            <p style="margin: 0 0 20px;">Your account has been created successfully.</p>
                            <p style="margin: 0 0 20px;">Please use the following credentials to log in to our platform:
                            </p>
                            <div
                                style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6;">
                                <p style="margin: 0 0 10px;"><strong>Username:</strong> {{ $username }}</p>
                                <p style="margin: 0;"><strong>Password:</strong> {{ $password }}</p>
                            </div>
                            <p style="margin: 20px 0 0;">For your security, we strongly recommend changing your password
                                after your first login.</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding: 20px 30px; background-color: #f4f4f4; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; font-size: 12px; color: #888888;">
                            <p style="margin: 0;">Thank you for joining us!</p>
                            <p style="margin: 5px 0 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights
                                reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
