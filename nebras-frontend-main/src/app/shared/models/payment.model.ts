import { ApiResponse } from "./api-response";
import { Course } from "./courses";

export type AddToCartResponse = ApiResponse<null>;

export interface PaymentResponse {
  status: boolean;
  message: string;
  payment_id: number;
  session_id: string;
}

export interface CartResponse {
  status: boolean;
  message: string;
  data: CartData;
}

export interface CartData {
  payment_id: number;
  items: CartItem[];
  original_amount: number;
  coupon_code: string;
  discount_percentage: string;
  discount_amount: string;
  final_amount: string;
  total_amount: string;
  item_count: number;
}

export interface CartItem {
  id: number;
  course_id: number;
  payment_type: string;
  quantity: number;
  price: string;
  total: string;
  title: string;
  short_description: string;
  course: Course;
}

export interface CouponResponse {
  status: boolean;
  message: string;
  data: CartData;
}

export interface CouponDetails {
  coupon_code: string;
  discount_percentage: string;
  discount_amount: number;
  original_amount: number;
  final_amount: number;
}

// ===== Profile payments (invoices & installments) =====

export interface ProfilePaymentInstallment {
  id: number;
  payment_item_id: number;
  installment_number: number;
  amount: number;
  due_date: string;
  status: string;
  paid_at: string | null;
  invoice_link: string | null;
  /** Order ID for this installment (present when paid, null when pending). Use this for installments instead of the payment-level order_id. */
  order_id: string | null;
}

export interface ProfilePaymentItem {
  order_id: string;
  amount: number;
  paid_at: string;
  course_name: string;
  class_name: string;
  invoice_link: string | null;
  installments: ProfilePaymentInstallment[];
}

export type ProfilePaymentsResponse = ApiResponse<ProfilePaymentItem[]>;

// ===== Unpaid invoices next two days (reminders) =====

export interface UnpaidInvoiceReminder {
  id: number;
  installment_number: number;
  amount: number;
  due_date: string;
  status: string;
  course_name: string;
  message: string;
}

export interface UnpaidInvoicesNextTwoDaysData {
  has_unpaid_invoices_next_two_days: boolean;
  count: number;
  installments: UnpaidInvoiceReminder[];
}

export type UnpaidInvoicesNextTwoDaysResponse = ApiResponse<UnpaidInvoicesNextTwoDaysData>;

// ===== Installment payment session =====

export interface InstallmentSessionData {
  payment_id: number;
  session_id: string;
}

export interface InstallmentSessionResponse {
  status: boolean;
  message: string;
  data: InstallmentSessionData;
}
