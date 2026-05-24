<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Nibras Purchase Confirmation</title>
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
                <!-- <img src="images/left-bg.png" alt="Left BG" style="position:absolute; top: -10px;; left:-10px;  display:block;"> -->
                <!-- <img src="images/right-bg.png" alt="Right BG" style="position:absolute; top:0; right:0;  display:block;"> -->
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
                      <h1 style="margin:0; font-weight:700; font-size:32px; line-height:130%; text-align:center; color:#043458;"> Thank you {{ $student->name }}</h1>
                      <p style="margin:15px auto 25px; font-weight:400; font-size:16px; line-height:130%; color:#545F71; max-width:75%; text-align:center;"> Nibras Academy is delighted to have you on your learning journey, education is the passport to the future. </p>
                    </td>
                  </tr>
                  <!-- Student Diamond -->
                  <tr>
                    <td align="center" style="padding:20px;">
                      <img src="{{ asset('assets/emails/images/en/diamond.png') }}" alt="Student" width="422" style="display:block;">
                    </td>
                  </tr>
                  <!-- Purchased Courses -->
                  <tr>
                    <td align="center" style="padding:10px 30px;">
                      <h2 style="margin:0 0 20px; font-weight:500; font-size:24px; line-height:130%; text-align:center; color:#043458;"> Purchased Courses </h2>
                      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; font-size:14px; background:linear-gradient(90deg, #09528A 0%, #043458 100%);
                                                border-radius:8px; overflow:hidden; color:#ffffff; text-align:left; margin-bottom:40px;">
                        <thead>
                          <tr style="font-weight:600; font-size:12px; line-height:130%;">
                            <th style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Course Name</th>
                            <th style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Grade</th>
                            <th style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Date</th>
                            <th style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Teacher Name</th>
                            <th style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Payment Type</th>
                            <th style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Price</th>
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
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">{{ $paymentItem->title_en }}</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">{{ $paymentItem->course->classRoom->name_en }}</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">{{ date('d/m/Y', strtotime($payment->paid_at)) }}</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">{{ $paymentItem->course->teacher->name_en }}</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">{{ $paymentItem->payment_type === 'monthly' ? 'Installments' : 'Cash' }}</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">${{ (new \App\Helpers\Helper)->formatNumber($displayPrice) }}</td>
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
                            <td colspan="5" style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Subtotal</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">${{ (new \App\Helpers\Helper)->formatNumber($subtotal) }}</td>
                          </tr>
                          <tr style="font-weight:500; color:#ffffff;">
                            <td colspan="5" style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">
                              @if($payment->coupon)
                                Discount (Coupon {{ $payment->coupon->coupon_code }} - {{ (new \App\Helpers\Helper)->formatNumber($payment->coupon->discount_percentage) }}%)
                              @else
                                Discount
                              @endif
                            </td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">-${{ (new \App\Helpers\Helper)->formatNumber($discountAmount) }}</td>
                          </tr>
                          <tr style="font-weight:600; color:#ffffff; font-size:15px;">
                            <td colspan="5" style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">Total</td>
                            <td style="padding:12px; border-width:1px 0 0 1px; border-style:solid; border-color:#336A94;">${{ (new \App\Helpers\Helper)->formatNumber($total) }}</td>
                          </tr>
                        </tfoot>
                      </table>
                      <p style="margin:20px auto; font-size:18px; font-weight:500; color:#545F71; line-height:130%; text-align:center; max-width:70%;"> You can access your purchased course anytime by logging into your account through: </p>
                      <p style="margin:0 0 40px; font-weight:500; color:#043458; font-size:24px; line-height:130%; text-align:center;">
                        <a href="https://mynibras.com">Website link</a>
                      </p>
                    </td>
                  </tr>
                  <!-- Footer -->
                  <tr>
                    <td align="center" style="padding: 30px 30px 0 30px;">
                      <table width="600" cellpadding="0" cellspacing="0" border="0" style="border-radius:20px; overflow:hidden; position:relative; background:#ffffff;">
                        <!-- Background Row (kept for glow images) -->
                        <!-- <tr> -->
                          <!-- <td style="position:relative;"> -->
                            <!-- Left Glow -->
                            <!-- <img src="{{ asset('assets/emails/images/en/footer-left-bg.png') }}" alt="Left BG" style="display:block; position:absolute; left:0; top:0; border-radius:20px 0 0 20px;"> -->
                            <!-- Right Glow -->
                            <!-- <img src="{{ asset('assets/emails/images/en/footer-right-bg.png') }}" alt="Right BG" style="display:block; position:absolute; right:0; top:0; border-radius:0 20px 20px 0;"> -->
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
                                  <p style="margin:15px 0 65px 0; font-weight:600; font-size:18px;
                                                            line-height:100%; color:#043458;"> If you need any assistance, please don’t hesitate to reach us at: </p>
                                  <!-- Contact info lower -->
                                  <table cellpadding="0" cellspacing="0" border="0" style="font-size:21px; line-height:32px; color:#043458;">
                                    <tr>
                                      <td style="padding:6px 0;">
                                        <img src="{{ asset('assets/emails/images/en/mail.png') }}" alt="mail-icon" width="24" style="vertical-align:middle; margin-right:8px;">
                                        <a href="mailto:notifier@mynibras.com" style="text-decoration:none; color:#043458;">notifier@mynibras.com</a>
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