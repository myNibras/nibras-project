<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Your verification code Nibras</title>
  </head>
  <body style="margin:0; padding:0; background:#f3f8ff; font-family:Inter, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td align="center" style="padding:20px;">
          <!-- MAIN CARD -->
          <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:20px; overflow:hidden; position:relative;">
            <!-- Background Images -->
            <tr>
              <td style="position:relative;">
                <!-- <img src="{{ asset('assets/emails/images/en/left-bg.png') }}" alt="Left BG" style="display:none; position:absolute; top: -10px;; left:-10px;  display:block;"> -->
                <!-- <img src="{{ asset('assets/emails/images/en/right-bg.png') }}" alt="Right BG" style="display:none; position:absolute; top:0; right:0;  display:block;"> -->
                <!-- CONTENT WRAPPER -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="position:relative; z-index:1;">
                  <!-- Logo -->
                  <tr>
                    <td align="center" style="padding:30px 20px 10px;">
                      <img src="{{ asset('assets/emails/images/en/en-logo.png') }}" alt="Nibras Logo" width="163" style="display:block;">
                    </td>
                  </tr>
                  <!-- Title -->
                  <tr>
                    <td align="center" style="padding:0 20px;">
                      <h1 style="margin:0; font-weight:700; font-size:32px; line-height:130%; text-align:center; color:#043458;">Hello {{ $student->name }},</h1>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:130%; color:#545F71; max-width:75%; text-align:center;">Your verification code (OTP) is: <b style="font-size:18px">{{ $otp }}</b></p>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:130%; color:#545F71; max-width:75%; text-align:center;">This code is valid for 3 minutes only.</p>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:130%; color:#545F71; max-width:75%; text-align:center;">⚠ If you did not request this code, please ignore this message and do not share it with anyone.</p>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:130%; color:#545F71; max-width:75%; text-align:center;">Best regards, <br> The Nibras Team</p>
                    </td>
                  </tr>
                  <!-- Footer -->
                  <tr>
                    <td align="center" style="padding: 30px 30px 0 30px;">
                      <table width="600" cellpadding="0" cellspacing="0" border="0" style="border-radius:20px; overflow:hidden; position:relative;  background:url('{{ url('assets/emails/images/bg-footer.png') }}') no-repeat center top; background-size:cover;">
                        <!-- Background Row (kept for glow images) -->
                        <!-- <tr> -->
                          <!-- <td style="position:relative;"> -->
                            <!-- Left Glow -->
                            <!-- <img src="{{ asset('assets/emails/images/en/footer-left-bg.png') }}" alt="Left BG" style="display:none; position:absolute; left:0; top:0; border-radius:20px 0 0 20px;"> -->
                            <!-- Right Glow -->
                            <!-- <img src="{{ asset('assets/emails/images/en/footer-right-bg.png') }}" alt="Right BG" style="display:none; position:absolute; right:0; top:0; border-radius:0 20px 20px 0;"> -->
                          <!-- </td> -->
                        <!-- </tr> -->
                        <!-- Content Row -->
                        <tr>
                          <td style="padding:30px 0 30px 30px; font-size:14px; color:#043458;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                              <tr>
                                <!-- Left: Sentence + Contact -->
                                <td align="left" valign="top" style="width:55%; text-align:left;">
                                  <!-- Sentence at top -->
                                  <p style="margin:15px 0 65px 0; font-weight:600; font-size:18px; line-height:100%; color:#043458;"> If you need any assistance, please don’t hesitate to reach us at: </p>
                                  <!-- Contact info lower -->
                                  <table cellpadding="0" cellspacing="0" border="0" style="font-size:21px; line-height:32px; color:#043458;">
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/en/mail.png') }}" alt="mail-icon" width="24" style="vertical-align:middle; margin-right:8px;">
                                        <a href="mailto:notifier@mynibras.com" style="text-decoration:none; color:#043458;"> notifier@mynibras.com </a>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/en/phone.png') }}" alt="phone-icon" width="24" style="vertical-align:middle; margin-right:8px;"> 
                                        <a href="tel:+97430102626" style="text-decoration:none; color:#043458;">+97430102626</a>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/en/globe1.png') }}" alt="global-icon" width="24" style="vertical-align:middle; margin-right:8px;">
                                        <a href="https://www.mynibras.com" style="text-decoration:none; color:#043458;"> www.mynibras.com </a>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                                <!-- Right: Footer Decor -->
                                <td align="right" valign="top" style="width:45%;">
                                  <img src="{{ asset('assets/emails/images/en/footer-decor.png') }}" alt="Footer Decor" width="240" style="display:block; border-radius:0 20px 20px 0;">
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
  </body>
</html>