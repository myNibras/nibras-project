
import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    redirectTo: '/ar',
    pathMatch: 'full',
  },
  {
    path: 'en',
    loadComponent: () => import('./home/home.component').then(c => c.HomeComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar',
    loadComponent: () => import('./home/home.component').then(c => c.HomeComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/subjects',
    redirectTo: '/en/subjects/all',
    pathMatch: 'full'
  },
  {
    path: 'ar/المواد',
    redirectTo: '/ar/المواد/الكل',
    pathMatch: 'full'
  },
  {
    path: 'en/subjects/:acadmic_level',
    loadComponent: () => import('./subjects/subjects/subjects.component').then(c => c.SubjectsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/المواد/:acadmic_level',
    loadComponent: () => import('./subjects/subjects/subjects.component').then(c => c.SubjectsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/terms-and-conditions',
    loadComponent: () => import('./static-pages/terms-and-conditions/terms-and-conditions.component').then(c => c.TermsAndConditionsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/الشروط-والاحكام',
    loadComponent: () => import('./static-pages/terms-and-conditions/terms-and-conditions.component').then(c => c.TermsAndConditionsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/privacy-policy',
    loadComponent: () => import('./static-pages/privacy-policy/privacy-policy.component').then(c => c.PrivacyPolicyComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/سياسة-الخصوصية',
    loadComponent: () => import('./static-pages/privacy-policy/privacy-policy.component').then(c => c.PrivacyPolicyComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/cancellation-and-refund-policy',
    loadComponent: () => import('./static-pages/cancellation-and-refund-policy/cancellation-and-refund-policy.component').then(c => c.CancellationAndRefundPolicyComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/سياسة-الالغاء-والاسترجاع',
    loadComponent: () => import('./static-pages/cancellation-and-refund-policy/cancellation-and-refund-policy.component').then(c => c.CancellationAndRefundPolicyComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/course-details/:acadmic_level/:course_slug/:course_id',
    loadComponent: () => import('./subject-page/subject-page.component').then(c => c.SubjectPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تفاصيل-المادة/:acadmic_level/:course_slug/:course_id',
    loadComponent: () => import('./subject-page/subject-page.component').then(c => c.SubjectPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/profile',
    loadComponent: () => import('./profile/profile-page/profile-page.component').then(c => c.ProfilePageComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/الملف-الشخصي',
    loadComponent: () => import('./profile/profile-page/profile-page.component').then(c => c.ProfilePageComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/our-news',
    loadComponent: () => import('./news/components/all-news/all-news.component').then(c => c.AllNewsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/أخبارنا',
    loadComponent: () => import('./news/components/all-news/all-news.component').then(c => c.AllNewsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/news-details/:id',
    loadComponent: () => import('./news/components/news-details/news-details.component').then(c => c.NewsDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تفاصيل-الخبر/:id',
    loadComponent: () => import('./news/components/news-details/news-details.component').then(c => c.NewsDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/articles',
    loadComponent: () => import('./articles/all-articles/all-articles.component').then(c => c.AllArticlesComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/مقالات',
    loadComponent: () => import('./articles/all-articles/all-articles.component').then(c => c.AllArticlesComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/article-details/:id',
    loadComponent: () => import('./articles/article-details/article-details.component').then(c => c.ArticleDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تفاصيل-المقال/:id',
    loadComponent: () => import('./articles/article-details/article-details.component').then(c => c.ArticleDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/partners',
    loadComponent: () => import('./partners-page/partners-page.component').then(c => c.PartnersPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/شركاؤنا',
    loadComponent: () => import('./partners-page/partners-page.component').then(c => c.PartnersPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/basket',
    loadComponent: () => import('./basket/basket.component').then(c => c.BasketComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/حقيبة-الشراء',
    loadComponent: () => import('./basket/basket.component').then(c => c.BasketComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/teachers',
    loadComponent: () => import('./teachers/all-teachers/all-teachers.component').then(c => c.AllTeachersComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/المعلمين',
    loadComponent: () => import('./teachers/all-teachers/all-teachers.component').then(c => c.AllTeachersComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/teacher-details/:id',
    loadComponent: () => import('./teachers/teacher-details/teacher-details.component').then(c => c.TeacherDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تفاصيل-المعلم/:id',
    loadComponent: () => import('./teachers/teacher-details/teacher-details.component').then(c => c.TeacherDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/student-subject-details/:course_slug/:course_id',
    loadComponent: () =>
      import('./profile/components/student-subject-details/student-subject-details.component')
        .then(c => c.StudentSubjectDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تفاصيل-مادة-الطالب/:course_slug/:course_id',
    loadComponent: () =>
      import('./profile/components/student-subject-details/student-subject-details.component')
        .then(c => c.StudentSubjectDetailsComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/about-us',
    loadComponent: () => import('./static-pages/about-us-page/about-us-page.component').then(c => c.AboutUsPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/من-نحن',
    loadComponent: () => import('./static-pages/about-us-page/about-us-page.component').then(c => c.AboutUsPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/our-careers',
    loadComponent: () => import('./our-careers/our-careers.component').then(c => c.OurCareersComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/وظائفنا',
    loadComponent: () => import('./our-careers/our-careers.component').then(c => c.OurCareersComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/contact-us',
    loadComponent: () => import('./contact-us-page/contact-us-page.component').then(c => c.ContactUsPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تواصل-معنا',
    loadComponent: () => import('./contact-us-page/contact-us-page.component').then(c => c.ContactUsPageComponent),
    pathMatch: 'full'
  },
   {
    path: 'en/faqs',
    loadComponent: () => import('./static-pages/faq-page/faq-page.component').then(c => c.FaqPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/الأسئلة-الشائعة',
    loadComponent: () => import('./static-pages/faq-page/faq-page.component').then(c => c.FaqPageComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/login',
    loadComponent: () => import('./auth/login-register/components/login/login.component').then(c => c.LoginComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تسجيل-الدخول',
    loadComponent: () => import('./auth/login-register/components/login/login.component').then(c => c.LoginComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/sign-up',
    loadComponent: () => import('./auth/login-register/components/register/register.component').then(c => c.RegisterComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/انشئ-حساب',
    loadComponent: () => import('./auth/login-register/components/register/register.component').then(c => c.RegisterComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/reset-password',
    loadComponent: () => import('./auth/login-register/components/reset-password/reset-password.component').then(c => c.ResetPasswordComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/إعادة-تعيين-كلمة-المرور',
    loadComponent: () => import('./auth/login-register/components/reset-password/reset-password.component').then(c => c.ResetPasswordComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/verification-code',
    loadComponent: () => import('./auth/login-register/components/verification-code/verification-code.component').then(c => c.VerificationCodeComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/التحقق-من-الرمز',
    loadComponent: () => import('./auth/login-register/components/verification-code/verification-code.component').then(c => c.VerificationCodeComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/new-password',
    loadComponent: () => import('./auth/login-register/components/new-password/new-password.component').then(c => c.NewPasswordComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/كلمة-المرور-الجديدة',
    loadComponent: () => import('./auth/login-register/components/new-password/new-password.component').then(c => c.NewPasswordComponent),
    pathMatch: 'full'
  },
  {
    path: 'en/successful-message',
    loadComponent: () => import('./auth/login-register/components/successful-popup/successful-popup.component').then(c => c.SuccessfulPopupComponent),
    pathMatch: 'full'
  },
  {
    path: 'ar/تمت-العملية-بنجاح',
    loadComponent: () => import('./auth/login-register/components/successful-popup/successful-popup.component').then(c => c.SuccessfulPopupComponent),
    pathMatch: 'full'
  },
  {
    path: '**',
    redirectTo: '/ar',
  }
];
