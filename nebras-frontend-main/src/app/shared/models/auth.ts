
export interface Student {
  id: number;
  name: string;
  email: string;
  phone: string;
  country: string;
  age: number;
  gender: number;
  class_id: number;
  class_name: string;
  profile_picture: string | null;
  created_at: string;
  updated_at: string;
}

export interface LoginData {
  student: Student;
  token: string;
}

export interface RegisterData {
  student: Student;
  token: string;
}

export interface LoginResponse {
  status: boolean;
  message: string;
  data: LoginData;
}

export interface RegisterResponse {
  status: boolean;
  message: string;
  data: RegisterData;
}

export interface ProfileResponse {
  status: boolean;
  message: string;
  data: Student;
}

export interface SendOtpData {
  email: string;
  expiresIn?: number;
}


export interface SendOtpResponse {
  status: boolean;
  message: string;
}

export interface VerifyOtpResponse {
  status: boolean;
  message: string;
}

export interface ResetPasswordResponse {
  status: boolean;
  message: string;
}
export interface ResetPasswordRequest {
  email: string;
  otp_code: string;
  password: string;
  password_confirmation: string;
}

export interface ChangePasswordResponse {
  status: boolean;
  message: string;
}

export interface ChangePasswordRequest {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}


