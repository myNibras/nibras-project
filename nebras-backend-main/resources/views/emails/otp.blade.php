<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق لموقع نبراس</title>
    <!-- Tajawal Font -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0; padding:0; background:#f3f8ff; font-family:'Tajawal', Arial, sans-serif; direction:rtl;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td align="center" style="padding:20px;">
          <!-- MAIN CARD -->
          <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:20px; overflow:hidden; position:relative;">
            <!-- Background Images -->
            <tr>
              <td style="position:relative;">
                <!-- <img src="{{ asset('assets/emails/images/ar/left-bg.png') }}" alt="خلفية يسار" style="display:none; position:absolute; top:-10px; left:-10px; display:block;"> -->
                <!-- <img src="{{ asset('assets/emails/images/ar/right-bg.png') }}" alt="خلفية يمين" style="display:none; position:absolute; top:0; right:0; display:block;"> -->
                <!-- CONTENT WRAPPER -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="position:relative; z-index:1;">
                  <!-- Logo -->
                  <tr>
                    <td align="center" style="padding:30px 20px 10px;">
                      <img src="{{ asset('assets/emails/images/ar/ar-logo.png') }}" alt="شعار نبراس" width="163" style="display:block;">
                    </td>
                  </tr>
                  <!-- Title -->
                  <tr>
                    <td align="center" style="padding:0 20px;">
                      <h1 style="margin:0; font-weight:700; font-size:32px; line-height:130%; text-align:center; color:#043458;"> مرحبا {{ $student->name }}, </h1>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:150%; color:#545F71; max-width:80%; text-align:center;">
                         رمز التحقق الخاص بك هو: <b style="font-size:18px;">{{ $otp }}</b>
                      </p>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:150%; color:#545F71; max-width:80%; text-align:center;">
                        هذا الرمز صالح لمدة 3 دقائق فقط.
                      </p>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:150%; color:#545F71; max-width:80%; text-align:center;">
                        ⚠ إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة وعدم مشاركته مع أي شخص.
                      </p>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:150%; color:#545F71; max-width:80%; text-align:center;">
                        مع تحياتنا،
                        <br>
                        فريق نبراس
                      </p>
                    </td>
                  </tr>
                  <!-- Footer -->
                  <tr>
                    <td align="center" style="padding:30px 30px 0 30px; background: #fff">
                      <table width="600" cellpadding="0" cellspacing="0" border="0" style="border-radius:20px; overflow:hidden; position:relative; background:url('{{ url('assets/emails/images/bg-footer.png') }}') no-repeat center top; background-size:cover;">
                        <!-- Background Row -->
                        <!-- <tr> -->
                          <!-- <td style="position:relative;"> -->
                            <!-- <img src="{{ asset('assets/emails/images/ar/footer-left-bg.png') }}" alt="خلفية يسار" style="display:block; position:absolute; left:0; top:0; border-radius:20px 0 0 20px;"> -->
                            <!-- <img src="{{ asset('assets/emails/images/ar/footer-right-bg.png') }}" alt="خلفية يمين" style="display:block; position:absolute; right:0; top:0; border-radius:0 20px 20px 0;"> -->
                          <!-- </td> -->
                        <!-- </tr> -->
                        <!-- Content Row -->
                        <tr>
                          <td style="padding:30px 30px 30px 0; font-size:14px; color:#043458;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                              <tr>
                                <!-- Right: Text + Contact -->
                                <td align="right" valign="top" style="width:55%;">
                                  <p style="margin:15px 0 65px 0; font-weight:600; font-size:18px;
                                                                    line-height:100%; color:#043458;"> إذا كنت بحاجة لأي مساعدة، لا تتردد بالتواصل معنا عبر: </p>
                                  <table cellpadding="0" cellspacing="0" border="0" style="font-size:18px; line-height:32px; color:#043458;">
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/ar/mail.png') }}" alt="mail-icon" width="20" style="vertical-align:middle; margin-left:8px;">
                                        <a href="mailto:notifier@mynibras.com" style="text-decoration:none; color:#043458;">notifier@mynibras.com</a>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/ar/phone.png') }}" alt="phone-icon" width="20" style="vertical-align:middle; margin-left:8px;">
                                        <a href="tel:+97430102626" style="text-decoration:none; color:#043458;">+97430102626</a>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/ar/globe1.png') }}" alt="global-icon" width="20" style="vertical-align:middle; margin-left:8px;">
                                        <a href="https://www.mynibras.com" style="text-decoration:none; color:#043458;"> www.mynibras.com </a>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                                <!-- Left: Decor -->
                                <td align="left" valign="top" style="width:45%;">
                                  <img src="{{ asset('assets/emails/images/ar/footer-decor.png') }}" alt="تزيين" width="240" style="display:block; border-radius:0 0 20px 20px;">
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