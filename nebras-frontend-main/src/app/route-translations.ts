export const routeTranslations: RouteTranslations = {
  en:
  {
    'ar': 'en',
    'المواد': 'subjects',
    'الشروط-والاحكام': 'terms-and-conditions',
    'سياسة-الالغاء-والاسترجاع': 'cancelation-and-refund-policy',
    'تفاصيل-المادة': 'course-details',
    'الملف-الشخصي': 'profile',
    'الكل': 'all',
    'أخبارنا': 'our-news',
    'تفاصيل-الخبر': 'news-details',
    'مقالات': 'articles',
    'تفاصيل-المقال': 'article-details',
    'شركاؤنا':'partners',
    'حقيبة-الشراء': 'basket',
    'المعلمين': 'all-teachers',
    'تفاصيل-المعلم': 'teacher-details',
    'من-نحن': 'about-us',
    'وظائفنا': 'our-careers',
    'تواصل-معنا': 'contact-us',
    'انشئ-حساب': 'sign-up',
    'تسجيل-الدخول': 'login',
    'إعادة-تعيين-كلمة-المرور': 'reset-password',
    'التحقق-من-الرمز': 'verification-code',
    'كلمة-المرور-الجديدة': 'new-password',
    'تمت-العملية-بنجاح': 'successful-message'
  },
  ar:
  {
    'en': 'ar',
    'subjects': 'المواد',
    'terms-and-conditions': 'الشروط-والاحكام',
    'cancellation-and-refund-policy': 'سياسة-الالغاء-والاسترجاع',
    'course-details': 'تفاصيل-المادة',
    'profile': 'الملف-الشخصي',
    'all': 'الكل',
    'our-news': 'أخبارنا',
    'news-details': 'تفاصيل-الخبر',
    'articles': 'مقالات',
    'article-details': 'تفاصيل-المقال',
    'partners':'شركاؤنا',
    'basket': 'حقيبة-الشراء',
    'teachers': 'المعلمين',
    'teacher-details': 'تفاصيل-المعلم',
    'about-us': 'من-نحن',
    'our-careers': 'وظائفنا',
    'contact-us': 'تواصل-معنا',
    'sign-up': 'انشئ-حساب',
    'login': 'تسجيل-الدخول',
    'reset-password': 'إعادة-تعيين-كلمة-المرور',
    'verification-code': 'التحقق-من-الرمز',
    'new-password': 'كلمة-المرور-الجديدة',
    'successful-message': 'تمت-العملية-بنجاح'
  }
};


export interface RouteTranslations {
  [key: string]: {
    [route: string]: string;
  };
}
