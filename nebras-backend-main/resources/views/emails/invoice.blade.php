<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تأكيد عملية الشراء - نبراس</title>
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
                <!-- <img src="{{ asset('emails/images/invoice-ar/left-bg.png') }}" alt="خلفية يسار" style="position:absolute; top:-10px; left:-10px; display:block;">
                <img src="{{ asset('emails/images/invoice-ar/right-bg.png') }}" alt="خلفية يمين" style="position:absolute; top:0; right:0; display:block;"> -->
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
                      <h1 style="margin:0; font-weight:700; font-size:32px; line-height:130%; text-align:center; color:#043458;"> شكراً لك {{ $student->name }} </h1>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:150%; color:#545F71; max-width:80%; text-align:center;"> أكاديمية نبراس سعيدة بانضمامك إلى رحلتك التعليمية، فالتعليم هو جواز السفر إلى المستقبل. </p>
                    </td>
                  </tr>
                  <!-- Student Diamond -->
                  <tr>
                    <td align="center" style="padding:20px;">
                      <img src="{{ asset('assets/emails/images/ar/diamond.png') }}" alt="طالب" width="422" style="display:block;">
                    </td>
                  </tr>
                  <!-- Purchased Courses -->
                  <tr>
                    <td align="center" style="padding:10px 30px;">
                      <h2 style="margin:0 0 20px; font-weight:500; font-size:24px; line-height:130%; text-align:center; color:#043458;"> الدورات المشتراة </h2>
                      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; font-size:14px; background:linear-gradient(90deg, #09528A 0%, #043458 100%);
                                                border-radius:8px; overflow:hidden; color:#ffffff; text-align:right; margin-bottom:40px;">
                        <thead>
                          <tr style="font-weight:600; font-size:12px; line-height:130%;">
                            <th style="padding:12px; border:1px solid #336A94;">اسم الدورة</th>
                            <th style="padding:12px; border:1px solid #336A94;">الصف</th>
                            <th style="padding:12px; border:1px solid #336A94;">التاريخ</th>
                            <th style="padding:12px; border:1px solid #336A94;">اسم المعلم</th>
                            <th style="padding:12px; border:1px solid #336A94;">نوع الدفع</th>
                            <th style="padding:12px; border:1px solid #336A94;">السعر</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($payment->items as $paymentItem)
                            @php
                              $displayPrice = $paymentItem->total;
                              if ($paymentItem->payment_type === 'monthly' && $paymentItem->installments->isNotEmpty()) {
                                $firstInstallment = $paymentItem->installments->sortBy('installment_number')->first();
                                if ($firstInstallment) {
                                  $displayPrice = $firstInstallment->amount;
                                }
                              }
                            @endphp
                          <tr style="font-weight:400; color:#ffffff;">
                            <td style="padding:12px; border:1px solid #336A94;">{{ $paymentItem->title }}</td>
                            <td style="padding:12px; border:1px solid #336A94;">{{ $paymentItem->course->classRoom->name }}</td>
                            <td style="padding:12px; border:1px solid #336A94;">{{ date('d/m/Y', strtotime($payment->paid_at)) }}</td>
                            <td style="padding:12px; border:1px solid #336A94;">{{ $paymentItem->course->teacher->name }}</td>
                            <td style="padding:12px; border:1px solid #336A94;">{{ $paymentItem->payment_type === 'monthly' ? 'أقساط' : 'كاش' }}</td>
                            <td style="padding:12px; border:1px solid #336A94;">${{ (new \App\Helpers\Helper)->formatNumber($displayPrice) }}</td>
                          </tr>
                          @endforeach
                        </tbody>
                        <tfoot>
                          @php
                            $subtotal = $payment->items->sum('total');
                            $discountAmount = $payment->discount_amount ?? 0;
                            $total = $payment->amount;
                          @endphp
                          <tr style="font-weight:500; color:#ffffff;">
                            <td colspan="5" style="padding:12px; border:1px solid #336A94;">المجموع الفرعي</td>
                            <td style="padding:12px; border:1px solid #336A94;">${{ (new \App\Helpers\Helper)->formatNumber($subtotal) }}</td>
                          </tr>
                          <tr style="font-weight:500; color:#ffffff;">
                            <td colspan="5" style="padding:12px; border:1px solid #336A94;">
                              @if($payment->coupon)
                                الخصم (كوبون {{ $payment->coupon->coupon_code }} - {{ (new \App\Helpers\Helper)->formatNumber($payment->coupon->discount_percentage) }}%)
                              @else
                                الخصم
                              @endif
                            </td>
                            <td style="padding:12px; border:1px solid #336A94;">-${{ (new \App\Helpers\Helper)->formatNumber($discountAmount) }}</td>
                          </tr>
                          <tr style="font-weight:600; color:#ffffff; font-size:15px;">
                            <td colspan="5" style="padding:12px; border:1px solid #336A94;">الإجمالي</td>
                            <td style="padding:12px; border:1px solid #336A94;">${{ (new \App\Helpers\Helper)->formatNumber($total) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                      <p style="margin:20px auto; font-size:18px; font-weight:500; color:#545F71; line-height:150%; text-align:center; max-width:80%;"> يمكنك الوصول إلى دورتك المشتراة في أي وقت عن طريق تسجيل الدخول إلى حسابك عبر: </p>
                      <p style="margin:0 0 40px; font-weight:600; color:#043458; font-size:22px; line-height:130%; text-align:center;">
                        <a href="https://mynibras.com">رابط الموقع</a>
                      </p>
                    </td>
                  </tr>
                  <!-- Footer -->
                  <tr>
                    <td align="center" style="padding:30px 30px 0 30px;">
                      <table width="600" cellpadding="0" cellspacing="0" border="0" style="border-radius:20px; overflow:hidden; position:relative; background:url('{{ url('assets/emails/images/bg-footer.png') }}') no-repeat center top; background-size:cover;">
                        <!-- Background Row -->
                        <!-- <tr> -->
                          <!-- <td style="position:relative;"> -->
                            <!-- <img src="{{ asset('emails/images/invoice-ar/footer-left-bg.png') }}" alt="خلفية يسار" style="display:block; position:absolute; left:0; top:0; border-radius:20px 0 0 20px;"> -->
                            <!-- <img src="{{ asset('emails/images/invoice-ar/footer-right-bg.png') }}" alt="خلفية يمين" style="display:block; position:absolute; right:0; top:0; border-radius:0 20px 20px 0;"> -->
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